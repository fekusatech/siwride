-- Siwride App SQL Schema (MySQL)
-- Converted from Prisma schema for Laravel
-- Database: siwride

-- ============================================
-- USERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(255) NOT NULL,
    lastname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    image VARCHAR(500) NULL,
    status VARCHAR(50) DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- DRIVERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    image VARCHAR(500) NULL,
    vehicle_type VARCHAR(100) NOT NULL,
    vehicle_registration_number VARCHAR(100) NOT NULL,
    status VARCHAR(50) DEFAULT 'inactive',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- ADMIN TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- PASSWORD RESET TOKENS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token)
);

-- ============================================
-- ORDERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    driver_id INT NULL,
    pickup VARCHAR(500) NOT NULL,
    destination VARCHAR(500) NOT NULL,
    pickup_lat DOUBLE NOT NULL,
    pickup_lng DOUBLE NOT NULL,
    destination_lat DOUBLE NOT NULL,
    destination_lng DOUBLE NOT NULL,
    distance DOUBLE NOT NULL,
    duration INT NOT NULL,
    fare INT NOT NULL,
    status ENUM('PENDING', 'ASSIGNED', 'PICKING_UP', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED') DEFAULT 'PENDING',
    vehicle_type VARCHAR(100) NOT NULL,
    ride_type ENUM('DISTANCE', 'HOURLY') DEFAULT 'DISTANCE',
    ride_category_id INT NULL,
    package_id INT NULL,
    passenger_count INT DEFAULT 1,
    adults INT DEFAULT 1,
    children INT DEFAULT 0,
    car_seats INT DEFAULT 0,
    flight_number VARCHAR(50) NULL,
    notes TEXT NULL,
    pickup_datetime DATETIME NULL,
    ride_end_datetime DATETIME NULL,
    parent_order_id INT NULL UNIQUE,
    is_return_trip BOOLEAN DEFAULT FALSE,
    pickup_image_url VARCHAR(500) NULL,
    dropoff_image_url VARCHAR(500) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL,
    FOREIGN KEY (ride_category_id) REFERENCES ride_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    INDEX idx_driver_id (driver_id),
    INDEX idx_ride_category_id (ride_category_id),
    INDEX idx_package_id (package_id),
    INDEX idx_created_at (created_at)
);

-- ============================================
-- DRIVER LOCATIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS driver_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL UNIQUE,
    lat DOUBLE NOT NULL,
    lng DOUBLE NOT NULL,
    heading DOUBLE NULL,
    speed DOUBLE NULL,
    is_online BOOLEAN DEFAULT FALSE,
    last_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE,
    INDEX idx_is_online (is_online),
    INDEX idx_last_updated_at (last_updated_at)
);

-- ============================================
-- DRIVER LOCATION HISTORY TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS driver_location_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    order_id INT NULL,
    lat DOUBLE NOT NULL,
    lng DOUBLE NOT NULL,
    heading DOUBLE NULL,
    speed DOUBLE NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_driver_id (driver_id),
    INDEX idx_order_id (order_id),
    INDEX idx_created_at (created_at)
);

-- ============================================
-- RATINGS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    user_id INT NOT NULL,
    driver_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE,
    INDEX idx_driver_id (driver_id),
    INDEX idx_rating (rating)
);

-- ============================================
-- CANCELLATIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS cancellations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    cancelled_by ENUM('USER', 'DRIVER', 'ADMIN') NOT NULL,
    reason VARCHAR(500) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_cancelled_by (cancelled_by)
);

-- ============================================
-- PUSH SUBSCRIPTIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS push_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NULL,
    user_id INT NULL,
    admin_email VARCHAR(255) NULL,
    endpoint VARCHAR(500) NOT NULL UNIQUE,
    p256dh VARCHAR(500) NOT NULL,
    auth VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_driver_id (driver_id),
    INDEX idx_user_id (user_id),
    INDEX idx_admin_email (admin_email)
);

-- ============================================
-- RIDE CATEGORIES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS ride_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    pricing_type ENUM('PER_KM', 'PER_HOUR', 'FLAT') DEFAULT 'PER_KM',
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order)
);

-- ============================================
-- PACKAGES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    tier VARCHAR(50) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    icon VARCHAR(255) NULL,
    price INT NOT NULL,
    capacity INT DEFAULT 4,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES ride_categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_category_tier (category_id, tier),
    INDEX idx_category_id (category_id),
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order)
);

-- ============================================
-- PRICING ZONES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS pricing_zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    polygon JSON NOT NULL,
    color VARCHAR(20) DEFAULT '#3B82F6',
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order)
);

-- ============================================
-- ZONE ROUTES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS zone_routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    origin_zone_id INT NOT NULL,
    destination_zone_id INT NOT NULL,
    driver_commission INT DEFAULT 0,
    driver_commission_type ENUM('PERCENTAGE', 'FLAT') DEFAULT 'FLAT',
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (origin_zone_id) REFERENCES pricing_zones(id) ON DELETE CASCADE,
    FOREIGN KEY (destination_zone_id) REFERENCES pricing_zones(id) ON DELETE CASCADE,
    UNIQUE KEY unique_origin_destination (origin_zone_id, destination_zone_id),
    INDEX idx_origin_zone_id (origin_zone_id),
    INDEX idx_destination_zone_id (destination_zone_id),
    INDEX idx_is_active (is_active)
);

-- ============================================
-- ZONE ROUTE PRICES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS zone_route_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_id INT NOT NULL,
    package_id INT NOT NULL,
    price INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (route_id) REFERENCES zone_routes(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
    UNIQUE KEY unique_route_package (route_id, package_id),
    INDEX idx_route_id (route_id),
    INDEX idx_package_id (package_id)
);

-- ============================================
-- SHARING RIDE SESSIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS sharing_ride_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pickup_zone_id INT NOT NULL,
    dropoff_zone_id INT NOT NULL,
    package_id INT NOT NULL,
    driver_id INT NULL,
    status ENUM('WAITING_PASSENGERS', 'READY', 'ASSIGNED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED', 'EXPIRED') DEFAULT 'WAITING_PASSENGERS',
    scheduled_pickup_time DATETIME NOT NULL,
    time_window_minutes INT DEFAULT 60,
    min_passengers INT DEFAULT 2,
    max_passengers INT DEFAULT 4,
    fare_per_person INT NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pickup_zone_id) REFERENCES pricing_zones(id),
    FOREIGN KEY (dropoff_zone_id) REFERENCES pricing_zones(id),
    FOREIGN KEY (package_id) REFERENCES packages(id),
    FOREIGN KEY (driver_id) REFERENCES drivers(id),
    INDEX idx_status (status),
    INDEX idx_pickup_dropoff (pickup_zone_id, dropoff_zone_id),
    INDEX idx_scheduled_pickup_time (scheduled_pickup_time),
    INDEX idx_expires_at (expires_at)
);

-- ============================================
-- SHARING RIDE PASSENGERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS sharing_ride_passengers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    order_id INT NOT NULL UNIQUE,
    pickup_order INT DEFAULT 0,
    dropoff_order INT DEFAULT 0,
    is_picked_up BOOLEAN DEFAULT FALSE,
    is_dropped_off BOOLEAN DEFAULT FALSE,
    pickup_time DATETIME NULL,
    dropoff_time DATETIME NULL,
    pickup_image_url VARCHAR(500) NULL,
    dropoff_image_url VARCHAR(500) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sharing_ride_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_session_id (session_id)
);

-- ============================================
-- SHARING RIDE CONFIG TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS sharing_ride_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    default_time_window INT DEFAULT 60,
    default_min_passengers INT DEFAULT 2,
    default_max_passengers INT DEFAULT 4,
    auto_expire_enabled BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- ADMIN NOTIFICATIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS admin_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('NEW_ORDER', 'ORDER_CANCELLED', 'DRIVER_ASSIGNED', 'ORDER_COMPLETED', 'SHARING_RIDE_READY', 'SHARING_RIDE_EXPIRED') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    order_id INT NULL,
    session_id INT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
);

-- ============================================
-- CHAT ROOMS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS chat_rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    user_id INT NOT NULL,
    driver_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_driver_id (driver_id),
    INDEX idx_is_active (is_active),
    INDEX idx_created_at (created_at)
);

-- ============================================
-- CHAT MESSAGES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    sender_type ENUM('USER', 'DRIVER') NOT NULL,
    sender_id INT NOT NULL,
    message VARCHAR(200) NULL,
    image_url VARCHAR(500) NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES chat_rooms(id) ON DELETE CASCADE,
    INDEX idx_room_id (room_id),
    INDEX idx_sender_type (sender_type),
    INDEX idx_created_at (created_at)
);

-- ============================================
-- SUPPORT CHATS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS support_chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    driver_id INT NULL,
    admin_id INT NULL,
    status ENUM('OPEN', 'RESOLVED', 'CLOSED') DEFAULT 'OPEN',
    subject VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(id),
    INDEX idx_user_id (user_id),
    INDEX idx_driver_id (driver_id),
    INDEX idx_admin_id (admin_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- ============================================
-- SUPPORT MESSAGES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS support_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT NOT NULL,
    sender_type VARCHAR(20) NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NULL,
    image_url VARCHAR(500) NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_id) REFERENCES support_chats(id) ON DELETE CASCADE,
    INDEX idx_chat_id (chat_id),
    INDEX idx_sender_type (sender_type),
    INDEX idx_created_at (created_at)
);

-- ============================================
-- PAYMENTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    external_id VARCHAR(255) NOT NULL UNIQUE,
    transaction_id VARCHAR(255) NULL,
    amount INT NOT NULL,
    method ENUM('QRIS', 'GOPAY', 'SHOPEEPAY', 'OVO', 'DANA', 'BANK_TRANSFER_BCA', 'BANK_TRANSFER_BNI', 'BANK_TRANSFER_BRI', 'BANK_TRANSFER_MANDIRI', 'BANK_TRANSFER_PERMATA', 'CREDIT_CARD', 'OTHER') NULL,
    status ENUM('PENDING', 'PAID', 'EXPIRED', 'FAILED', 'REFUNDED', 'PARTIALLY_REFUNDED') DEFAULT 'PENDING',
    payment_url VARCHAR(500) NULL,
    va_number VARCHAR(100) NULL,
    qr_code_url VARCHAR(500) NULL,
    paid_at DATETIME NULL,
    expired_at DATETIME NULL,
    metadata JSON NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_external_id (external_id),
    INDEX idx_created_at (created_at)
);

-- ============================================
-- REFUNDS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS refunds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL,
    external_id VARCHAR(255) NULL UNIQUE,
    amount INT NOT NULL,
    reason VARCHAR(500) NOT NULL,
    status ENUM('PENDING', 'PENDING_MANUAL', 'PROCESSING', 'COMPLETED', 'FAILED') DEFAULT 'PENDING',
    processed_at DATETIME NULL,
    processed_by VARCHAR(255) NULL,
    metadata JSON NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    INDEX idx_payment_id (payment_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- ============================================
-- DRIVER BANK ACCOUNTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS driver_bank_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL UNIQUE,
    bank_code VARCHAR(50) NOT NULL,
    bank_name VARCHAR(100) NOT NULL,
    account_number VARCHAR(100) NOT NULL,
    account_name VARCHAR(255) NOT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE
);

-- ============================================
-- DRIVER PAYOUTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS driver_payouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    amount INT NOT NULL,
    bank_code VARCHAR(50) NOT NULL,
    bank_name VARCHAR(100) NOT NULL,
    account_number VARCHAR(100) NOT NULL,
    account_name VARCHAR(255) NOT NULL,
    notes TEXT NULL,
    status ENUM('PENDING', 'PROCESSING', 'COMPLETED', 'FAILED') DEFAULT 'PENDING',
    processed_at DATETIME NULL,
    processed_by VARCHAR(255) NULL,
    proof_image_url VARCHAR(500) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES drivers(id),
    INDEX idx_driver_id (driver_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);
