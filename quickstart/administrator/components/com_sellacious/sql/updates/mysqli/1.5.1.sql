-- v1.5.1 Changes

-- Sample data error in v1.5.0, but we update only those records which are never modified since update.
UPDATE `#__sellacious_emailtemplates` SET `send_actual_recipient` = 1;
