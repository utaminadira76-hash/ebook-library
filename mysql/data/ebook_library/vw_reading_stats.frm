TYPE=VIEW
query=select `urh`.`user_id` AS `user_id`,`urh`.`book_id` AS `book_id`,`b`.`title` AS `title`,`b`.`author` AS `author`,`urh`.`last_page` AS `last_page`,`urh`.`progress_percentage` AS `progress_percentage`,`urh`.`last_read_at` AS `last_read_at`,to_days(current_timestamp()) - to_days(`urh`.`last_read_at`) AS `days_since_last_read` from (`ebook_library`.`user_reading_history` `urh` join `ebook_library`.`books` `b` on(`urh`.`book_id` = `b`.`id`)) where `urh`.`progress_percentage` < 100
md5=99c8848d290e865210e4759caee7e84b
updatable=1
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001768163518685350
create-version=2
source=SELECT \n    urh.user_id,\n    urh.book_id,\n    b.title,\n    b.author,\n    urh.last_page,\n    urh.progress_percentage,\n    urh.last_read_at,\n    DATEDIFF(NOW(), urh.last_read_at) as days_since_last_read\nFROM user_reading_history urh\nJOIN books b ON urh.book_id = b.id\nWHERE urh.progress_percentage < 100
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=select `urh`.`user_id` AS `user_id`,`urh`.`book_id` AS `book_id`,`b`.`title` AS `title`,`b`.`author` AS `author`,`urh`.`last_page` AS `last_page`,`urh`.`progress_percentage` AS `progress_percentage`,`urh`.`last_read_at` AS `last_read_at`,to_days(current_timestamp()) - to_days(`urh`.`last_read_at`) AS `days_since_last_read` from (`ebook_library`.`user_reading_history` `urh` join `ebook_library`.`books` `b` on(`urh`.`book_id` = `b`.`id`)) where `urh`.`progress_percentage` < 100
mariadb-version=100432
