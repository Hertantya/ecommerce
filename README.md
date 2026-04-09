# 🛒 Laravel E-Commerce System

A complete e-commerce web application built with **Laravel 10** (PHP 8.2), MySQL (phpMyAdmin),
and Bootstrap 5. Two user roles: **Admin** and **Customer**.

---

## 📋 Requirements

| Tool       | Version        |
|------------|----------------|
| PHP        | 8.2.12         |
| Composer   | 2.9.5          |
| Laravel    | 10.x           |
| MySQL      | 5.7+ / 8.x     |
| Extension  | php_gd, php_pdo_mysql, php_fileinfo, php_mbstring |

---

## 🚀 Installation – Step by Step

### Step 1 — Create a fresh Laravel project

Open your terminal (CMD / PowerShell / Git Bash) and run:

```bash
composer create-project laravel/laravel ecommerce "^10.0"
cd ecommerce
```

> This installs Laravel 10 which works perfectly with PHP 8.2.

---

### Step 2 — Copy the project files

Copy all folders from this ZIP into your new `ecommerce/` folder.
**Replace** any files that already exist (especially `routes/web.php`,
`app/Providers/AppServiceProvider.php`, `app/Http/Middleware/RedirectIfAuthenticated.php`).

The files to copy are:
```
app/
  Http/
    Controllers/
      AuthController.php
      Admin/  (all 6 controllers)
      Customer/  (all 3 controllers)
    Middleware/
      AdminMiddleware.php
      CustomerMiddleware.php
      RedirectIfAuthenticated.php   ← replace existing
  Models/
    User.php          ← replace existing
    Category.php
    Product.php
    Order.php
    OrderItem.php
  Providers/
    AppServiceProvider.php          ← replace existing

database/
  seeders/
    DatabaseSeeder.php              ← replace existing

resources/
  views/
    layouts/
      admin.blade.php
      customer.blade.php
    auth/
      login.blade.php
      register.blade.php
    admin/  (all views)
    customer/  (all views)

routes/
  web.php                           ← replace existing
```

---

### Step 3 — Configure the database (.env)

Open the `.env` file in the project root and set your database credentials:

```env
APP_NAME="My Shop"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_db
DB_USERNAME=root
DB_PASSWORD=              ← your phpMyAdmin/MySQL password (leave blank if none)
```

---

### Step 4 — Import the SQL file

**Option A — phpMyAdmin (recommended for beginners):**
1. Open phpMyAdmin in your browser (usually `http://localhost/phpmyadmin`)
2. Click **"New"** in the left sidebar → name it `ecommerce_db` → click **Create**
3. Select `ecommerce_db` → click the **Import** tab
4. Choose the file `ecommerce.sql` → click **Go**

**Option B — Command line:**
```bash
mysql -u root -p < ecommerce.sql
```

> The SQL file creates all tables AND inserts sample data (categories + products).
> BUT it does NOT insert users with correct password hashes.
> Use the seeder (Step 5) to create users.

---

### Step 5 — Register the middleware

Open `app/Http/Kernel.php` and find the `$middlewareAliases` array.
Add these two lines inside it:

```php
'admin'    => \App\Http\Middleware\AdminMiddleware::class,
'customer' => \App\Http\Middleware\CustomerMiddleware::class,
```

See the file `KERNEL_REGISTRATION.php` for a full example.
*(For Laravel 11, see the Laravel 11 section in that file.)*

---

### Step 6 — Run the seeder (creates admin & demo customer)

```bash
php artisan db:seed
```

You will see:
```
✅ Seeded: 1 admin, 1 customer, 3 categories, 8 products
   Admin    → admin@shop.com    / admin123
   Customer → customer@shop.com / customer123
```

---

### Step 7 — Create the storage symlink (for product images)

```bash
php artisan storage:link
```

> This creates `public/storage` → `storage/app/public` so uploaded images are accessible.

---

### Step 8 — Start the development server

```bash
php artisan serve
```

Open your browser: **http://localhost:8000**

---

## 🔐 Login Credentials

| Role     | Email                 | Password     |
|----------|-----------------------|--------------|
| Admin    | admin@shop.com        | admin123     |
| Customer | customer@shop.com     | customer123  |

---

## 🗂️ Feature Overview

### Customer
| Feature | Route |
|---------|-------|
| Register | /register |
| Login | /login |
| Browse menu (filter by category) | /customer/dashboard |
| Add to cart (with quantity) | POST /customer/cart/add |
| View & manage cart | /customer/cart |
| Checkout (takeaway / delivery toggle, address, notes) | /customer/checkout |
| Place order | POST /customer/order/place |
| View order history | /customer/orders |
| Track order status | /customer/orders/{id} |

### Admin
| Feature | Route |
|---------|-------|
| Dashboard with stats | /admin/dashboard |
| Manage categories (CRUD) | /admin/categories |
| Manage products (CRUD + image + stock + price) | /admin/products |
| View all orders (filter by status) | /admin/orders |
| View order detail | /admin/orders/{id} |
| Change order status | PATCH /admin/orders/{id}/status |
| Manage customers | /admin/customers |
| Daily report | /admin/reports/daily |

---

## 🗄️ Database Schema Explained

### `users`
Stores both admins and customers. The `role` column (`admin` / `customer`)
controls which part of the app they access.

### `categories`
Simple grouping for products (Food, Beverages, Snacks, etc.)

### `products`
Each product belongs to a category. Has `price` and `stock` columns.
When a customer places an order, stock is automatically decremented.

### `orders`
One order per checkout. Key columns:
- `order_code` — unique code like `ORD-20241015-A3F2B`
- `delivery_type` — `takeaway` or `delivery`
- `address` — filled only when delivery_type is `delivery`
- `status` — `wait` → `processing` → `ready` → `done`
- `total` — grand total in Rupiah

### `order_items`
The "line items" of each order. Stores a **snapshot** of the price
at the time of purchase, so changing the product price later won't
affect old orders.

---

## 🔄 Order Status Flow

```
[Customer places order]
        ↓
     WAIT          ← order just placed, awaiting admin
        ↓
  PROCESSING       ← admin is preparing the order
        ↓
    READY           ← ready to pick up (takeaway) or out for delivery
        ↓
     DONE           ← order completed
```

Admin can set any status from the order detail page.

---

## 🧠 Key Concepts Explained

### Why use DB Transaction in OrderController?
When placing an order we do multiple DB writes:
create the order, create each order item, decrement stock.
If any step fails mid-way, the transaction rolls back everything
so you never end up with a half-created order.

### Why store price in order_items?
Product prices can change over time. If we only stored `product_id`,
old orders would show the new price. Storing `price` as a snapshot
preserves the exact price the customer paid.

### Why session-based cart?
No extra database table needed. The cart only needs to survive
while the user is browsing. Once the order is placed, the cart
is cleared from the session automatically.

### What is @active() in the layout?
It's a custom Blade directive registered in AppServiceProvider.
`@active('admin/products*')` outputs the CSS class `active`
when the current URL matches the pattern — used to highlight
the current page in the sidebar.

---

## ⚙️ Common Artisan Commands

```bash
# Start dev server
php artisan serve

# Clear all caches (run this if changes aren't showing)
php artisan optimize:clear

# Re-seed (resets demo users)
php artisan db:seed

# Re-create storage symlink
php artisan storage:link

# List all routes
php artisan route:list
```

---

## 📂 Project Structure

```
app/
  Http/
    Controllers/
      AuthController.php          ← login, register, logout
      Admin/
        DashboardController.php   ← summary stats
        ProductController.php     ← CRUD products
        CategoryController.php    ← CRUD categories
        OrderController.php       ← list + update status
        CustomerController.php    ← list + delete
        ReportController.php      ← daily report
      Customer/
        DashboardController.php   ← product catalogue
        CartController.php        ← session cart
        OrderController.php       ← checkout + order history
    Middleware/
      AdminMiddleware.php         ← blocks non-admins
      CustomerMiddleware.php      ← blocks non-customers
      RedirectIfAuthenticated.php ← role-based redirect on login
  Models/
    User.php / Category.php / Product.php / Order.php / OrderItem.php

resources/views/
  layouts/
    admin.blade.php               ← sidebar layout for admin
    customer.blade.php            ← navbar layout for customer
  auth/  login + register
  admin/ dashboard, products, categories, orders, customers, reports
  customer/ dashboard, cart, checkout, orders, order-detail

routes/
  web.php                         ← all routes defined here
```
