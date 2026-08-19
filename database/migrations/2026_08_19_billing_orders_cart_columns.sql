-- Checkout columns for billing_orders used by public/cart.php and admin/BillingController@orderStore
ALTER TABLE billing_orders ADD COLUMN package_id INT NULL AFTER product_id;
ALTER TABLE billing_orders ADD COLUMN items LONGTEXT NULL AFTER package_id;
ALTER TABLE billing_orders ADD COLUMN payment_method VARCHAR(50) NULL AFTER items;
ALTER TABLE billing_orders ADD COLUMN description TEXT NULL AFTER notes;