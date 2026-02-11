-- Create reviews table
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `rating` int(1) NOT NULL DEFAULT 5,
  `review_text` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert some dummy reviews for initial display
INSERT INTO `reviews` (`name`, `location`, `rating`, `review_text`, `image_path`, `status`, `is_featured`) VALUES
('Priya Sharma', 'Delhi', 5, 'Our trip to Shimla was unforgettable. The tour was well-organized and the guide was very knowledgeable. Highly recommend!', 'https://randomuser.me/api/portraits/women/45.jpg', 'approved', 1),
('Rahul Verma', 'Mumbai', 5, 'The Spiti Valley trek was a life-changing experience. The guides were excellent and the arrangements were perfect. Will definitely book with Travel In Peace again!', 'https://randomuser.me/api/portraits/men/32.jpg', 'approved', 1),
('Anita Gupta', 'Bangalore', 4, 'We booked the Innova Crysta for our family trip to Manali. The vehicle was in excellent condition and the driver was very professional and friendly.', 'https://randomuser.me/api/portraits/women/68.jpg', 'approved', 1);
