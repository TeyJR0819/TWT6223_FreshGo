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

-- SEED: default admin account (password: FreshGo#Adm1n26)
INSERT INTO users (name, email, password, role) VALUES
  ('Admin', 'admin@food.com',
   '$2b$10$3GfYosrRD1VdxCFUY/9RsuWHBDxSsfu4EEs7rMH5KMRLkpdvfdATK',
   'admin');

-- SEED: sample menu items
INSERT INTO menu_items (name, description, price, category, available) VALUES
  ('Nasi Lemak', 'Coconut rice with sambal, egg, and anchovies', 8.50, 'Rice', 1),
  ('Char Kway Teow', 'Stir-fried flat noodles with prawns and egg', 10.00, 'Noodles', 1),
  ('Roti Canai', 'Flaky flatbread with curry dipping sauce', 3.50, 'Bread', 1),
  ('Teh Tarik', 'Pulled milk tea', 2.50, 'Drinks', 1),
  ('Milo Ais', 'Iced Milo drink', 3.00, 'Drinks', 1);
