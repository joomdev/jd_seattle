-- Changes in v1.4.4

ALTER TABLE `#__sellacious_addresses`
  ADD COLUMN `company` varchar(200) NOT NULL AFTER `state`,
  ADD COLUMN `po_box` varchar(50) NOT NULL AFTER `company`,
  ADD COLUMN `residential` int(11) NOT NULL AFTER `po_box`;

ALTER TABLE `#__sellacious_cart`
  DROP COLUMN `shipping_charge`;

ALTER TABLE `#__sellacious_cart_info`
  ADD COLUMN `ship_quotes` text NOT NULL AFTER `shipping`,
  ADD COLUMN `shipment_params` text NOT NULL AFTER `payment_params`;

ALTER TABLE `#__sellacious_orders`
  ADD COLUMN `bt_company` varchar(200) NOT NULL AFTER `bt_mobile`,
  ADD COLUMN `bt_po_box` varchar(50) NOT NULL AFTER `bt_company`,
  ADD COLUMN `bt_residential` int(11) NOT NULL AFTER `bt_po_box`,
  ADD COLUMN `st_company` varchar(200) NOT NULL AFTER `st_mobile`,
  ADD COLUMN `st_po_box` varchar(50) NOT NULL AFTER `st_company`,
  ADD COLUMN `st_residential` int(11) NOT NULL AFTER `st_po_box`,
  ADD COLUMN `shipping_rule` varchar(100) NOT NULL AFTER `product_ship_tbd`,
  ADD COLUMN `shipping_service` varchar(100) NOT NULL AFTER `shipping_rule`,
  ADD COLUMN `shipping_params` text NOT NULL AFTER `shipping_service`,
  ADD COLUMN `checkout_forms` text NOT NULL AFTER `shipping_params`,
  ADD COLUMN `params` text NOT NULL AFTER `modified_by`;

ALTER TABLE `#__sellacious_order_items`
  ADD COLUMN `shipping_rule` varchar(100) NOT NULL AFTER `tax_amount`,
  ADD COLUMN `shipping_service` varchar(100) NOT NULL AFTER  `shipping_rule`;

ALTER TABLE `#__sellacious_payments`
  CHANGE COLUMN `data` `data` text NOT NULL COMMENT 'The form data is optional here. Plugins must use from session only',
  CHANGE COLUMN `order_amount` `order_amount` double NOT NULL AFTER `percent_fee`,
  ADD COLUMN `method_name` varchar(150) NOT NULL AFTER `handler`,
  ADD COLUMN `handler_name` varchar(150) NOT NULL COMMENT '@unused,future use' AFTER `method_name`,
  ADD COLUMN `fee_amount` double NOT NULL AFTER `order_amount`;
