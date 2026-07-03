<?php
namespace Plugins\Store\Controllers\User;

use Core\Controller;

class StoreController extends Controller
{
    protected $auth, $db, $response, $request;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get("auth");
        $this->db = $app->get("db");
        $this->response = $app->get("response");
        $this->request = $app->get("request");
    }

    protected function getUserId()
    {
        if ($this->auth->check()) {
            $user = $this->auth->user();
            $hosting = $this->db->table("hosting_users")->where("email", $user->email)->first();
            return $hosting ? $hosting->id : $user->id ?? 0;
        }
        return 0;
    }

    protected function getSessionId()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION["store_session"])) $_SESSION["store_session"] = bin2hex(random_bytes(16));
        return $_SESSION["store_session"];
    }

    public function catalog()
    {
        $categories = $this->db->table("store_categories")->where("is_active", 1)->orderBy("sort_order")->get() ?: [];
        $featured = $this->db->table("store_products")->where("status", "active")->where("featured", 1)->limit(8)->get() ?: [];
        $new = $this->db->table("store_products")->where("status", "active")->orderBy("created_at", "DESC")->limit(8)->get() ?: [];
        $cartCount = $this->getCartCount();
        return $this->view("Plugins.Store.Views.user.catalog", [
            "title" => "Store", "categories" => $categories,
            "featured" => $featured, "new" => $new, "cartCount" => $cartCount,
        ]);
    }

    public function category($slug)
    {
        $cat = $this->db->table("store_categories")->where("slug", $slug)->where("is_active", 1)->first();
        if (!$cat) { $this->response->redirect("/store"); exit; }
        $products = $this->db->table("store_products")->where("category_id", $cat->id)->where("status", "active")->get() ?: [];
        return $this->view("Plugins.Store.Views.user.category", [
            "title" => $cat->name, "category" => $cat, "products" => $products,
        ]);
    }

    public function product($slug)
    {
        $product = $this->db->table("store_products")->where("slug", $slug)->where("status", "active")->first();
        if (!$product) { $this->response->redirect("/store"); exit; }
        $product->images_arr = $product->images ? json_decode($product->images, true) : [];
        $related = $this->db->table("store_products")->where("category_id", $product->category_id)->where("id", "!=", $product->id)->where("status", "active")->limit(4)->get() ?: [];
        $cartCount = $this->getCartCount();
        return $this->view("Plugins.Store.Views.user.product", [
            "title" => $product->name, "product" => $product,
            "related" => $related, "cartCount" => $cartCount,
        ]);
    }

    public function cart()
    {
        $userId = $this->getUserId();
        $sessionId = $this->getSessionId();
        $items = $this->db->query(
            "SELECT sc.*, sp.name, sp.price, sp.images, sp.slug FROM store_cart sc JOIN store_products sp ON sc.product_id = sp.id WHERE (sc.user_id = ? OR sc.session_id = ?)",
            [$userId, $sessionId]
        )->fetchAll() ?: [];
        $total = array_sum(array_map(fn($i) => $i->price * $i->qty, $items));
        return $this->view("Plugins.Store.Views.user.cart", [
            "title" => "Cart", "items" => $items, "total" => $total,
        ]);
    }

    public function cartAdd()
    {
        $productId = (int)$this->request->post("product_id", 0);
        $qty = max(1, (int)$this->request->post("qty", 1));
        $userId = $this->getUserId();
        $sessionId = $this->getSessionId();
        $existing = $this->db->table("store_cart")->where("product_id", $productId)->where(function($q) use ($userId, $sessionId) {
            if ($userId) $q->where("user_id", $userId);
            else $q->where("session_id", $sessionId);
        })->first();
        if ($existing) {
            $this->db->table("store_cart")->where("id", $existing->id)->update(["qty" => $existing->qty + $qty]);
        } else {
            $data = ["product_id" => $productId, "qty" => $qty];
            if ($userId) $data["user_id"] = $userId;
            else $data["session_id"] = $sessionId;
            $this->db->table("store_cart")->insert($data);
        }
        $_SESSION["success_message"] = "Added to cart!";
        $this->response->redirect("/store/cart");
    }

    public function cartUpdate()
    {
        $id = (int)$this->request->post("id", 0);
        $qty = max(0, (int)$this->request->post("qty", 0));
        if ($qty < 1) { $this->db->table("store_cart")->where("id", $id)->delete(); }
        else { $this->db->table("store_cart")->where("id", $id)->update(["qty" => $qty]); }
        $this->response->redirect("/store/cart");
    }

    public function cartRemove()
    {
        $id = (int)$this->request->post("id", 0);
        $this->db->table("store_cart")->where("id", $id)->delete();
        $this->response->redirect("/store/cart");
    }

    public function checkout()
    {
        $userId = $this->getUserId();
        $sessionId = $this->getSessionId();
        $items = $this->db->query(
            "SELECT sc.*, sp.name, sp.price, sp.slug FROM store_cart sc JOIN store_products sp ON sc.product_id = sp.id WHERE (sc.user_id = ? OR sc.session_id = ?)",
            [$userId, $sessionId]
        )->fetchAll() ?: [];
        if (empty($items)) { $this->response->redirect("/store/cart"); exit; }
        $total = array_sum(array_map(fn($i) => $i->price * $i->qty, $items));
        $user = $this->auth->check() ? $this->auth->user() : null;
        return $this->view("Plugins.Store.Views.user.checkout", [
            "title" => "Checkout", "items" => $items, "total" => $total, "user" => $user,
        ]);
    }

    public function placeOrder()
    {
        $userId = $this->getUserId();
        $sessionId = $this->getSessionId();
        $items = $this->db->query(
            "SELECT sc.*, sp.name, sp.price, sp.slug FROM store_cart sc JOIN store_products sp ON sc.product_id = sp.id WHERE (sc.user_id = ? OR sc.session_id = ?)",
            [$userId, $sessionId]
        )->fetchAll() ?: [];
        if (empty($items)) { $_SESSION["error_message"] = "Cart is empty."; $this->response->redirect("/store/cart"); exit; }

        $first = $this->request->post("first_name", "");
        $last = $this->request->post("last_name", "");
        $email = $this->request->post("email", "");
        $phone = $this->request->post("phone", "");
        $addr1 = $this->request->post("address_line1", "");
        $addr2 = $this->request->post("address_line2", "");
        $city = $this->request->post("city", "");
        $state = $this->request->post("state", "");
        $zip = $this->request->post("zip", "");
        $country = $this->request->post("country", "");
        $notes = $this->request->post("notes", "");

        $subtotal = array_sum(array_map(fn($i) => $i->price * $i->qty, $items));
        $tax = round($subtotal * 0.0, 2);
        $total = $subtotal + $tax;

        $this->db->beginTransaction();
        try {
            $invId = null;
            if ($userId) {
                $invNum = "INV-" . date("Ymd") . "-" . strtoupper(bin2hex(random_bytes(4)));
                $this->db->table("invoices")->insert([
                    "user_id" => $userId, "invoice_number" => $invNum,
                    "date" => date("Y-m-d"), "due_date" => date("Y-m-d", strtotime("+7 days")),
                    "subtotal" => $subtotal, "tax_amount" => $tax, "total" => $total,
                    "status" => "sent",
                ]);
                $invId = $this->db->lastInsertId();
            }

            $orderId = $this->db->table("store_orders")->insertGetId([
                "invoice_id" => $invId, "user_id" => $userId ?: 0,
                "first_name" => $first, "last_name" => $last, "email" => $email,
                "phone" => $phone, "address_line1" => $addr1, "address_line2" => $addr2,
                "city" => $city, "state" => $state, "zip" => $zip, "country" => $country,
                "notes" => $notes, "subtotal" => $subtotal, "tax" => $tax,
                "total" => $total, "status" => "pending", "payment_status" => "unpaid",
            ]);

            foreach ($items as $item) {
                $this->db->table("store_order_items")->insert([
                    "order_id" => $orderId, "product_id" => $item->product_id,
                    "product_name" => $item->name, "qty" => $item->qty,
                    "unit_price" => $item->price, "total" => $item->price * $item->qty,
                ]);
            }

            $this->db->table("store_cart")->where(function($q) use ($userId, $sessionId) {
                if ($userId) $q->where("user_id", $userId);
                else $q->where("session_id", $sessionId);
            })->delete();

            $this->db->commit();
            $_SESSION["success_message"] = "Order placed!";
            $this->response->redirect("/store/orders/" . $orderId);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $_SESSION["error_message"] = "Order failed: " . $e->getMessage();
            $this->response->redirect("/store/checkout");
        }
    }

    public function orders()
    {
        $userId = $this->getUserId();
        if (!$userId) { $this->response->redirect("/store"); exit; }
        $orders = $this->db->table("store_orders")->where("user_id", $userId)->orderBy("created_at", "DESC")->get() ?: [];
        return $this->view("Plugins.Store.Views.user.orders", [
            "title" => "My Orders", "orders" => $orders,
        ]);
    }

    public function orderShow($id)
    {
        $userId = $this->getUserId();
        $order = $this->db->table("store_orders")->where("id", (int)$id)->first();
        if (!$order || ($order->user_id != $userId && $userId < 1)) { $this->response->redirect("/store/orders"); exit; }
        $items = $this->db->table("store_order_items")->where("order_id", $order->id)->get() ?: [];
        return $this->view("Plugins.Store.Views.user.order_show", [
            "title" => "Order #" . $order->id, "order" => $order, "items" => $items,
        ]);
    }

    protected function getCartCount()
    {
        $userId = $this->getUserId();
        $sessionId = $this->getSessionId();
        $row = $this->db->query(
            "SELECT SUM(qty) as cnt FROM store_cart WHERE (user_id = ? OR session_id = ?)",
            [$userId, $sessionId]
        )->fetch();
        return $row ? (int)$row->cnt : 0;
    }
}
