<?php
namespace Plugins\Store\Controllers\Admin;

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

    protected function requireAdmin()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect("/admin/login");
            exit;
        }
    }

    public function dashboard()
    {
        $this->requireAdmin();
        $stats = [
            "products" => $this->db->table("store_products")->count() ?: 0,
            "categories" => $this->db->table("store_categories")->count() ?: 0,
            "orders" => $this->db->table("store_orders")->count() ?: 0,
            "revenue" => $this->db->query("SELECT COALESCE(SUM(total),0) FROM store_orders WHERE status NOT IN ('cancelled','refunded')")->fetchColumn() ?: 0,
            "pending" => $this->db->table("store_orders")->where("status", "pending")->count() ?: 0,
        ];
        $recent = $this->db->table("store_orders")->orderBy("created_at", "DESC")->limit(5)->get() ?: [];
        return $this->view("Plugins.Store.Views.admin.dashboard", [
            "title" => "Store Admin", "stats" => $stats, "recent" => $recent,
        ]);
    }

    public function products()
    {
        $this->requireAdmin();
        $products = $this->db->query(
            "SELECT sp.*, sc.name as category_name FROM store_products sp LEFT JOIN store_categories sc ON sp.category_id = sc.id ORDER BY sp.created_at DESC"
        )->fetchAll() ?: [];
        return $this->view("Plugins.Store.Views.admin.products", [
            "title" => "Products", "products" => $products,
        ]);
    }

    public function productCreate()
    {
        $this->requireAdmin();
        $categories = $this->db->table("store_categories")->orderBy("name")->get() ?: [];
        return $this->view("Plugins.Store.Views.admin.product_form", [
            "title" => "New Product", "categories" => $categories, "product" => null,
        ]);
    }

    public function productStore()
    {
        $this->requireAdmin();
        $slug = strtolower(preg_replace('/[^a-z0-9-]/', '', str_replace(' ', '-', $this->request->post("name", ""))));
        $data = [
            "category_id" => (int)$this->request->post("category_id", 0) ?: null,
            "name" => $this->request->post("name", ""),
            "slug" => $slug,
            "description" => $this->request->post("description", ""),
            "short_description" => $this->request->post("short_description", ""),
            "price" => (float)$this->request->post("price", 0),
            "compare_price" => (float)$this->request->post("compare_price", 0) ?: null,
            "images" => $this->request->post("images", ""),
            "stock" => (int)$this->request->post("stock", -1),
            "sku" => $this->request->post("sku", ""),
            "type" => $this->request->post("type", "digital"),
            "featured" => (int)$this->request->post("featured", 0),
            "status" => $this->request->post("status", "draft"),
            "metadata" => $this->request->post("metadata", ""),
        ];
        $id = $this->db->table("store_products")->insertGetId($data);
        $_SESSION["success_message"] = "Product created!";
        $this->response->redirect("/admin/store/products/edit/" . $id);
    }

    public function productEdit($id)
    {
        $this->requireAdmin();
        $product = $this->db->table("store_products")->where("id", (int)$id)->first();
        if (!$product) { $this->response->redirect("/admin/store/products"); exit; }
        $categories = $this->db->table("store_categories")->orderBy("name")->get() ?: [];
        return $this->view("Plugins.Store.Views.admin.product_form", [
            "title" => "Edit Product", "categories" => $categories, "product" => $product,
        ]);
    }

    public function productUpdate($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        $slug = strtolower(preg_replace('/[^a-z0-9-]/', '', str_replace(' ', '-', $this->request->post("name", ""))));
        $data = [
            "category_id" => (int)$this->request->post("category_id", 0) ?: null,
            "name" => $this->request->post("name", ""),
            "slug" => $slug,
            "description" => $this->request->post("description", ""),
            "short_description" => $this->request->post("short_description", ""),
            "price" => (float)$this->request->post("price", 0),
            "compare_price" => (float)$this->request->post("compare_price", 0) ?: null,
            "images" => $this->request->post("images", ""),
            "stock" => (int)$this->request->post("stock", -1),
            "sku" => $this->request->post("sku", ""),
            "type" => $this->request->post("type", "digital"),
            "featured" => (int)$this->request->post("featured", 0),
            "status" => $this->request->post("status", "draft"),
            "metadata" => $this->request->post("metadata", ""),
        ];
        $this->db->table("store_products")->where("id", $id)->update($data);
        $_SESSION["success_message"] = "Product updated!";
        $this->response->redirect("/admin/store/products/edit/" . $id);
    }

    public function productDelete($id)
    {
        $this->requireAdmin();
        $this->db->table("store_products")->where("id", (int)$id)->delete();
        $_SESSION["success_message"] = "Product deleted.";
        $this->response->redirect("/admin/store/products");
    }

    public function categories()
    {
        $this->requireAdmin();
        $cats = $this->db->table("store_categories")->orderBy("sort_order")->get() ?: [];
        return $this->view("Plugins.Store.Views.admin.categories", [
            "title" => "Categories", "categories" => $cats,
        ]);
    }

    public function categoryStore()
    {
        $this->requireAdmin();
        $slug = strtolower(preg_replace('/[^a-z0-9-]/', '', str_replace(' ', '-', $this->request->post("name", ""))));
        $this->db->table("store_categories")->insert([
            "name" => $this->request->post("name", ""),
            "slug" => $slug,
            "description" => $this->request->post("description", ""),
            "icon" => $this->request->post("icon", ""),
            "sort_order" => (int)$this->request->post("sort_order", 0),
        ]);
        $_SESSION["success_message"] = "Category created!";
        $this->response->redirect("/admin/store/categories");
    }

    public function categoryDelete($id)
    {
        $this->requireAdmin();
        $this->db->table("store_categories")->where("id", (int)$id)->delete();
        $_SESSION["success_message"] = "Category deleted.";
        $this->response->redirect("/admin/store/categories");
    }

    public function orders()
    {
        $this->requireAdmin();
        $orders = $this->db->table("store_orders")->orderBy("created_at", "DESC")->get() ?: [];
        return $this->view("Plugins.Store.Views.admin.orders", [
            "title" => "Orders", "orders" => $orders,
        ]);
    }

    public function orderShow($id)
    {
        $this->requireAdmin();
        $order = $this->db->table("store_orders")->where("id", (int)$id)->first();
        if (!$order) { $this->response->redirect("/admin/store/orders"); exit; }
        $items = $this->db->table("store_order_items")->where("order_id", $order->id)->get() ?: [];
        return $this->view("Plugins.Store.Views.admin.order_show", [
            "title" => "Order #" . $order->id, "order" => $order, "items" => $items,
        ]);
    }

    public function orderUpdateStatus($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        $status = $this->request->post("status", "pending");
        $paymentStatus = $this->request->post("payment_status", "unpaid");
        $this->db->table("store_orders")->where("id", $id)->update([
            "status" => $status, "payment_status" => $paymentStatus,
        ]);
        if ($paymentStatus === "paid") {
            $order = $this->db->table("store_orders")->where("id", $id)->first();
            if ($order && $order->invoice_id) {
                $this->db->table("invoices")->where("id", $order->invoice_id)->update(["status" => "paid"]);
            }
        }
        $_SESSION["success_message"] = "Order updated.";
        $this->response->redirect("/admin/store/orders/" . $id);
    }
}
