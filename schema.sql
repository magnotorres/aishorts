-- Video Content Automation DB Schema

-- Main categories for content generation.
-- The AI will use `priority_score` to select the next topic.
CREATE TABLE `categories` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `priority_score` FLOAT NOT NULL DEFAULT 1.0,
  `last_used_at` TIMESTAMP NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Social media accounts linked to a specific subject/personality.
-- Credentials should be encrypted in a production environment.
CREATE TABLE `social_accounts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `subject` VARCHAR(255) NOT NULL,
  `platform` ENUM('youtube', 'facebook', 'instagram', 'tiktok', 'kwai') NOT NULL,
  `account_name` VARCHAR(255) NOT NULL,
  `credentials` TEXT NOT NULL, -- IMPORTANT: Encrypt this data before inserting.
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tracks every video that has been generated and published.
CREATE TABLE `published_videos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `category_id` INT NOT NULL,
  `social_account_id` INT NOT NULL,
  `prompt_used` TEXT NOT NULL,
  `video_path` VARCHAR(255) NOT NULL,
  `post_url` VARCHAR(255) DEFAULT NULL,
  `published_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id_idx` (`category_id`),
  KEY `social_account_id_idx` (`social_account_id`),
  CONSTRAINT `fk_pv_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pv_social_account` FOREIGN KEY (`social_account_id`) REFERENCES `social_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Stores engagement metrics for each published video.
-- This data is used to calculate the `priority_score` for categories.
CREATE TABLE `video_metrics` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `video_id` INT NOT NULL,
  `fetched_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `views` INT NOT NULL DEFAULT 0,
  `likes` INT NOT NULL DEFAULT 0,
  `comments` INT NOT NULL DEFAULT 0,
  `shares` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `video_id_idx` (`video_id`),
  CONSTRAINT `fk_vm_video` FOREIGN KEY (`video_id`) REFERENCES `published_videos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
