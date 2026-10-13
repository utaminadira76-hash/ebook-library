TYPE=VIEW
query=select `u`.`id` AS `user_id`,`u`.`username` AS `username`,`u`.`email` AS `email`,`u`.`membership_status` AS `membership_status`,`dl`.`monthly_limit` AS `monthly_limit`,`dl`.`download_count` AS `current_month_downloads`,`dl`.`monthly_limit` - `dl`.`download_count` AS `remaining_downloads`,`dl`.`current_month` AS `current_month`,`dl`.`last_reset` AS `last_reset`,(select count(0) from `ebook_library`.`download_logs` where `ebook_library`.`download_logs`.`user_id` = `u`.`id`) AS `total_downloads` from (`ebook_library`.`users` `u` left join `ebook_library`.`download_limits` `dl` on(`u`.`id` = `dl`.`user_id`)) where `dl`.`current_month` = date_format(current_timestamp(),\'%Y-%m\')
md5=ffe3ae6de23eb8d29373defb2ddb0b8f
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001768163518662639
create-version=2
source=SELECT \n    u.id as user_id,\n    u.username,\n    u.email,\n    u.membership_status,\n    dl.monthly_limit,\n    dl.download_count as current_month_downloads,\n    dl.monthly_limit - dl.download_count as remaining_downloads,\n    dl.current_month,\n    dl.last_reset,\n    (SELECT COUNT(*) FROM download_logs WHERE user_id = u.id) as total_downloads\nFROM users u\nLEFT JOIN download_limits dl ON u.id = dl.user_id\nWHERE dl.current_month = DATE_FORMAT(NOW(), \'%Y-%m\')
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=select `u`.`id` AS `user_id`,`u`.`username` AS `username`,`u`.`email` AS `email`,`u`.`membership_status` AS `membership_status`,`dl`.`monthly_limit` AS `monthly_limit`,`dl`.`download_count` AS `current_month_downloads`,`dl`.`monthly_limit` - `dl`.`download_count` AS `remaining_downloads`,`dl`.`current_month` AS `current_month`,`dl`.`last_reset` AS `last_reset`,(select count(0) from `ebook_library`.`download_logs` where `ebook_library`.`download_logs`.`user_id` = `u`.`id`) AS `total_downloads` from (`ebook_library`.`users` `u` left join `ebook_library`.`download_limits` `dl` on(`u`.`id` = `dl`.`user_id`)) where `dl`.`current_month` = date_format(current_timestamp(),\'%Y-%m\')
mariadb-version=100432
