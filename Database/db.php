<?php
// $host = "sql102.infinityfree.com";
// $username = "if0_41332525";
// $password = "siZgdyZB5Uvgo5m";
// $dbname = "if0_41332525_anime_cube";

$host = "localhost";
$username = "root";
$password = "";
$dbname = "anime_cube"; // Create connection to MySQL server first (without database)
$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Create database if it doesn't exist
$conn->query(
    "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;",
);

// 2. Select the database
$conn->select_db($dbname);

// 3. Create users table
$conn->query("CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `address` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 4. Create anime table
$conn->query("CREATE TABLE IF NOT EXISTS `anime` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `title_english` VARCHAR(255),
  `title_japanese` VARCHAR(255),
  `image` VARCHAR(500) NOT NULL,
  `description` TEXT NOT NULL,
  `synopsis` TEXT,
  `type` ENUM('TV', 'Movie', 'OVA', 'ONA', 'Special') DEFAULT 'TV',
  `episodes` INT(11) DEFAULT 0,
  `status` ENUM('Airing', 'Finished Airing', 'Not yet aired') DEFAULT 'Finished Airing',
  `aired_from` DATE,
  `aired_to` DATE,
  `premiered` VARCHAR(50),
  `broadcast` VARCHAR(100),
  `producers` TEXT,
  `licensors` TEXT,
  `studios` TEXT,
  `source` VARCHAR(100),
  `genres` TEXT,
  `themes` TEXT,
  `demographic` VARCHAR(50),
  `duration` VARCHAR(50),
  `rating` VARCHAR(50),
  `score` DECIMAL(3,2) DEFAULT 0.00,
  `scored_by` INT(11) DEFAULT 0,
  `rank` INT(11),
  `popularity` INT(11),
  `members` INT(11) DEFAULT 0,
  `favorites` INT(11) DEFAULT 0,
  `trailer_url` VARCHAR(500),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 5. Create user_favorites table
$conn->query("CREATE TABLE IF NOT EXISTS `user_favorites` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `anime_id` INT(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_favorite` (`user_id`, `anime_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`anime_id`) REFERENCES `anime`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 6. Create user_watchlist table
$conn->query("CREATE TABLE IF NOT EXISTS `user_watchlist` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `anime_id` INT(11) NOT NULL,
  `status` ENUM('Watching', 'Completed', 'On Hold', 'Dropped', 'Plan to Watch') DEFAULT 'Plan to Watch',
  `episodes_watched` INT(11) DEFAULT 0,
  `score` INT(11) DEFAULT NULL CHECK (`score` >= 1 AND `score` <= 10),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_watchlist` (`user_id`, `anime_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`anime_id`) REFERENCES `anime`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 7. Create qa_posts table
$conn->query("CREATE TABLE IF NOT EXISTS `qa_posts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `question` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
?>
