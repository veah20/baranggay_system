-- Fix Admin Password
-- Run this SQL query in phpMyAdmin if you already imported the database
-- This will reset the admin password to: admin123

USE `birs_db`;

UPDATE `users` 
SET `password` = '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe6nMXyLYLvJxPdJQqZfIXVKGQVXUJqtu' 
WHERE `username` = 'admin';

-- Verify the update
SELECT user_id, username, fullname, role, status FROM users WHERE username = 'admin';
