CREATE DATABASE IF NOT EXISTS travel_guide CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE travel_guide;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'scout', 'user') NOT NULL DEFAULT 'user',
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    profile_picture VARCHAR(255) DEFAULT NULL,
    remember_token VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scout_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    short_history TEXT NOT NULL,
    country VARCHAR(100) NOT NULL,
    genre VARCHAR(50) NOT NULL,
    cost_level ENUM('low', 'medium', 'high') NOT NULL,
    travel_medium_info TEXT NOT NULL,
    image_paths TEXT DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (scout_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS post_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scout_id INT NOT NULL,
    post_data JSON NOT NULL,
    original_post_id INT DEFAULT NULL,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    reject_reason VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (scout_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (original_post_id) REFERENCES posts(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_wishlist (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS cost_estimates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL UNIQUE,
    base_cost DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'USD',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

INSERT INTO users (name, email, password_hash, role, is_verified)
SELECT 'Site Admin', 'admin@travelguide.local',
       '$2y$12$haVW00GFjnG7ODS6iXWT8eaUHWRIFEQp4wyWZxFOF5OtIeFVpqCXe',
       'admin', 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@travelguide.local');

INSERT INTO users (name, email, password_hash, role, is_verified)
SELECT 'Alex Scout', 'scout@travelguide.local',
       '$2y$12$haVW00GFjnG7ODS6iXWT8eaUHWRIFEQp4wyWZxFOF5OtIeFVpqCXe',
       'scout', 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'scout@travelguide.local');

INSERT INTO users (name, email, password_hash, role, is_verified)
SELECT 'Jamie Traveler', 'user@travelguide.local',
       '$2y$12$haVW00GFjnG7ODS6iXWT8eaUHWRIFEQp4wyWZxFOF5OtIeFVpqCXe',
       'user', 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'user@travelguide.local');

INSERT INTO posts (scout_id, title, short_history, country, genre, cost_level, travel_medium_info, status)
SELECT u.id, 'Santorini Sunsets', 'Iconic whitewashed villages and volcanic caldera views attract millions each year.', 'Greece', 'beach', 'high', 'Fly to Athens, ferry to Santorini.', 'approved'
FROM users u WHERE u.email = 'scout@travelguide.local'
AND NOT EXISTS (SELECT 1 FROM posts WHERE title = 'Santorini Sunsets');

INSERT INTO posts (scout_id, title, short_history, country, genre, cost_level, travel_medium_info, status)
SELECT u.id, 'Kyoto Temple Trail', 'Ancient shrines and bamboo groves showcase Japan cultural heritage.', 'Japan', 'historical', 'medium', 'Train from Tokyo via Shinkansen.', 'approved'
FROM users u WHERE u.email = 'scout@travelguide.local'
AND NOT EXISTS (SELECT 1 FROM posts WHERE title = 'Kyoto Temple Trail');

INSERT INTO posts (scout_id, title, short_history, country, genre, cost_level, travel_medium_info, status)
SELECT u.id, 'Banff Mountain Escape', 'Rocky Mountain lakes and wildlife in a UNESCO-listed park.', 'Canada', 'mountain', 'low', 'Fly to Calgary, rent car to Banff.', 'approved'
FROM users u WHERE u.email = 'scout@travelguide.local'
AND NOT EXISTS (SELECT 1 FROM posts WHERE title = 'Banff Mountain Escape');

INSERT INTO cost_estimates (post_id, base_cost, currency)
SELECT p.id, 3000, 'USD' FROM posts p WHERE p.title = 'Santorini Sunsets'
AND NOT EXISTS (SELECT 1 FROM cost_estimates ce WHERE ce.post_id = p.id);

INSERT INTO cost_estimates (post_id, base_cost, currency)
SELECT p.id, 1500, 'USD' FROM posts p WHERE p.title = 'Kyoto Temple Trail'
AND NOT EXISTS (SELECT 1 FROM cost_estimates ce WHERE ce.post_id = p.id);

INSERT INTO cost_estimates (post_id, base_cost, currency)
SELECT p.id, 500, 'USD' FROM posts p WHERE p.title = 'Banff Mountain Escape'
AND NOT EXISTS (SELECT 1 FROM cost_estimates ce WHERE ce.post_id = p.id);
