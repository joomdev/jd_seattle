-- Changes in v1.4.5

ALTER TABLE `#__sellacious_cart`
  ADD COLUMN `cart_version` varchar(20) NOT NULL AFTER `params`;

ALTER TABLE `#__sellacious_cart_info`
  ADD COLUMN `cart_id` varchar(20) NOT NULL AFTER `cart_hash`,
  ADD COLUMN `cart_token` varchar(100) NOT NULL AFTER `cart_id`,
  ADD COLUMN `cart_version` varchar(20) NOT NULL AFTER `params`;

ALTER TABLE `#__sellacious_clients`
  ADD COLUMN `credit_limit` double NOT NULL AFTER `org_reg_no`;

ALTER TABLE `#__sellacious_client_authorised`
  ADD COLUMN `credit_limit` double NOT NULL AFTER `user_id`;

ALTER TABLE `#__sellacious_order_items`
  ADD COLUMN `cart_id` varchar(50) NOT NULL AFTER `sub_total`,
  ADD COLUMN `transaction_id` varchar(100) NOT NULL AFTER `cart_id`,
  ADD COLUMN `source_id` varchar(100) NOT NULL AFTER `transaction_id`;

ALTER TABLE `#__sellacious_cart_info`
  DROP `payment_method_id`,
  DROP `payment_params`;

ALTER TABLE `#__sellacious_orders`
  DROP `payment_method_id`,
  DROP `payment_params`;
