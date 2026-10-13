TYPE=VIEW
query=select `u`.`id` AS `id`,`u`.`username` AS `username`,`u`.`email` AS `email`,`u`.`password` AS `password`,`u`.`full_name` AS `full_name`,`u`.`role` AS `role`,`u`.`membership_status` AS `membership_status`,`u`.`membership_expiry` AS `membership_expiry`,`u`.`profile_image` AS `profile_image`,`u`.`created_at` AS `created_at`,`u`.`updated_at` AS `updated_at`,case when `u`.`membership_status` = \'premium\' and `u`.`membership_expiry` >= curdate() then \'active\' else \'expired\' end AS `membership_active_status`,to_days(`u`.`membership_expiry`) - to_days(curdate()) AS `days_until_expiry` from `ebook_library`.`users` `u`
md5=72013639449a8eeae430afe5bf943b34
updatable=1
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001768124027416617
create-version=2
source=SELECT \n    u.*,\n    CASE \n        WHEN u.membership_status = \'premium\' AND u.membership_expiry >= CURDATE() THEN \'active\'\n        ELSE \'expired\'\n    END as membership_active_status,\n    DATEDIFF(u.membership_expiry, CURDATE()) as days_until_expiry\nFROM users u
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=select `u`.`id` AS `id`,`u`.`username` AS `username`,`u`.`email` AS `email`,`u`.`password` AS `password`,`u`.`full_name` AS `full_name`,`u`.`role` AS `role`,`u`.`membership_status` AS `membership_status`,`u`.`membership_expiry` AS `membership_expiry`,`u`.`profile_image` AS `profile_image`,`u`.`created_at` AS `created_at`,`u`.`updated_at` AS `updated_at`,case when `u`.`membership_status` = \'premium\' and `u`.`membership_expiry` >= curdate() then \'active\' else \'expired\' end AS `membership_active_status`,to_days(`u`.`membership_expiry`) - to_days(curdate()) AS `days_until_expiry` from `ebook_library`.`users` `u`
mariadb-version=100432
