# Food Delivery Management System — Implementation Blueprint

> **For Claude Code:** Build every file in this document completely. No placeholders, no TODOs, no stubs. Every PHP file must include CORS headers, require helpers, and handle all error cases listed in Section 8. Every HTML page must include the session guard, link all required CSS/JS, and implement full functionality. Match all UI requirements in Section 10 exactly.

---

## Table of Contents

1. [Infrastructure Overview](#1-infrastructure-overview)
2. [Free Hosting Setup](#2-free-hosting-setup)
3. [Project Folder Structure](#3-project-folder-structure)
4. [Database Schema](#4-database-schema)
5. [Backend — PHP API Specification](#5-backend--php-api-specification)
6. [Frontend — Pages & Features](#6-frontend--pages--features)
7. [Authentication & Sessions](#7-authentication--sessions)
8. [Error Handling Requirements](#8-error-handling-requirements)
9. [Build & Deployment Instructions](#9-build--deployment-instructions)
10. [UI / UX Requirements](#10-ui--ux-requirements)
11. [File-by-File Build Order](#11-file-by-file-build-order)

---

## 1. Infrastructure Overview

The system uses a **split-hosting model**:

- **Frontend** (HTML/CSS/JS) → Firebase Hosting (free, global CDN)
- **Backend** (PHP + MySQL) → InfinityFree (free PHP hosting, permanent, no credit card)

Every group member accesses the same live URLs. No one needs to run a local server to use the system.

### Architecture

```
Browser (Any Device)
  https://food-delivery-xxx.web.app   ← Firebase CDN
  HTML · CSS · Vanilla JavaScript
          │
          │  fetch() / AJAX over HTTPS
          ▼
  https://fooddelivery.infinityfreeapp.com/api/
  PHP 8.x + Apache + .htaccess CORS
          │
          │  PDO (prepared statements)
          ▼
  MySQL / MariaDB
  Managed via phpMyAdmin (provided by InfinityFree)
```

### Course Requirement Mapping

| Requirement | Implementation | Where It Appears |
|---|---|---|
| HTML5 | Semantic tags, form validation attrs, data- attributes | All .html pages |
| CSS3 | Custom properties, Flexbox, Grid, transitions, animations | style.css + page styles |
| JavaScript | Fetch API, DOM manipulation, form validation, setInterval polling | api.js + page scripts |
| PHP | REST API endpoints, sessions, PDO prepared statements | backend/api/*.php |
| MySQL | Relational schema, JOINs, foreign keys, transactions | food_delivery database |
| Web Server | Apache on InfinityFree + Firebase CDN | Both hosting layers |

---

## 2. Free Hosting Setup

### 2.1 Firebase Hosting (Frontend)

1. Go to https://firebase.google.com — sign in with a Google account.
2. Click **Add project** → name it `food-delivery-system` → disable Analytics → **Create project**.
3. In VS Code terminal: `npm install -g firebase-tools`
4. `firebase login` (opens browser)
5. Inside `frontend/` folder: `firebase init hosting`
   - Select existing project: `food-delivery-system`
   - Public directory: `.` (just a dot)
   - Single-page app rewrite: **No**
   - Overwrite index.html: **No**
6. Deploy: `firebase deploy` (run from `frontend/`)
7. Firebase prints a URL like `https://food-delivery-xxx.web.app` — share with all group members.

> Re-deploy after every frontend change: `cd frontend && firebase deploy`

### 2.2 InfinityFree Hosting (PHP + MySQL Backend)

1. Go to https://infinityfree.com — create a free account.
2. Create a new hosting account. You get a subdomain like `fooddelivery.infinityfreeapp.com` and a VistaPanel control panel.
3. In VistaPanel → **MySQL Databases** → create a database. Note:
   - Database name (format: `epiz_XXXXXXX_food`)
   - Database username
   - Database password
   - Database host (e.g. `sql200.infinityfree.com` — shown in VistaPanel)
4. Open **phpMyAdmin** from VistaPanel and run the full SQL schema from Section 4.
5. In `backend/config/db.php` set the four constants to your values from step 3.
6. Upload the entire `backend/` folder contents into `htdocs/` using VistaPanel File Manager or FileZilla FTP.
7. Test: visit `https://fooddelivery.infinityfreeapp.com/api/menu/index.php` — should return `[]`.
8. Set `API_BASE` in `frontend/js/config.js` to your InfinityFree URL. Re-deploy Firebase.

**FileZilla FTP credentials** (from VistaPanel → FTP Accounts):
- Host: `ftpupload.net` | Port: `21`
- Username and password shown in VistaPanel

### 2.3 Local Development with XAMPP

Each group member can run the stack locally for development:

1. Install XAMPP from https://apachefriends.org
2. Start Apache and MySQL in XAMPP Control Panel
3. Place `backend/` contents in `C:/xampp/htdocs/food-delivery-backend/`
4. Open phpMyAdmin at `http://localhost/phpmyadmin` and import the schema
5. Set `API_BASE` in `config.js` to `http://localhost/food-delivery-backend/api`
6. Use VS Code **Live Server** extension to preview `frontend/` locally

---

## 3. Project Folder Structure

```
food-delivery-system/
│
├── frontend/                          ← Firebase Hosting root
│   ├── firebase.json                  ← Firebase config (auto-generated by firebase init)
│   ├── .firebaserc                    ← Project alias (auto-generated)
│   │
│   ├── index.html                     ← Landing / Login + Register page
│   │
│   ├── css/
│   │   ├── style.css                  ← Global variables, reset, typography, toast, spinner
│   │   ├── components.css             ← Cards, badges, modals, tables, buttons, forms
│   │   └── pages/
│   │       ├── auth.css               ← Login & register page styles
│   │       ├── admin.css              ← Admin dashboard + sidebar styles
│   │       ├── customer.css           ← Customer menu, cart, track styles
│   │       └── rider.css              ← Rider deliveries page styles
│   │
│   ├── js/
│   │   ├── config.js                  ← API_BASE URL — single source of truth
│   │   ├── api.js                     ← apiFetch() wrapper with central error handling
│   │   ├── auth.js                    ← login(), logout(), getSession(), sessionGuard()
│   │   └── utils.js                   ← showToast(), formatDate(), formatPrice()
│   │
│   ├── admin/
│   │   ├── dashboard.html             ← KPI cards + recent orders, auto-refresh 30s
│   │   ├── orders.html                ← All orders table, status update, rider assign
│   │   ├── menu.html                  ← Add/edit/delete menu items (modal forms)
│   │   └── riders.html                ← Manage rider accounts
│   │
│   ├── customer/
│   │   ├── menu.html                  ← Browse menu by category, add to cart
│   │   ├── cart.html                  ← Cart review, place order
│   │   └── track.html                 ← Order status tracking, auto-refresh 20s
│   │
│   └── rider/
│       └── deliveries.html            ← Assigned deliveries, mark delivered
│
└── backend/                           ← InfinityFree htdocs root
    ├── .htaccess                      ← CORS headers, security, OPTIONS handler
    │
    ├── config/
    │   └── db.php                     ← PDO connection singleton
    │
    ├── helpers/
    │   ├── response.php               ← json_response(), error_response()
    │   ├── auth.php                   ← require_role(), current_user()
    │   └── validate.php               ← sanitize(), require_fields()
    │
    └── api/
        ├── auth/
        │   ├── login.php              ← POST: email + password → set session
        │   ├── logout.php             ← POST: destroy session
        │   ├── register.php           ← POST: create customer account
        │   └── session.php            ← GET: return current user info
        │
        ├── menu/
        │   ├── index.php              ← GET: list items / POST: create item (admin)
        │   └── item.php               ← GET/PUT/DELETE: single item by ?id=
        │
        ├── orders/
        │   ├── index.php              ← GET: all orders (admin) / POST: place order (customer)
        │   ├── item.php               ← GET: order detail / PUT: update status
        │   └── my.php                 ← GET: orders for current customer
        │
        ├── riders/
        │   ├── index.php              ← GET: list all riders (admin only)
        │   └── assign.php             ← PUT: assign rider to order (admin only)
        │
        └── stats/
            └── index.php              ← GET: KPI counts for dashboard (admin only)
```

---

## 4. Database Schema

Database name: `food_delivery`. Run this entire block in phpMyAdmin → SQL tab.

```sql
CREATE DATABASE IF NOT EXISTS food_delivery
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE food_delivery;

-- USERS: customers, admins, and riders share this table, differentiated by role
CREATE TABLE users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100)  NOT NULL,
  email      VARCHAR(150)  NOT NULL UNIQUE,
  password   VARCHAR(255)  NOT NULL,
  role       ENUM('admin','customer','rider') NOT NULL DEFAULT 'customer',
  phone      VARCHAR(20),
  address    TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- MENU ITEMS
CREATE TABLE menu_items (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(150)  NOT NULL,
  description TEXT,
  price       DECIMAL(8,2)  NOT NULL,
  category    VARCHAR(80)   NOT NULL,
  image_url   VARCHAR(500),
  available   TINYINT(1)    NOT NULL DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ORDERS
CREATE TABLE orders (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  customer_id      INT           NOT NULL,
  rider_id         INT,
  delivery_address TEXT          NOT NULL,
  total_price      DECIMAL(8,2)  NOT NULL,
  status           ENUM('pending','preparing','out_for_delivery','delivered','cancelled')
                   NOT NULL DEFAULT 'pending',
  notes            TEXT,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES users(id),
  FOREIGN KEY (rider_id)    REFERENCES users(id)
) ENGINE=InnoDB;

-- ORDER ITEMS: line items per order
CREATE TABLE order_items (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  order_id     INT          NOT NULL,
  menu_item_id INT          NOT NULL,
  quantity     INT          NOT NULL DEFAULT 1,
  unit_price   DECIMAL(8,2) NOT NULL,  -- price snapshot at time of order
  FOREIGN KEY (order_id)     REFERENCES orders(id)    ON DELETE CASCADE,
  FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
) ENGINE=InnoDB;

-- SEED: default admin account (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
  ('Admin', 'admin@food.com',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'admin');

-- SEED: sample menu items
INSERT INTO menu_items (name, description, price, category, available) VALUES
  ('Nasi Lemak', 'Coconut rice with sambal, egg, and anchovies', 8.50, 'Rice', 1),
  ('Char Kway Teow', 'Stir-fried flat noodles with prawns and egg', 10.00, 'Noodles', 1),
  ('Roti Canai', 'Flaky flatbread with curry dipping sauce', 3.50, 'Bread', 1),
  ('Teh Tarik', 'Pulled milk tea', 2.50, 'Drinks', 1),
  ('Milo Ais', 'Iced Milo drink', 3.00, 'Drinks', 1);
```

### Entity Relationships

| Table | PK | Foreign Keys | Relationship |
|---|---|---|---|
| users | id | — | One user → many orders (as customer or rider) |
| menu_items | id | — | One menu item → many order_items |
| orders | id | customer_id → users, rider_id → users | One order → one customer, optionally one rider |
| order_items | id | order_id → orders, menu_item_id → menu_items | Bridge table: one order → many items |

---

## 5. Backend — PHP API Specification

All endpoints:
- Return `Content-Type: application/json`
- Accept POST/PUT bodies as JSON (`Content-Type: application/json`)
- Include CORS headers (via `.htaccess` + per-file headers)
- Use PDO prepared statements — never string-interpolated queries

### 5.1 Shared Helper Files

#### `backend/.htaccess`

```apache
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type"

RewriteEngine On
RewriteCond %{REQUEST_METHOD} OPTIONS
RewriteRule .* - [R=200,L]

Options -Indexes
php_flag display_errors off
php_flag log_errors on
```

#### `backend/config/db.php`

```php
<?php
define('DB_HOST', 'sql200.infinityfree.com'); // replace with actual InfinityFree host
define('DB_NAME', 'epiz_XXXXXXX_food');       // replace with your DB name
define('DB_USER', 'epiz_XXXXXXX');            // replace with your DB user
define('DB_PASS', 'your_password');           // replace with your DB password

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
```

#### `backend/helpers/response.php`

```php
<?php
function json_response($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($data);
    exit;
}

function error_response(string $message, int $code = 400): void {
    json_response(['error' => $message], $code);
}
```

#### `backend/helpers/auth.php`

```php
<?php
function require_role(string ...$roles): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id'])) {
        error_response('Unauthenticated. Please log in.', 401);
    }
    if (!in_array($_SESSION['role'], $roles, true)) {
        error_response('Forbidden. You do not have permission.', 403);
    }
}

function current_user(): array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return [
        'id'   => $_SESSION['user_id'] ?? null,
        'role' => $_SESSION['role']    ?? null,
        'name' => $_SESSION['name']    ?? null,
    ];
}
```

#### `backend/helpers/validate.php`

```php
<?php
function require_fields(array $data, array $fields): void {
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
            error_response("Field '{$field}' is required.");
        }
    }
}

function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}
```

### 5.2 API Endpoint Reference

| Method | Endpoint | Auth Required | Description |
|---|---|---|---|
| POST | `/api/auth/login.php` | None | Login — set PHP session |
| POST | `/api/auth/logout.php` | Any | Destroy session |
| POST | `/api/auth/register.php` | None | Register new customer |
| GET | `/api/auth/session.php` | Any | Return current user info |
| GET | `/api/menu/index.php` | None | List all available menu items |
| POST | `/api/menu/index.php` | admin | Create new menu item |
| GET | `/api/menu/item.php?id=N` | None | Get single menu item |
| PUT | `/api/menu/item.php?id=N` | admin | Update menu item |
| DELETE | `/api/menu/item.php?id=N` | admin | Delete menu item |
| GET | `/api/orders/index.php` | admin | List all orders with customer + rider names |
| POST | `/api/orders/index.php` | customer | Place new order (inserts order + order_items) |
| GET | `/api/orders/my.php` | customer | Orders for current logged-in customer |
| GET | `/api/orders/item.php?id=N` | admin/customer | Single order + line items |
| PUT | `/api/orders/item.php?id=N` | admin/rider | Update order status |
| GET | `/api/riders/index.php` | admin | List all users with role=rider |
| PUT | `/api/riders/assign.php` | admin | Assign rider_id to an order |
| GET | `/api/stats/index.php` | admin | Count totals for KPI dashboard cards |

### 5.3 Full Endpoint Implementations

#### `backend/api/auth/login.php`

```php
<?php
require_once '../../helpers/response.php';
require_once '../../helpers/validate.php';
require_once '../../config/db.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') error_response('Method not allowed', 405);

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) error_response('Invalid JSON body');

require_fields($body, ['email', 'password']);

$email = trim($body['email']);
$pass  = $body['password'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_response('Invalid email format');
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($pass, $user['password'])) {
    error_response('Invalid email or password', 401);
}

session_start();
$_SESSION['user_id'] = $user['id'];
$_SESSION['role']    = $user['role'];
$_SESSION['name']    = $user['name'];

json_response([
    'id'    => $user['id'],
    'name'  => $user['name'],
    'role'  => $user['role'],
    'email' => $user['email'],
]);
```

#### `backend/api/auth/register.php`

```php
<?php
require_once '../../helpers/response.php';
require_once '../../helpers/validate.php';
require_once '../../config/db.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') error_response('Method not allowed', 405);

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) error_response('Invalid JSON body');

require_fields($body, ['name', 'email', 'password']);

$name  = sanitize($body['name']);
$email = trim($body['email']);
$pass  = $body['password'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) error_response('Invalid email format');
if (strlen($pass) < 6) error_response('Password must be at least 6 characters');
if (strlen($name) < 2) error_response('Name must be at least 2 characters');

$hash = password_hash($pass, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "customer")');
    $stmt->execute([$name, $email, $hash]);
    $id = $pdo->lastInsertId();
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        error_response('Email is already registered', 409);
    }
    error_response('Registration failed. Please try again.', 500);
}

session_start();
$_SESSION['user_id'] = $id;
$_SESSION['role']    = 'customer';
$_SESSION['name']    = $name;

json_response(['id' => $id, 'name' => $name, 'role' => 'customer', 'email' => $email], 201);
```

#### `backend/api/auth/session.php`

```php
<?php
require_once '../../helpers/response.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

session_start();

if (!isset($_SESSION['user_id'])) {
    error_response('Not authenticated', 401);
}

json_response([
    'id'   => $_SESSION['user_id'],
    'name' => $_SESSION['name'],
    'role' => $_SESSION['role'],
]);
```

#### `backend/api/auth/logout.php`

```php
<?php
require_once '../../helpers/response.php';
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

session_start();
session_destroy();
json_response(['message' => 'Logged out successfully']);
```

#### `backend/api/menu/index.php`

```php
<?php
require_once '../../../helpers/response.php';
require_once '../../../helpers/auth.php';
require_once '../../../helpers/validate.php';
require_once '../../../config/db.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query('SELECT * FROM menu_items ORDER BY category, name');
    json_response($stmt->fetchAll());
}

if ($method === 'POST') {
    require_role('admin');
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) error_response('Invalid JSON body');
    require_fields($body, ['name', 'price', 'category']);

    $name      = sanitize($body['name']);
    $price     = (float)$body['price'];
    $category  = sanitize($body['category']);
    $desc      = sanitize($body['description'] ?? '');
    $image_url = sanitize($body['image_url'] ?? '');
    $available = isset($body['available']) ? (int)$body['available'] : 1;

    if ($price <= 0) error_response('Price must be greater than 0');

    $stmt = $pdo->prepare(
        'INSERT INTO menu_items (name, description, price, category, image_url, available)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$name, $desc, $price, $category, $image_url, $available]);
    json_response(['id' => $pdo->lastInsertId(), 'message' => 'Menu item created'], 201);
}

error_response('Method not allowed', 405);
```

#### `backend/api/menu/item.php`

```php
<?php
require_once '../../../helpers/response.php';
require_once '../../../helpers/auth.php';
require_once '../../../helpers/validate.php';
require_once '../../../config/db.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, PUT, DELETE, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) error_response('Invalid or missing item ID');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->prepare('SELECT * FROM menu_items WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) error_response('Menu item not found', 404);
    json_response($item);
}

if ($method === 'PUT') {
    require_role('admin');
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) error_response('Invalid JSON body');

    $stmt = $pdo->prepare('SELECT id FROM menu_items WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) error_response('Menu item not found', 404);

    $fields = [];
    $values = [];
    $allowed = ['name', 'description', 'price', 'category', 'image_url', 'available'];
    foreach ($allowed as $f) {
        if (isset($body[$f])) {
            $fields[] = "$f = ?";
            $values[] = ($f === 'price') ? (float)$body[$f] : sanitize((string)$body[$f]);
        }
    }
    if (empty($fields)) error_response('No valid fields provided to update');

    $values[] = $id;
    $pdo->prepare('UPDATE menu_items SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($values);
    json_response(['message' => 'Menu item updated']);
}

if ($method === 'DELETE') {
    require_role('admin');
    $stmt = $pdo->prepare('SELECT id FROM menu_items WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) error_response('Menu item not found', 404);

    $pdo->prepare('DELETE FROM menu_items WHERE id = ?')->execute([$id]);
    json_response(['message' => 'Menu item deleted']);
}

error_response('Method not allowed', 405);
```

#### `backend/api/orders/index.php`

```php
<?php
require_once '../../../helpers/response.php';
require_once '../../../helpers/auth.php';
require_once '../../../helpers/validate.php';
require_once '../../../config/db.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    require_role('admin');
    $stmt = $pdo->query(
        'SELECT o.*, 
                u.name AS customer_name, u.phone AS customer_phone,
                r.name AS rider_name,
                COUNT(oi.id) AS item_count
         FROM orders o
         JOIN users u ON o.customer_id = u.id
         LEFT JOIN users r ON o.rider_id = r.id
         LEFT JOIN order_items oi ON o.id = oi.order_id
         GROUP BY o.id
         ORDER BY o.created_at DESC'
    );
    json_response($stmt->fetchAll());
}

if ($method === 'POST') {
    require_role('customer');
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) error_response('Invalid JSON body');
    require_fields($body, ['delivery_address', 'items']);

    if (!is_array($body['items']) || count($body['items']) === 0) {
        error_response('Order must contain at least one item');
    }

    $user    = current_user();
    $address = sanitize($body['delivery_address']);
    $notes   = sanitize($body['notes'] ?? '');
    $items   = $body['items'];

    // Validate items and calculate total
    $total = 0;
    $validatedItems = [];
    foreach ($items as $item) {
        if (!isset($item['menu_item_id'], $item['quantity'])) {
            error_response('Each item must have menu_item_id and quantity');
        }
        $qty = (int)$item['quantity'];
        if ($qty <= 0) error_response('Quantity must be at least 1');

        $stmt = $pdo->prepare('SELECT id, price, available FROM menu_items WHERE id = ?');
        $stmt->execute([(int)$item['menu_item_id']]);
        $menuItem = $stmt->fetch();
        if (!$menuItem) error_response('Menu item ID ' . (int)$item['menu_item_id'] . ' not found', 404);
        if (!$menuItem['available']) error_response('Menu item is currently unavailable');

        $total += $menuItem['price'] * $qty;
        $validatedItems[] = ['id' => $menuItem['id'], 'price' => $menuItem['price'], 'qty' => $qty];
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'INSERT INTO orders (customer_id, delivery_address, total_price, notes)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$user['id'], $address, $total, $notes]);
        $orderId = $pdo->lastInsertId();

        $stmt = $pdo->prepare(
            'INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price)
             VALUES (?, ?, ?, ?)'
        );
        foreach ($validatedItems as $vi) {
            $stmt->execute([$orderId, $vi['id'], $vi['qty'], $vi['price']]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_response('Failed to place order. Please try again.', 500);
    }

    json_response(['id' => $orderId, 'total' => $total, 'message' => 'Order placed successfully'], 201);
}

error_response('Method not allowed', 405);
```

#### `backend/api/orders/item.php`

```php
<?php
require_once '../../../helpers/response.php';
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, PUT, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) error_response('Invalid or missing order ID');

$method = $_SERVER['REQUEST_METHOD'];
$user   = current_user();

if ($method === 'GET') {
    require_role('admin', 'customer', 'rider');
    $stmt = $pdo->prepare(
        'SELECT o.*,
                u.name AS customer_name, u.phone AS customer_phone,
                r.name AS rider_name
         FROM orders o
         JOIN users u ON o.customer_id = u.id
         LEFT JOIN users r ON o.rider_id = r.id
         WHERE o.id = ?'
    );
    $stmt->execute([$id]);
    $order = $stmt->fetch();
    if (!$order) error_response('Order not found', 404);

    // Customers can only view their own orders
    if ($user['role'] === 'customer' && $order['customer_id'] != $user['id']) {
        error_response('Forbidden', 403);
    }

    $stmt = $pdo->prepare(
        'SELECT oi.*, m.name AS item_name, m.image_url
         FROM order_items oi
         JOIN menu_items m ON oi.menu_item_id = m.id
         WHERE oi.order_id = ?'
    );
    $stmt->execute([$id]);
    $order['items'] = $stmt->fetchAll();

    json_response($order);
}

if ($method === 'PUT') {
    require_role('admin', 'rider');
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body || !isset($body['status'])) error_response('Status field is required');

    $allowed = ['pending', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'];
    if (!in_array($body['status'], $allowed, true)) {
        error_response('Invalid status value');
    }

    $stmt = $pdo->prepare('SELECT id, rider_id FROM orders WHERE id = ?');
    $stmt->execute([$id]);
    $order = $stmt->fetch();
    if (!$order) error_response('Order not found', 404);

    // Riders can only update their own assigned orders
    if ($user['role'] === 'rider' && $order['rider_id'] != $user['id']) {
        error_response('Forbidden', 403);
    }

    $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$body['status'], $id]);
    json_response(['message' => 'Order status updated']);
}

error_response('Method not allowed', 405);
```

#### `backend/api/orders/my.php`

```php
<?php
require_once '../../../helpers/response.php';
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') error_response('Method not allowed', 405);

require_role('customer');
$user = current_user();

$stmt = $pdo->prepare(
    'SELECT o.*, r.name AS rider_name,
            COUNT(oi.id) AS item_count
     FROM orders o
     LEFT JOIN users r ON o.rider_id = r.id
     LEFT JOIN order_items oi ON o.id = oi.order_id
     WHERE o.customer_id = ?
     GROUP BY o.id
     ORDER BY o.created_at DESC'
);
$stmt->execute([$user['id']]);
json_response($stmt->fetchAll());
```

#### `backend/api/riders/index.php`

```php
<?php
require_once '../../../helpers/response.php';
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT id, name, email, phone FROM users WHERE role = 'rider' ORDER BY name");
    json_response($stmt->fetchAll());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../../../helpers/validate.php';
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) error_response('Invalid JSON body');
    require_fields($body, ['name', 'email', 'password']);

    if (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) error_response('Invalid email format');
    if (strlen($body['password']) < 6) error_response('Password must be at least 6 characters');

    $hash = password_hash($body['password'], PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, 'rider', ?)"
        );
        $stmt->execute([
            sanitize($body['name']),
            trim($body['email']),
            $hash,
            sanitize($body['phone'] ?? '')
        ]);
        json_response(['id' => $pdo->lastInsertId(), 'message' => 'Rider account created'], 201);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') error_response('Email already registered', 409);
        error_response('Failed to create rider account', 500);
    }
}

error_response('Method not allowed', 405);
```

#### `backend/api/riders/assign.php`

```php
<?php
require_once '../../../helpers/response.php';
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') error_response('Method not allowed', 405);

require_role('admin');

$body = json_decode(file_get_contents('php://input'), true);
if (!$body || !isset($body['order_id'], $body['rider_id'])) {
    error_response('order_id and rider_id are required');
}

$orderId  = (int)$body['order_id'];
$riderId  = (int)$body['rider_id'];

// Verify order exists
$stmt = $pdo->prepare('SELECT id FROM orders WHERE id = ?');
$stmt->execute([$orderId]);
if (!$stmt->fetch()) error_response('Order not found', 404);

// Verify rider exists and has correct role
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'rider'");
$stmt->execute([$riderId]);
if (!$stmt->fetch()) error_response('Rider not found', 404);

$pdo->prepare('UPDATE orders SET rider_id = ? WHERE id = ?')->execute([$riderId, $orderId]);
json_response(['message' => 'Rider assigned successfully']);
```

#### `backend/api/stats/index.php`

```php
<?php
require_once '../../../helpers/response.php';
require_once '../../../helpers/auth.php';
require_once '../../../config/db.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') error_response('Method not allowed', 405);

require_role('admin');

$stats = [];

$row = $pdo->query('SELECT COUNT(*) AS total FROM orders')->fetch();
$stats['total_orders'] = (int)$row['total'];

$row = $pdo->query("SELECT COUNT(*) AS cnt FROM orders WHERE status = 'pending'")->fetch();
$stats['pending_orders'] = (int)$row['cnt'];

$row = $pdo->query("SELECT COUNT(*) AS cnt FROM orders WHERE status = 'out_for_delivery'")->fetch();
$stats['active_deliveries'] = (int)$row['cnt'];

$row = $pdo->query("SELECT COALESCE(SUM(total_price), 0) AS rev FROM orders WHERE status = 'delivered'")->fetch();
$stats['total_revenue'] = (float)$row['rev'];

$row = $pdo->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'customer'")->fetch();
$stats['total_customers'] = (int)$row['cnt'];

$row = $pdo->query("SELECT COUNT(*) AS cnt FROM menu_items WHERE available = 1")->fetch();
$stats['active_menu_items'] = (int)$row['cnt'];

json_response($stats);
```

---

## 6. Frontend — Pages & Features

All pages are plain `.html` files. No frameworks, no jQuery, no build tools.

### 6.1 Global JavaScript Files

#### `frontend/js/config.js`

```javascript
// SINGLE SOURCE OF TRUTH — change this one value to switch environments
const API_BASE = 'https://fooddelivery.infinityfreeapp.com/api';
// Local dev: const API_BASE = 'http://localhost/food-delivery-backend/api';
```

#### `frontend/js/api.js`

```javascript
async function apiFetch(endpoint, method = 'GET', body = null) {
    const opts = {
        method,
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
    };
    if (body) opts.body = JSON.stringify(body);

    try {
        const res = await fetch(API_BASE + endpoint, opts);
        const data = await res.json();

        if (res.status === 401) {
            sessionStorage.removeItem('user');
            showToast('Session expired. Please log in again.', 'error');
            setTimeout(() => { window.location.href = '/index.html'; }, 1500);
            throw new Error('Unauthenticated');
        }
        if (res.status === 403) {
            showToast('You do not have permission to do this.', 'error');
            throw new Error('Forbidden');
        }
        if (!res.ok) {
            throw new Error(data.error || `Request failed (${res.status})`);
        }

        return data;
    } catch (err) {
        if (err.message === 'Failed to fetch') {
            showToast('Cannot connect to server. Check your internet connection.', 'error');
        } else if (err.message !== 'Unauthenticated' && err.message !== 'Forbidden') {
            showToast(err.message, 'error');
        }
        throw err;
    }
}
```

#### `frontend/js/auth.js`

```javascript
async function sessionGuard(requiredRole) {
    let user = JSON.parse(sessionStorage.getItem('user') || 'null');

    if (!user) {
        try {
            user = await apiFetch('/auth/session.php');
            sessionStorage.setItem('user', JSON.stringify(user));
        } catch {
            window.location.href = '/index.html';
            return null;
        }
    }

    if (requiredRole && user.role !== requiredRole) {
        window.location.href = '/index.html';
        return null;
    }

    // Display user name in nav if element exists
    const el = document.getElementById('nav-user-name');
    if (el) el.textContent = user.name;

    return user;
}

async function logout() {
    try {
        await apiFetch('/auth/logout.php', 'POST');
    } finally {
        sessionStorage.removeItem('user');
        window.location.href = '/index.html';
    }
}
```

#### `frontend/js/utils.js`

```javascript
function showToast(message, type = 'info') {
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add('toast--visible'));
    setTimeout(() => {
        toast.classList.remove('toast--visible');
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

function formatDate(timestamp) {
    return new Date(timestamp).toLocaleString('en-MY', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

function formatPrice(amount) {
    return 'RM ' + parseFloat(amount).toFixed(2);
}

function statusBadge(status) {
    const map = {
        pending:          { label: 'Pending',          cls: 'badge--amber'  },
        preparing:        { label: 'Preparing',        cls: 'badge--blue'   },
        out_for_delivery: { label: 'Out for Delivery', cls: 'badge--purple' },
        delivered:        { label: 'Delivered',        cls: 'badge--green'  },
        cancelled:        { label: 'Cancelled',        cls: 'badge--red'    },
    };
    const s = map[status] || { label: status, cls: '' };
    return `<span class="badge ${s.cls}">${s.label}</span>`;
}

function showFieldError(inputId, message) {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.classList.add('input--error');
    let err = input.parentElement.querySelector('.field-error');
    if (!err) {
        err = document.createElement('span');
        err.className = 'field-error';
        input.parentElement.appendChild(err);
    }
    err.textContent = message;
}

function clearFieldErrors() {
    document.querySelectorAll('.input--error').forEach(el => el.classList.remove('input--error'));
    document.querySelectorAll('.field-error').forEach(el => el.remove());
}

function setButtonLoading(btn, loading) {
    if (loading) {
        btn.dataset.originalText = btn.textContent;
        btn.textContent = 'Loading...';
        btn.disabled = true;
    } else {
        btn.textContent = btn.dataset.originalText || btn.textContent;
        btn.disabled = false;
    }
}
```

### 6.2 Page Feature Specifications

#### `frontend/index.html` — Login / Register

- Two panels toggled by "Sign In" / "Register" tabs (no page reload)
- **Login form:** email, password fields. On submit: client-side validate (non-empty, valid email format) → POST `/auth/login.php` → store user in `sessionStorage` → redirect by role:
  - `admin` → `/admin/dashboard.html`
  - `customer` → `/customer/menu.html`
  - `rider` → `/rider/deliveries.html`
- **Register form:** name, email, password, confirm password. Validate: all fields filled, valid email, password ≥ 6 chars, passwords match → POST `/auth/register.php` → auto-login same as above
- Show inline field errors for validation failures
- Show toast for API errors
- Button shows "Loading..." during fetch and is disabled

#### `frontend/admin/dashboard.html` — Admin Dashboard

- `sessionGuard('admin')` on page load
- Sidebar nav with links to all admin pages + logout button
- **KPI cards** (4 cards in a grid): Total Orders, Pending Orders, Active Deliveries, Total Revenue
- **Recent Orders table** (last 10): order ID, customer name, item count, total, status badge, created date
- Auto-refresh all data every 30 seconds via `setInterval`
- Show loading spinner inside cards during first fetch

#### `frontend/admin/orders.html` — All Orders

- `sessionGuard('admin')` on page load
- Filter bar: status dropdown (All / Pending / Preparing / Out for Delivery / Delivered / Cancelled)
- Table columns: ID, Customer, Items, Total, Status badge, Rider, Date, Actions
- **Status update:** dropdown per row → on change → PUT `/orders/item.php?id=N` → update badge in place (no reload)
- **Assign rider:** dropdown per row (populated from `/riders/index.php`) → on change → PUT `/riders/assign.php`
- **Expand row:** click row to show order items detail below it
- Empty state: "No orders found" message when filtered list is empty

#### `frontend/admin/menu.html` — Menu Management

- `sessionGuard('admin')` on page load
- Grid of menu item cards. Each card: image (or placeholder), name, category, price, availability toggle, Edit button, Delete button
- **Add Item button** → opens modal with form: name, description, price, category, image URL, available toggle
- **Edit button** → opens same modal pre-filled with item data
- **Delete button** → confirmation dialog ("Are you sure?") → DELETE `/menu/item.php?id=N` → remove card
- **Availability toggle** → PUT `/menu/item.php?id=N` with `{available: 0|1}`
- Image URL field: show live preview of image below the input
- Category filter tabs above grid

#### `frontend/admin/riders.html` — Rider Management

- `sessionGuard('admin')` on page load
- Table of all riders: name, email, phone
- **Add Rider button** → modal form: name, email, password, phone → POST `/riders/index.php`
- New rider appears in table immediately after creation

#### `frontend/customer/menu.html` — Browse Menu

- `sessionGuard('customer')` on page load
- Fetch all available menu items from `/menu/index.php`
- Category filter tabs (All + unique categories from data)
- Menu item cards: image, name, description, price, **Add to Cart** button
- Cart stored in `localStorage` as JSON array of `{menu_item_id, name, price, quantity}`
- Cart icon in nav shows item count badge
- Clicking Add to Cart: if item already in cart, increment quantity. Show "Added!" toast.
- Empty state if no items in selected category

#### `frontend/customer/cart.html` — Cart & Checkout

- `sessionGuard('customer')` on page load
- Load cart from `localStorage`. If empty, show "Your cart is empty" with link back to menu.
- Line items: item name, unit price, quantity (- and + buttons), subtotal, remove (×) button
- Quantity changes update localStorage and recalculate total
- **Order summary** section: subtotal, delivery fee (fixed RM 2.00), total
- **Delivery address** textarea (required)
- **Order notes** textarea (optional)
- **Place Order** button → validate address not empty → POST `/orders/index.php` with `{delivery_address, notes, items: [{menu_item_id, quantity}]}` → on success: clear cart from localStorage, show success toast, redirect to `/customer/track.html`

#### `frontend/customer/track.html` — Order Tracking

- `sessionGuard('customer')` on page load
- Fetch all customer orders from `/orders/my.php`
- List of orders sorted newest first. Click order to expand detail.
- **Status progress bar** with 4 steps: Pending → Preparing → Out for Delivery → Delivered. Highlight current step.
- Show rider name when assigned
- Show order items list with quantities and prices
- Auto-refresh every 20 seconds to poll for status updates
- Cancelled orders shown with strikethrough style

#### `frontend/rider/deliveries.html` — Rider Deliveries

- `sessionGuard('rider')` on page load
- Two tabs: **Active** (pending, preparing, out_for_delivery) and **Completed** (delivered)
- Fetch orders assigned to current rider — filter from `/orders/index.php` (rider calls this — note: adjust backend to allow rider to call `/orders/my-deliveries.php` or filter in frontend from a rider-specific endpoint)

> **Note for Claude Code:** Add `backend/api/orders/assigned.php` — GET endpoint that requires role=rider and returns orders where `rider_id = current_user_id`.

- Each order card: customer name, delivery address, order items list, total, status badge
- **Mark as Out for Delivery** button (when status is preparing) → PUT status update
- **Mark as Delivered** button (when status is out_for_delivery) → PUT status update
- Completed tab shows delivered orders

---

## 7. Authentication & Sessions

PHP `$_SESSION` handles server-side auth. The frontend mirrors minimal info in `sessionStorage`.

### Session Guard Pattern (all protected pages)

Every protected HTML page must call `sessionGuard()` at the top of its init script before rendering any content:

```javascript
// Runs immediately on page load — before init
(async () => {
    const user = await sessionGuard('admin'); // pass required role
    if (!user) return; // sessionGuard redirects automatically
    initPage(user);
})();

async function initPage(user) {
    // all page logic here
}
```

### Password Security

- All passwords stored as bcrypt hashes: `password_hash($pass, PASSWORD_DEFAULT)`
- Verify with: `password_verify($input, $hash)`
- Never log or return passwords in any API response
- Never store passwords in sessionStorage or localStorage

---

## 8. Error Handling Requirements

Every error must produce a **user-visible message**. No blank screens, no raw PHP errors, no console-only failures.

### Frontend — Required Error Cases

| Situation | Required Handling |
|---|---|
| Empty required field | Inline red text below the field before fetch is called |
| Invalid email format | Inline error: "Please enter a valid email address" |
| Password too short | Inline error: "Password must be at least 6 characters" |
| Passwords don't match | Inline error on confirm password field |
| Fetch network failure | Toast (error): "Cannot connect to server. Check your internet connection." |
| API returns error JSON | Toast (error): display `data.error` from response |
| HTTP 401 response | Clear sessionStorage, toast "Session expired. Please log in again.", redirect after 1.5s |
| HTTP 403 response | Toast (error): "You do not have permission to do this." |
| Empty list result | Visible empty state text in the container — never a blank div |
| Cart empty on checkout | Block API call, show inline error "Your cart is empty" |
| Placing order while offline | Catch fetch failure, show toast, do not clear cart |

### Backend — Required Error Cases

| Situation | Required Handling |
|---|---|
| DB connection fails | HTTP 500: `{"error": "Database connection failed"}` |
| Missing required POST field | HTTP 400: `{"error": "Field 'X' is required"}` |
| Invalid email format | HTTP 400: `{"error": "Invalid email format"}` |
| Wrong login credentials | HTTP 401: `{"error": "Invalid email or password"}` (never say which field) |
| Resource not found | HTTP 404: `{"error": "X not found"}` |
| Duplicate email | HTTP 409: `{"error": "Email is already registered"}` |
| Not authenticated | HTTP 401: `{"error": "Unauthenticated. Please log in."}` |
| Wrong role | HTTP 403: `{"error": "Forbidden. You do not have permission."}` |
| Invalid status value | HTTP 400: `{"error": "Invalid status value"}` |
| `display_errors` | Must be `off` in `.htaccess` — PHP errors must never appear in responses |

---

## 9. Build & Deployment Instructions

### First-Time Setup (follow in order)

1. Create the project folder structure as per Section 3
2. Set up InfinityFree account, create DB, note credentials (Section 2.2 steps 1–3)
3. Run the SQL schema in phpMyAdmin (Section 4)
4. Fill in `backend/config/db.php` with your credentials
5. Upload all `backend/` contents to InfinityFree `htdocs/` via File Manager or FileZilla
6. Test backend: visit `https://yoursubdomain.infinityfreeapp.com/api/menu/index.php` — should return `[]`
7. Set `API_BASE` in `frontend/js/config.js` to your InfinityFree URL
8. `npm install -g firebase-tools`
9. `firebase login`
10. `cd frontend && firebase init hosting` (public dir: `.`, not SPA, don't overwrite index.html)
11. `firebase deploy` → note the live URL, share with all group members

### Regular Updates

```bash
# Frontend update
cd frontend/
firebase deploy

# Backend update — Option A: File Manager in VistaPanel (upload and overwrite)
# Backend update — Option B: FileZilla FTP drag-and-drop overwrite
```

### VS Code FTP Extension (recommended for backend updates)

Install **FTP-Simple** extension. Create `.vscode/ftp-simple.json`:

```json
[{
  "name": "InfinityFree",
  "host": "ftpupload.net",
  "port": 21,
  "type": "ftp",
  "username": "your_ftp_username",
  "password": "your_ftp_password",
  "remotePath": "/htdocs"
}]
```

Right-click any backend file → **FTP-Simple: Save** to upload instantly.

---

## 10. UI / UX Requirements

### CSS Custom Properties (define in `style.css :root`)

```css
:root {
    --color-primary:     #1A3C5E;
    --color-primary-light: #2D5A8E;
    --color-accent:      #E85D04;
    --color-accent-light: #FB8B24;
    --color-success:     #2D6A4F;
    --color-bg:          #F8FAFC;
    --color-surface:     #FFFFFF;
    --color-border:      #E2E8F0;
    --color-text:        #1E293B;
    --color-text-muted:  #64748B;
    --color-danger:      #DC2626;

    --radius-sm:   6px;
    --radius-md:   12px;
    --radius-lg:   20px;

    --shadow-sm:   0 1px 3px rgba(0,0,0,0.08);
    --shadow-md:   0 4px 16px rgba(0,0,0,0.10);
    --shadow-lg:   0 8px 32px rgba(0,0,0,0.12);

    --transition:  0.2s ease;

    --sidebar-w:   240px;
}
```

### Typography

- Font: **Inter** from Google Fonts (`weights=400;500;600;700`)
- Import in `style.css`: `@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');`
- Base font size: `16px`, line-height: `1.6`

### Status Badges

```css
.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.02em;
}
.badge--amber  { background: #FEF3C7; color: #92400E; }
.badge--blue   { background: #DBEAFE; color: #1E40AF; }
.badge--purple { background: #EDE9FE; color: #5B21B6; }
.badge--green  { background: #D1FAE5; color: #065F46; }
.badge--red    { background: #FEE2E2; color: #991B1B; }
```

### Toast Notifications

```css
.toast {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 9999;
    padding: 14px 20px;
    border-radius: var(--radius-md);
    font-size: 0.875rem;
    font-weight: 500;
    box-shadow: var(--shadow-lg);
    transform: translateX(120%);
    transition: transform 0.3s ease;
    max-width: 360px;
}
.toast--visible     { transform: translateX(0); }
.toast--info        { background: #1E293B; color: #fff; }
.toast--error       { background: #DC2626; color: #fff; }
.toast--success     { background: #2D6A4F; color: #fff; }
```

### Layout

- Admin pages: fixed sidebar (`var(--sidebar-w)` wide) + main content area
- Sidebar collapses to hamburger on mobile (`< 768px`) — overlay drawer
- Cards: `background: var(--color-surface); border-radius: var(--radius-md); box-shadow: var(--shadow-sm); padding: 24px;`
- KPI cards in 4-column grid (2 on tablet, 1 on mobile)

### "Live" Interface Requirements

- Admin dashboard auto-refreshes KPI counts every **30 seconds** using `setInterval`
- Customer track page auto-polls order status every **20 seconds**
- Status badge and progress bar update **in-place** without page reload on data refresh
- Toast notifications slide in from top-right for **all** success and error events
- Loading spinner (CSS `@keyframes spin`) shown inside buttons and data containers during fetch
- Smooth `transition: var(--transition)` on all hover states

### Form UX

- Inline error messages appear **below** each field on validation failure (`.field-error` span, `color: var(--color-danger)`, `font-size: 0.8rem`)
- Submit buttons show "Loading..." and are `disabled` during fetch
- On success: form resets, success toast shown
- Modals used for Add/Edit forms — no page navigation for CRUD operations
- Modal closes on overlay click and on × button

### Loading Spinner

```css
.spinner {
    width: 20px; height: 20px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
    display: inline-block;
    vertical-align: middle;
    margin-right: 8px;
}
@keyframes spin { to { transform: rotate(360deg); } }
```

---

## 11. File-by-File Build Order

Build in this exact sequence. Each phase depends on the previous.

### Phase 1 — Backend Foundation
1. `backend/.htaccess`
2. `backend/config/db.php`
3. `backend/helpers/response.php`
4. `backend/helpers/auth.php`
5. `backend/helpers/validate.php`

### Phase 2 — Auth API
6. `backend/api/auth/login.php`
7. `backend/api/auth/logout.php`
8. `backend/api/auth/register.php`
9. `backend/api/auth/session.php`

### Phase 3 — Core APIs
10. `backend/api/menu/index.php`
11. `backend/api/menu/item.php`
12. `backend/api/orders/index.php`
13. `backend/api/orders/item.php`
14. `backend/api/orders/my.php`
15. `backend/api/orders/assigned.php` *(rider-specific, requires role=rider, returns orders where rider_id = current user)*
16. `backend/api/riders/index.php`
17. `backend/api/riders/assign.php`
18. `backend/api/stats/index.php`

### Phase 4 — Frontend Foundation
19. `frontend/js/config.js`
20. `frontend/js/api.js`
21. `frontend/js/auth.js`
22. `frontend/js/utils.js`
23. `frontend/css/style.css` *(variables, reset, typography, toast, spinner, badges, buttons, forms, modals)*
24. `frontend/css/components.css` *(cards, tables, sidebar, nav, empty states)*

### Phase 5 — Login Page
25. `frontend/index.html`
26. `frontend/css/pages/auth.css`

### Phase 6 — Admin Pages
27. `frontend/admin/dashboard.html`
28. `frontend/admin/orders.html`
29. `frontend/admin/menu.html`
30. `frontend/admin/riders.html`
31. `frontend/css/pages/admin.css`

### Phase 7 — Customer Pages
32. `frontend/customer/menu.html`
33. `frontend/customer/cart.html`
34. `frontend/customer/track.html`
35. `frontend/css/pages/customer.css`

### Phase 8 — Rider Page
36. `frontend/rider/deliveries.html`
37. `frontend/css/pages/rider.css`

### Phase 9 — Firebase Config
38. `frontend/firebase.json`
39. `frontend/.firebaserc`

---

## Appendix — VS Code Extensions

| Extension | Publisher | Purpose |
|---|---|---|
| PHP Intelephense | bmewburn | PHP intellisense and error highlighting |
| Live Server | ritwickdey | Local frontend preview with auto-reload |
| MySQL (Database Client) | cweijan | Browse DB from VS Code sidebar |
| Prettier | esbenp | Auto-format HTML, CSS, JS on save |
| FTP-Simple | humy2733 | Upload backend files to InfinityFree |
| Firebase | toba | Deploy Firebase from VS Code terminal |
| Thunder Client | rangav | Test PHP API endpoints inside VS Code |

### `.vscode/settings.json`

```json
{
  "editor.formatOnSave": true,
  "editor.defaultFormatter": "esbenp.prettier-vscode",
  "[php]": {
    "editor.defaultFormatter": "bmewburn.vscode-intelephense-client"
  },
  "liveServer.settings.port": 5500,
  "liveServer.settings.root": "/frontend"
}
```

---

*End of Blueprint — 39 files total. Build every file completely. No stubs, no placeholders.*
