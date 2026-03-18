-- ================================
-- ANIME CUBE DATABASE SCHEMA
-- ================================

CREATE DATABASE IF NOT EXISTS `anime_cube`;
USE `anime_cube`;

-- ================================
-- USERS TABLE
-- ================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `address` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================
-- ANIME TABLE
-- ================================
CREATE TABLE IF NOT EXISTS `anime` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================
-- USER FAVORITES TABLE
-- ================================
CREATE TABLE IF NOT EXISTS `user_favorites` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `anime_id` INT(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_favorite` (`user_id`, `anime_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`anime_id`) REFERENCES `anime`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================
-- USER WATCHLIST TABLE
-- ================================
CREATE TABLE IF NOT EXISTS `user_watchlist` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================
-- SAMPLE ANIME DATA
-- ================================
INSERT INTO `anime` (`title`, `title_english`, `title_japanese`, `image`, `description`, `synopsis`, `type`, `episodes`, `status`, `aired_from`, `aired_to`, `premiered`, `genres`, `themes`, `source`, `duration`, `rating`, `score`, `studios`) VALUES
('Naruto', 'Naruto', 'ナルト', './public/anime1.jpg', 'A young ninja seeks recognition and dreams of becoming the Hokage.', 'Naruto Uzumaki is a young ninja who seeks recognition from his peers and dreams of becoming the Hokage, the leader of his village. The story follows Naruto as he goes through various trials and tribulations as a ninja.', 'TV', 220, 'Finished Airing', '2002-10-03', '2007-02-08', 'Fall 2002', 'Action, Adventure, Fantasy', 'Martial Arts, Ninja', 'Manga', '23 min per ep', 'PG-13', 7.99, 'Studio Pierrot'),

('Attack on Titan', 'Attack on Titan', '進撃の巨人', './public/anime1.jpg', 'Humanity fights for survival against giant humanoid Titans.', 'Several hundred years ago, humans were nearly exterminated by Titans. A small percentage of humanity survived by walling themselves in a city protected by extremely high walls. One day, a Titan breaks through the wall and begins to attack the humans.', 'TV', 25, 'Finished Airing', '2013-04-07', '2013-09-29', 'Spring 2013', 'Action, Drama, Fantasy', 'Gore, Military, Survival', 'Manga', '24 min per ep', 'R', 8.54, 'Wit Studio'),

('Death Note', 'Death Note', 'デスノート', './public/anime1.jpg', 'A high school student discovers a supernatural notebook that can kill anyone.', 'A high school student named Light Yagami discovers a supernatural notebook, the "Death Note", dropped on Earth by a god of death named Ryuk. The Death Note grants its user the ability to kill anyone whose name and face they know.', 'TV', 37, 'Finished Airing', '2006-10-04', '2007-06-27', 'Fall 2006', 'Mystery, Psychological, Supernatural, Thriller', 'Detective, Psychological', 'Manga', '23 min per ep', 'R', 8.62, 'Madhouse'),

('One Piece', 'One Piece', 'ワンピース', './public/anime1.jpg', 'A young pirate searches for the ultimate treasure to become Pirate King.', 'Monkey D. Luffy sets off on an adventure with his pirate crew in hopes of finding the greatest treasure ever, known as the "One Piece". Along the way, he makes new friends and encounters dangerous enemies.', 'TV', 1000, 'Airing', '1999-10-20', NULL, 'Fall 1999', 'Action, Adventure, Fantasy', 'Pirates, Super Power', 'Manga', '24 min per ep', 'PG-13', 8.71, 'Toei Animation'),

('My Hero Academia', 'My Hero Academia', '僕のヒーローアカデミア', './public/anime1.jpg', 'A boy born without superpowers dreams of becoming a hero.', 'In a world where most humans have superpowers called "Quirks", Izuku Midoriya dreams of becoming a hero despite being born without powers. His life changes when he meets All Might, the greatest hero of all time.', 'TV', 13, 'Finished Airing', '2016-04-03', '2016-06-26', 'Spring 2016', 'Action, Fantasy', 'School, Super Power', 'Manga', '24 min per ep', 'PG-13', 7.87, 'Bones'),

('Demon Slayer', 'Demon Slayer: Kimetsu no Yaiba', '鬼滅の刃', './public/anime1.jpg', 'A boy fights demons to save his sister and avenge his family.', 'After his family was attacked by demons, Tanjiro Kamado begins his journey to become a demon slayer to avenge his family and cure his sister Nezuko, who has been turned into a demon.', 'TV', 26, 'Finished Airing', '2019-04-06', '2019-09-28', 'Spring 2019', 'Action, Fantasy', 'Historical, Martial Arts', 'Manga', '24 min per ep', 'R', 8.49, 'ufotable');
