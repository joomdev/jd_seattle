-- Changes in v1.4.3

ALTER TABLE `#__sellacious_cart_info`
  ADD COLUMN   `ship_quote_id` varchar(100) NOT NULL AFTER `shipping`;
