--
-- Database changes in Sellacious version 1.2.8
--

--
-- Add New Tables
--
CREATE TABLE `#__sellacious_field_tags` (
  `field_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
