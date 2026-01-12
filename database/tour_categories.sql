-- Create tour_categories table
CREATE TABLE IF NOT EXISTS `tour_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `icon` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `color` varchar(20) DEFAULT 'primary',
  `display_order` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default categories
INSERT INTO `tour_categories` (`name`, `slug`, `description`, `icon`, `image`, `color`, `display_order`, `active`) VALUES
('Adventure', 'adventure', 'Trekking, paragliding, and river rafting for thrill-seekers.', 'fa-hiking', 'images/placeholder/adventure-tours.png', 'primary', 1, 1),
('Family', 'family', 'Comfortable itineraries with kid-friendly activities.', 'fa-users', 'images/placeholder/family-tours.png', 'success', 2, 1),
('Honeymoon', 'honeymoon', 'Romantic getaways with luxury stays and special experiences.', 'fa-heart', 'images/placeholder/honeymoon-tours.png', 'danger', 3, 1),
('Spiritual', 'spiritual', 'Discover inner peace at ancient Himalayan monasteries & temples.', 'fa-om', 'images/placeholder/spiritual-tours.png', 'warning', 4, 1),
('Group Tours', 'group', 'Bonfires, camping, and unforgettable memories with friends.', 'fa-users-cog', 'images/placeholder/group-tours.png', 'info', 5, 1),
('Nature', 'nature', 'Immerse yourself in lush valleys, forests, and untouched wilderness.', 'fa-leaf', 'images/placeholder/nature-tours.png', 'success', 6, 1);
