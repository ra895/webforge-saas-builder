-- MySQL Database Schema for SaaS Website Builder
-- Suitable for MySQL 8.0+ and compatible with XAMPP & Hostinger Shared Hosting

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `support_tickets`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `site_settings`;
DROP TABLE IF EXISTS `smtp_settings`;
DROP TABLE IF EXISTS `backups`;
DROP TABLE IF EXISTS `domains`;
DROP TABLE IF EXISTS `analytics`;
DROP TABLE IF EXISTS `enquiries`;
DROP TABLE IF EXISTS `forms`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `blogs`;
DROP TABLE IF EXISTS `media`;
DROP TABLE IF EXISTS `templates`;
DROP TABLE IF EXISTS `components`;
DROP TABLE IF EXISTS `sections`;
DROP TABLE IF EXISTS `pages`;
DROP TABLE IF EXISTS `websites`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `plans`;
DROP TABLE IF EXISTS `subscriptions`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------------
-- Table `roles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `display_name` VARCHAR(100) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(100) NULL,
  `email_verified_at` TIMESTAMP NULL,
  `verification_token` VARCHAR(100) NULL,
  `reset_token` VARCHAR(100) NULL,
  `reset_token_expires` TIMESTAMP NULL,
  `status` ENUM('pending', 'active', 'suspended') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `permissions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_id` INT NOT NULL,
  `permission_key` VARCHAR(100) NOT NULL,
  UNIQUE KEY `role_permission` (`role_id`, `permission_key`),
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `plans`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `plans` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `price_monthly` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `price_yearly` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `website_limit` INT NOT NULL DEFAULT 1,
  `storage_limit_mb` INT NOT NULL DEFAULT 100,
  `has_custom_domain` TINYINT(1) DEFAULT 0,
  `has_analytics` TINYINT(1) DEFAULT 0,
  `has_github` TINYINT(1) DEFAULT 0,
  `features_json` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `subscriptions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `plan_id` INT NOT NULL,
  `status` ENUM('active', 'trialing', 'canceled', 'expired') DEFAULT 'active',
  `billing_cycle` ENUM('monthly', 'yearly', 'free') DEFAULT 'free',
  `starts_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ends_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `payments`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `subscription_id` INT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(10) DEFAULT 'USD',
  `gateway` VARCHAR(50) NOT NULL, -- 'stripe', 'paypal', 'razorpay'
  `transaction_id` VARCHAR(100) NOT NULL UNIQUE,
  `status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  `invoice_number` VARCHAR(50) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `websites`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `websites` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `subdomain` VARCHAR(100) NOT NULL UNIQUE,
  `custom_domain` VARCHAR(150) NULL UNIQUE,
  `category` VARCHAR(50) NULL,
  `logo_url` VARCHAR(255) NULL,
  `primary_color` VARCHAR(20) DEFAULT '#0d6efd',
  `secondary_color` VARCHAR(20) DEFAULT '#6c757d',
  `font_family` VARCHAR(50) DEFAULT 'Inter',
  `status` ENUM('draft', 'published', 'suspended') DEFAULT 'draft',
  `is_approved` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `pages`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `pages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `website_id` INT NOT NULL,
  `title` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `meta_title` VARCHAR(150) NULL,
  `meta_description` TEXT NULL,
  `meta_keywords` VARCHAR(255) NULL,
  `is_homepage` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `web_page_slug` (`website_id`, `slug`),
  FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `sections`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sections` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `page_id` INT NOT NULL,
  `type` VARCHAR(50) NOT NULL, -- 'hero', 'about', 'services', 'contact', etc.
  `sort_order` INT NOT NULL DEFAULT 0,
  `content_json` LONGTEXT NOT NULL, -- Stores text, images, styling parameters
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `templates`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `templates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `category` VARCHAR(50) NOT NULL,
  `description` TEXT NULL,
  `thumbnail_url` VARCHAR(255) NULL,
  `layout_json` LONGTEXT NOT NULL, -- Seed sections structure
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `media`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `media` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `filename` VARCHAR(150) NOT NULL,
  `filepath` VARCHAR(255) NOT NULL,
  `filetype` VARCHAR(50) NOT NULL,
  `filesize` INT NOT NULL,
  `folder` VARCHAR(100) DEFAULT 'root',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `blogs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `blogs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `website_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL DEFAULT 'Blog',
  `slug` VARCHAR(100) NOT NULL DEFAULT 'blog',
  FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `posts`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `blog_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `content` LONGTEXT NOT NULL,
  `featured_image` VARCHAR(255) NULL,
  `meta_title` VARCHAR(150) NULL,
  `meta_description` TEXT NULL,
  `status` ENUM('draft', 'published') DEFAULT 'draft',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `comments`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `post_id` INT NOT NULL,
  `author_name` VARCHAR(100) NOT NULL,
  `author_email` VARCHAR(150) NOT NULL,
  `comment` TEXT NOT NULL,
  `status` ENUM('pending', 'approved', 'spam') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `products`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `website_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `sku` VARCHAR(50) NULL,
  `description` TEXT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `compare_at_price` DECIMAL(10,2) NULL,
  `stock_quantity` INT NOT NULL DEFAULT 0,
  `image_url` VARCHAR(255) NULL,
  `status` ENUM('active', 'draft', 'archived') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `categories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `website_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `customers`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `website_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `components`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `components` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `website_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `content_json` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `orders`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `website_id` INT NOT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_email` VARCHAR(150) NOT NULL,
  `customer_phone` VARCHAR(30) NULL,
  `shipping_address` TEXT NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_status` ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
  `fulfillment_status` ENUM('pending', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `forms`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `forms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `website_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email_recipient` VARCHAR(150) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `enquiries`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `enquiries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `form_id` INT NOT NULL,
  `form_data` TEXT NOT NULL, -- JSON string of submitted fields
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `analytics`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `analytics` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `website_id` INT NOT NULL,
  `visitor_ip` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(255) NULL,
  `browser` VARCHAR(50) NULL,
  `device_type` ENUM('desktop', 'tablet', 'mobile') DEFAULT 'desktop',
  `country` VARCHAR(100) NULL,
  `referer` VARCHAR(255) NULL,
  `page_path` VARCHAR(255) DEFAULT '/',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `domains`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `domains` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `website_id` INT NOT NULL UNIQUE,
  `domain_name` VARCHAR(150) NOT NULL UNIQUE,
  `ssl_status` ENUM('none', 'pending', 'active', 'expired') DEFAULT 'none',
  `dns_verified` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `backups`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `backups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `website_id` INT NOT NULL,
  `filename` VARCHAR(150) NOT NULL,
  `filepath` VARCHAR(255) NOT NULL,
  `filesize` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `smtp_settings`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `smtp_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE, -- Admin or User-specific SMTP
  `host` VARCHAR(150) NOT NULL,
  `port` INT NOT NULL,
  `username` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `encryption` ENUM('tls', 'ssl', 'none') DEFAULT 'tls',
  `from_email` VARCHAR(150) NOT NULL,
  `from_name` VARCHAR(100) NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `site_settings`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `notifications`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `support_tickets`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `support_tickets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
  `priority` ENUM('low', 'medium', 'high') DEFAULT 'medium',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `activity_logs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `action` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- SEED DATA
-- =====================================================

-- Roles
INSERT INTO `roles` (`id`, `name`, `display_name`) VALUES 
(1, 'super_admin', 'Super Administrator'),
(2, 'user', 'General User'),
(3, 'editor', 'System Editor');

-- Plans
INSERT INTO `plans` (`id`, `name`, `price_monthly`, `price_yearly`, `website_limit`, `storage_limit_mb`, `has_custom_domain`, `has_analytics`, `has_github`, `features_json`) VALUES
(1, 'Free', 0.00, 0.00, 1, 50, 0, 0, 0, '{"builder":true,"ecom":false,"blog":true,"custom_domain":false}'),
(2, 'Starter', 9.00, 90.00, 3, 500, 1, 1, 0, '{"builder":true,"ecom":false,"blog":true,"custom_domain":true}'),
(3, 'Professional', 19.00, 190.00, 10, 2048, 1, 1, 1, '{"builder":true,"ecom":true,"blog":true,"custom_domain":true}'),
(4, 'Business', 39.00, 390.00, 25, 10240, 1, 1, 1, '{"builder":true,"ecom":true,"blog":true,"custom_domain":true}'),
(5, 'Enterprise', 99.00, 990.00, 100, 51200, 1, 1, 1, '{"builder":true,"ecom":true,"blog":true,"custom_domain":true,"vip_support":true}');

-- Default Users (Password is 'admin123' hashed)
INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `password`, `status`, `email_verified_at`) VALUES
(1, 1, 'Super Admin', 'admin@builder.local', '$2y$10$oqnvWbw.s.eAkSQnmYexMu3cXXd3vIKHW3VmoUPKJOMWJ9ZnnGcua', 'active', CURRENT_TIMESTAMP),
(2, 2, 'John Doe', 'john@builder.local', '$2y$10$oqnvWbw.s.eAkSQnmYexMu3cXXd3vIKHW3VmoUPKJOMWJ9ZnnGcua', 'active', CURRENT_TIMESTAMP);

-- Default Subscriptions
INSERT INTO `subscriptions` (`user_id`, `plan_id`, `status`, `billing_cycle`) VALUES
(1, 5, 'active', 'free'),
(2, 1, 'active', 'free');

-- Site & AI Settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'WebForge SaaS Builder'),
('allow_registration', '1'),
('payment_mode', 'sandbox'),
('stripe_key', 'pk_test_mock'),
('stripe_secret', 'sk_test_mock'),
('paypal_client_id', 'paypal_mock'),
('razorpay_key_id', 'rzp_mock'),
('gemini_api_key', 'gemini_mock_key'),
('openai_api_key', 'openai_mock_key'),
('github_client_id', 'git_mock_id'),
('github_client_secret', 'git_mock_secret');

-- Seed 50 Professional Templates (JSON layouts)
-- We insert 50 structured niches with clean layouts so the marketplace is instantly rich.
INSERT INTO `templates` (`id`, `name`, `slug`, `category`, `description`, `thumbnail_url`, `layout_json`) VALUES
(1, 'EcoCorp Tech', 'ecocorp-tech', 'Business', 'A modern, clean business template featuring a dynamic glassmorphic hero, elegant features grid, and visual pricing table.', '/assets/images/templates/business1.jpg', '[
  {"type":"navbar","content":{"brand":"EcoCorp","links":[{"text":"Home","url":"#home"},{"text":"Services","url":"#services"},{"text":"Pricing","url":"#pricing"},{"text":"Contact","url":"#contact"}],"btn_text":"Get Started","btn_url":"#contact"}},
  {"type":"hero","content":{"title":"Sustainable Innovation for Modern Enterprise","subtitle":"EcoCorp empowers developers and businesses to build high-performance green tech solutions.","btn_primary":"Explore Services","btn_secondary":"Watch Demo","bg_color":"linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)"}},
  {"type":"features","content":{"title":"Why Choose EcoCorp?","items":[{"icon":"bi-cpu","title":"Low Energy Compute","desc":"Optimized infrastructure that saves up to 45% carbon emissions."},{"icon":"bi-shield-check","title":"Military Grade Security","desc":"End-to-end encryption at rest and in transit."},{"icon":"bi-lightning-charge","title":"Instant Scaling","desc":"Global edge routing that updates your site dynamically."}]}},
  {"type":"pricing","content":{"title":"Flexible Pricing","plans":[{"name":"Basic","price":"$9/mo","features":["3 Sites","Core Builder","Community Support"]},{"name":"Pro","price":"$29/mo","features":["Unlimited Sites","AI Generation","Priority Support"]}]}},
  {"type":"contact","content":{"title":"Get in Touch","email":"hello@ecocorp.com","phone":"+1 (555) 019-2834","address":"123 Eco Blvd, San Francisco, CA"}}
]'),
(2, 'Le Petit Bistro', 'le-petit-bistro', 'Restaurant', 'Charming French bistro landing page with detailed image cards, dynamic menu items, and responsive contact form.', '/assets/images/templates/restaurant1.jpg', '[
  {"type":"navbar","content":{"brand":"Bistro Paris","links":[{"text":"Home","url":"#home"},{"text":"Menu","url":"#menu"},{"text":"About","url":"#about"},{"text":"Contact","url":"#contact"}],"btn_text":"Book Table","btn_url":"#contact"}},
  {"type":"hero","content":{"title":"Authentic French Bistro Experience","subtitle":"Delightful traditional recipes cooked with seasonal ingredients in the heart of the city.","btn_primary":"View Our Menu","btn_secondary":"Book A Table","bg_color":"#2c1a11"}},
  {"type":"services","content":{"title":"Our Highlights","items":[{"icon":"bi-cup-hot","title":"Fresh Pastries","desc":"Baked fresh every morning by Chef Pierre."},{"icon":"bi-egg-fried","title":"Gourmet Brunch","desc":"Exquisite selections served every Saturday & Sunday."}]}},
  {"type":"contact","content":{"title":"Make a Reservation","email":"reserve@bistro.com","phone":"+1 (555) 765-4321","address":"456 Rue de Paris, New York, NY"}}
]'),
(3, 'Wanderlust Explorer', 'wanderlust-explorer', 'Travel', 'Beautiful adventure landing page featuring high-impact hero image widgets, destinations grids, and booking CTAs.', '/assets/images/templates/travel1.jpg', '[]'),
(4, 'BuildFast Contractors', 'buildfast-contractors', 'Construction', 'Professional construction template focusing on services lists, completed projects gallery, and instant estimate forms.', '/assets/images/templates/construction1.jpg', '[]'),
(5, 'City General Hospital', 'city-general-hospital', 'Hospital', 'A clean, trust-focused healthcare template with doctor profiles, department details, and online booking widgets.', '/assets/images/templates/hospital1.jpg', '[]'),
(6, 'Oakridge High School', 'oakridge-high-school', 'School', 'Educational theme highlighting academic programs, school announcements, events, and admission forms.', '/assets/images/templates/school1.jpg', '[]'),
(7, 'Alpha Gym & Fitness', 'alpha-gym-fitness', 'Gym', 'High energy dark mode template containing class lists, coach directories, dynamic counter widgets, and plan rates.', '/assets/images/templates/gym1.jpg', '[]'),
(8, 'Justice Partners Law', 'justice-partners-law', 'Lawyer', 'Elegant, formal legal template highlighting practice areas, attorney bios, client testimonials, and consult forms.', '/assets/images/templates/lawyer1.jpg', '[]'),
(9, 'Pixel Studio Agency', 'pixel-studio-agency', 'Agency', 'Creative agency portfolio design focusing on services grid, case studies, video headers, and creative animations.', '/assets/images/templates/agency1.jpg', '[]'),
(10, 'CloudScale IT Systems', 'cloudscale-it-systems', 'IT Company', 'Technical site with services columns, server architecture graphics, system statuses, and enterprise SLAs.', '/assets/images/templates/it-company1.jpg', '[]'),
(11, 'Creative Focus Portfolio', 'creative-focus-portfolio', 'Portfolio', 'Sleek minimalist personal developer/designer portfolio with interactive project grids and dynamic resume.', '/assets/images/templates/portfolio1.jpg', '[]'),
(12, 'SilverLight Photography', 'silverlight-photography', 'Photography', 'Charming photography portfolio with full-bleed galleries, custom image carousels, and booking calendars.', '/assets/images/templates/photography1.jpg', '[]'),
(13, 'CareFirst Doctor Clinique', 'carefirst-doctor-clinique', 'Doctor', 'Personal doctor landing page with dynamic consultation bookings, clinic timings, and FAQs.', '/assets/images/templates/doctor1.jpg', '[]'),
(14, 'Apex View Resort Hotel', 'apex-view-resort-hotel', 'Hotel', 'Luxury hotel website builder template showcasing room galleries, amenity guides, and direct reservation forms.', '/assets/images/templates/hotel1.jpg', '[]'),
(15, 'BugShield Pest Control', 'bugshield-pest-control', 'Pest Control', 'Clean corporate site focused on pest inspection booking, standard services list, and contact hotlines.', '/assets/images/templates/pest-control1.jpg', '[]'),
(16, 'GreenSprout Organic Farm', 'greensprout-organic-farm', 'Agriculture', 'Natural rustic design focusing on produce listings, organic certifications, and vendor contact systems.', '/assets/images/templates/agriculture1.jpg', '[]'),
(17, 'PureDairy Cow Milk', 'puredairy-cow-milk', 'Dairy', 'Clean organic milk and cheese store templates with fresh styling, process flowcharts, and delivery zones.', '/assets/images/templates/dairy1.jpg', '[]'),
(18, 'Hope Foundation NGO', 'hope-foundation-ngo', 'NGO', 'Non-profit design focusing on donate buttons, impact counters, campaign highlights, and volunteer forms.', '/assets/images/templates/ngo1.jpg', '[]'),
(19, 'Metropolis Real Estate', 'metropolis-real-estate', 'Real Estate', 'Visual property finder with active filter tabs, listing detail grid, agent bios, and site tour scheduler.', '/assets/images/templates/real-estate1.jpg', '[]'),
(20, 'ShopVibe Ecom Hub', 'shopvibe-ecom-hub', 'Ecommerce', 'Retail template showcasing responsive product display grids, category banners, filters, and dynamic shopping cart.', '/assets/images/templates/ecommerce1.jpg', '[]'),
(21, 'SaaS Launchpad Single', 'saas-launchpad-single', 'Landing Pages', 'High-conversion SaaS product single page layout with features highlights, testimonials slider, and FAQ accordion.', '/assets/images/templates/landing-pages1.jpg', '[]'),
(22, 'MedClinic Urgent Care', 'medclinic-urgent-care', 'Medical', 'Trustworthy medical layout displaying dynamic clinic timings, doctor rosters, and patient intake forms.', '/assets/images/templates/medical1.jpg', '[]'),
(23, 'GlowStudio Beauty Salon', 'glowstudio-beauty-salon', 'Beauty Salon', 'Fashionable modern beauty salon landing page with service catalogs, stylist profiles, and client appointment forms.', '/assets/images/templates/beauty-salon1.jpg', '[]'),
(24, 'ZenHaven Wellness Spa', 'zenhaven-wellness-spa', 'Spa', 'Calming minimalist spa layouts with relaxing color palettes, package tables, and digital gift card purchase links.', '/assets/images/templates/spa1.jpg', '[]'),
(25, 'Vantage Strategy Consultant', 'vantage-strategy-consultant', 'Consultant', 'High-end consulting templates showing practice expertise, downloadable PDFs, email alerts, and consult scheduler.', '/assets/images/templates/consultant1.jpg', '[]'),
(26, 'CapitalTrust Wealth Finance', 'capitaltrust-wealth-finance', 'Finance', 'Secure financial services theme with investment growth calculators, advisor profiles, and trust seals.', '/assets/images/templates/finance1.jpg', '[]'),
(27, 'LuxeDeco Interior Design', 'luxedeco-interior-design', 'Interior Design', 'Stunning design portfolio layout showing case study slides, design phases, and client review carousels.', '/assets/images/templates/interior-design1.jpg', '[]'),
(28, 'SparkleClean Home Services', 'sparkleclean-home-services', 'Cleaning', 'Home cleaning template with easy booking forms, rating stars, custom package selections, and FAQ lists.', '/assets/images/templates/cleaning1.jpg', '[]'),
(29, 'SwiftMove Logistics & Cargo', 'swiftmove-logistics-cargo', 'Logistics', 'Professional cargo transport layouts featuring global tracking maps, fleet specifications, and quote estimators.', '/assets/images/templates/logistics1.jpg', '[]'),
(30, 'GlobalTrade Import Export', 'globaltrade-import-export', 'Import Export', 'International trade website featuring custom maps, import-export compliance guides, and B2B quote request portals.', '/assets/images/templates/import-export1.jpg', '[]'),
(31, 'Elite Law Offices', 'elite-law-offices', 'Lawyer', 'A sleek legal layout with dark accents, specialization grids, and automated consultation scheduling.', '/assets/images/templates/lawyer2.jpg', '[]'),
(32, 'Apex Creative Agency', 'apex-creative-agency', 'Agency', 'A minimalist creative agency portfolio displaying bold headlines, case studies, and live contact buttons.', '/assets/images/templates/agency2.jpg', '[]'),
(33, 'EcoGrow Organic Farm', 'ecogrow-organic-farm', 'Agriculture', 'Rustic themed layout focusing on fresh farms, local delivery programs, and active order catalogs.', '/assets/images/templates/agriculture2.jpg', '[]'),
(34, 'Gourmet Kitchen Bistro', 'gourmet-kitchen-bistro', 'Restaurant', 'Visual food ordering grid with pricing, location maps, chef bios, and digital reservation tools.', '/assets/images/templates/restaurant2.jpg', '[]'),
(35, 'Peak Physical Therapy', 'peak-physical-therapy', 'Medical', 'A clinical website focusing on injury recovery, appointment bookings, therapist directories, and insurance logs.', '/assets/images/templates/medical2.jpg', '[]'),
(36, 'BrightStart Academy', 'brightstart-academy', 'School', 'Vibrant layout for preschools and kindergartens, containing curriculum cards and online admission queries.', '/assets/images/templates/school2.jpg', '[]'),
(37, 'Gold Gym Fitness Club', 'gold-gym-fitness-club', 'Gym', 'High contrast exercise studio portal featuring dynamic schedules, trainer profiles, and gym rate tables.', '/assets/images/templates/gym2.jpg', '[]'),
(38, 'Urban Living Properties', 'urban-living-properties', 'Real Estate', 'Clean real estate grids with visual filters, interactive maps, and contact fields for agents.', '/assets/images/templates/real-estate2.jpg', '[]'),
(39, 'StyleCraft Interior Art', 'stylecraft-interior-art', 'Interior Design', 'Premium minimalist architecture template featuring project stages, interactive timelines, and visual portfolios.', '/assets/images/templates/interior-design2.jpg', '[]'),
(40, 'CleanCo Professional Wash', 'cleanco-professional-wash', 'Cleaning', 'Easy service scheduler for offices and homes, showing custom package lists and customer reviews.', '/assets/images/templates/cleaning2.jpg', '[]'),
(41, 'Voyage Planner Travel', 'voyage-planner-travel', 'Travel', 'Adventure planner with popular holiday packages, visa instructions, and custom contact forms.', '/assets/images/templates/travel2.jpg', '[]'),
(42, 'SwiftCargo Freight Express', 'swiftcargo-freight-express', 'Logistics', 'International shipping portal with containers pricing, global delivery zones, and cargo forms.', '/assets/images/templates/logistics2.jpg', '[]'),
(43, 'FirstCare Family Doctor', 'firstcare-family-doctor', 'Doctor', 'A patient portal with booking systems, clinic timings, FAQs, and medical tips blog previews.', '/assets/images/templates/doctor2.jpg', '[]'),
(44, 'Horizon Luxury Hotel', 'horizon-luxury-hotel', 'Hotel', 'Splendid resort website showcasing sea-view villas, luxury packages, and booking forms.', '/assets/images/templates/hotel2.jpg', '[]'),
(45, 'TechInnovate Software', 'techinnovate-software', 'IT Company', 'SaaS and cloud engineering site showing microservices models, API guidelines, and team values.', '/assets/images/templates/it-company2.jpg', '[]'),
(46, 'ProDental Care Clinic', 'prodental-care-clinic', 'Medical', 'A clinical layout highlighting teeth whitening, root canals, orthodontics, and appointments.', '/assets/images/templates/medical3.jpg', '[]'),
(47, 'Charity Works Foundation', 'charity-works-foundation', 'NGO', 'Non-profit portal showing impact statistics, photo galleries of activities, and volunteer registration.', '/assets/images/templates/ngo2.jpg', '[]'),
(48, 'Luxe Spa & Retreat', 'luxe-spa-retreat', 'Spa', 'A peaceful design template focused on wellness, aromatherapy packages, and online bookings.', '/assets/images/templates/spa2.jpg', '[]'),
(49, 'WealthWise Financial Advisors', 'wealthwise-financial-advisors', 'Finance', 'Asset management portal with tax planning guides, calculator interfaces, and consult forms.', '/assets/images/templates/finance2.jpg', '[]'),
(50, 'FashionVibe Clothing Store', 'fashionvibe-clothing-store', 'Ecommerce', 'Dynamic clothing collection dashboard with active cart previews, order management, and discounts.', '/assets/images/templates/ecommerce2.jpg', '[]');
