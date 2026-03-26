-- All users use password 'password123'
-- Hash: $2y$12$JGTA3vJy0/fULa6GUaQcVueDvgagh0RVu247UGJRZ.rWPvM/ev1Ca
INSERT INTO users (uuid, name, email, password_hash, role, phone, theme_preference) VALUES
(UUID(), 'Admin User', 'admin@example.com', '$2y$12$JGTA3vJy0/fULa6GUaQcVueDvgagh0RVu247UGJRZ.rWPvM/ev1Ca', 'admin', '1234567890', 'dark'),
(UUID(), 'Manager Manager', 'manager@example.com', '$2y$12$JGTA3vJy0/fULa6GUaQcVueDvgagh0RVu247UGJRZ.rWPvM/ev1Ca', 'manager', '0987654321', 'light'),
(UUID(), 'Staff One', 'staff1@example.com', '$2y$12$JGTA3vJy0/fULa6GUaQcVueDvgagh0RVu247UGJRZ.rWPvM/ev1Ca', 'staff', '1112223333', 'dark'),
(UUID(), 'Staff Two', 'staff2@example.com', '$2y$12$JGTA3vJy0/fULa6GUaQcVueDvgagh0RVu247UGJRZ.rWPvM/ev1Ca', 'staff', '2223334444', 'light'),
(UUID(), 'Alice Customer', 'customer1@example.com', '$2y$12$JGTA3vJy0/fULa6GUaQcVueDvgagh0RVu247UGJRZ.rWPvM/ev1Ca', 'customer', '5556667777', 'dark'),
(UUID(), 'Bob Customer', 'customer2@example.com', '$2y$12$JGTA3vJy0/fULa6GUaQcVueDvgagh0RVu247UGJRZ.rWPvM/ev1Ca', 'customer', '8889990000', 'light'),
(UUID(), 'Charlie Customer', 'customer3@example.com', '$2y$12$JGTA3vJy0/fULa6GUaQcVueDvgagh0RVu247UGJRZ.rWPvM/ev1Ca', 'customer', '9990001111', 'dark');

INSERT INTO products (sku, name, description, price, stock_qty, category) VALUES
('SKU-1001', 'Wireless Mouse', 'Ergonomic wireless mouse with USB receiver', 25.99, 100, 'Electronics'),
('SKU-1002', 'Mechanical Keyboard', 'RGB mechanical keyboard with blue switches', 89.99, 50, 'Electronics'),
('SKU-2001', 'Coffee Mug', 'Ceramic coffee mug 12oz', 12.50, 200, 'Home & Kitchen'),
('SKU-2002', 'Desk Lamp', 'LED desk lamp with adjustable brightness', 45.00, 75, 'Home & Kitchen'),
('SKU-3001', 'Notebook', 'A5 dotted notebook 160 pages', 15.00, 150, 'Stationery');

-- Seed sample orders
INSERT INTO orders (order_number, customer_id, assigned_to, status, subtotal, discount, tax, total, payment_method, payment_status, shipping_address) VALUES
('ORD-2026-00001', 5, 3, 'delivered', 115.98, 0.00, 11.60, 127.58, 'Credit Card', 'paid', '{"street": "123 Main St", "city": "Metropolis", "zip": "10001"}'),
('ORD-2026-00002', 6, NULL, 'pending', 45.00, 5.00, 4.00, 44.00, 'PayPal', 'unpaid', '{"street": "456 Elm St", "city": "Gotham", "zip": "10002"}'),
('ORD-2026-00003', 7, 4, 'shipped', 25.99, 0.00, 2.60, 28.59, 'Stripe', 'paid', '{"street": "789 Oak Ave", "city": "Star City", "zip": "10003"}'),
('ORD-2026-00004', 5, NULL, 'cancelled', 89.99, 0.00, 9.00, 98.99, 'Credit Card', 'refunded', '{"street": "123 Main St", "city": "Metropolis", "zip": "10001"}'),
('ORD-2026-00005', 6, 3, 'processing', 15.00, 0.00, 1.50, 16.50, 'Bank Transfer', 'paid', '{"street": "456 Elm St", "city": "Gotham", "zip": "10002"}');

-- Seed order items
INSERT INTO order_items (order_id, product_id, product_name, product_sku, unit_price, quantity, subtotal) VALUES
(1, 1, 'Wireless Mouse', 'SKU-1001', 25.99, 1, 25.99),
(1, 2, 'Mechanical Keyboard', 'SKU-1002', 89.99, 1, 89.99),
(2, 4, 'Desk Lamp', 'SKU-2002', 45.00, 1, 45.00),
(3, 1, 'Wireless Mouse', 'SKU-1001', 25.99, 1, 25.99),
(4, 2, 'Mechanical Keyboard', 'SKU-1002', 89.99, 1, 89.99),
(5, 5, 'Notebook', 'SKU-3001', 15.00, 1, 15.00);

-- Seed history
INSERT INTO order_status_history (order_id, changed_by, old_status, new_status, comment) VALUES
(1, 5, NULL, 'pending', 'Order placed by customer'),
(1, 3, 'pending', 'processing', 'Started processing'),
(1, 3, 'processing', 'shipped', 'Package handed to courier'),
(1, 3, 'shipped', 'delivered', 'Customer signed for delivery'),
(2, 6, NULL, 'pending', 'Order placed by customer'),
(3, 7, NULL, 'pending', 'Order placed by customer'),
(3, 4, 'pending', 'processing', 'Processing order'),
(3, 4, 'processing', 'shipped', 'Shipped via FedEx'),
(4, 5, NULL, 'pending', 'Order placed by customer'),
(4, 5, 'pending', 'cancelled', 'Customer requested cancellation'),
(5, 6, NULL, 'pending', 'Order placed by customer'),
(5, 3, 'pending', 'processing', 'Payment verified, preparing to ship');
