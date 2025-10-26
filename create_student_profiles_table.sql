-- Run this in your MySQL (phpMyAdmin) to create the student_profiles table

CREATE TABLE IF NOT EXISTS `student_profiles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `degree` varchar(255) DEFAULT NULL,
  `advisor` varchar(255) DEFAULT NULL,
  `expected_graduation` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_idx` (`userId`),
  KEY `user_fk` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optionally add foreign key if `users` exists and uses `usersId` as PK:
-- ALTER TABLE `student_profiles` ADD CONSTRAINT `fk_student_profiles_user` FOREIGN KEY (`userId`) REFERENCES `users`(`usersId`) ON DELETE CASCADE ON UPDATE CASCADE;