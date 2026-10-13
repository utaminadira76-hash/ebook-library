TYPE=VIEW
query=select `b`.`id` AS `id`,`b`.`title` AS `title`,`b`.`author` AS `author`,`b`.`description` AS `description`,`b`.`isbn` AS `isbn`,`b`.`category_id` AS `category_id`,`b`.`cover_image` AS `cover_image`,`b`.`book_file` AS `book_file`,`b`.`preview_file` AS `preview_file`,`b`.`price` AS `price`,`b`.`is_free` AS `is_free`,`b`.`is_featured` AS `is_featured`,`b`.`total_pages` AS `total_pages`,`b`.`publish_date` AS `publish_date`,`b`.`status` AS `status`,`b`.`created_at` AS `created_at`,`b`.`updated_at` AS `updated_at`,`c`.`name` AS `category_name`,`c`.`description` AS `category_description`,(select avg(`ebook_library`.`reviews`.`rating`) from `ebook_library`.`reviews` where `ebook_library`.`reviews`.`book_id` = `b`.`id` and `ebook_library`.`reviews`.`is_approved` = 1) AS `avg_rating`,(select count(0) from `ebook_library`.`reviews` where `ebook_library`.`reviews`.`book_id` = `b`.`id` and `ebook_library`.`reviews`.`is_approved` = 1) AS `review_count` from (`ebook_library`.`books` `b` left join `ebook_library`.`categories` `c` on(`b`.`category_id` = `c`.`id`))
md5=72a1c660a21952b77669b33213c02617
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001768124027383119
create-version=2
source=SELECT \n    b.*,\n    c.name as category_name,\n    c.description as category_description,\n    (SELECT AVG(rating) FROM reviews WHERE book_id = b.id AND is_approved = 1) as avg_rating,\n    (SELECT COUNT(*) FROM reviews WHERE book_id = b.id AND is_approved = 1) as review_count\nFROM books b\nLEFT JOIN categories c ON b.category_id = c.id
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=select `b`.`id` AS `id`,`b`.`title` AS `title`,`b`.`author` AS `author`,`b`.`description` AS `description`,`b`.`isbn` AS `isbn`,`b`.`category_id` AS `category_id`,`b`.`cover_image` AS `cover_image`,`b`.`book_file` AS `book_file`,`b`.`preview_file` AS `preview_file`,`b`.`price` AS `price`,`b`.`is_free` AS `is_free`,`b`.`is_featured` AS `is_featured`,`b`.`total_pages` AS `total_pages`,`b`.`publish_date` AS `publish_date`,`b`.`status` AS `status`,`b`.`created_at` AS `created_at`,`b`.`updated_at` AS `updated_at`,`c`.`name` AS `category_name`,`c`.`description` AS `category_description`,(select avg(`ebook_library`.`reviews`.`rating`) from `ebook_library`.`reviews` where `ebook_library`.`reviews`.`book_id` = `b`.`id` and `ebook_library`.`reviews`.`is_approved` = 1) AS `avg_rating`,(select count(0) from `ebook_library`.`reviews` where `ebook_library`.`reviews`.`book_id` = `b`.`id` and `ebook_library`.`reviews`.`is_approved` = 1) AS `review_count` from (`ebook_library`.`books` `b` left join `ebook_library`.`categories` `c` on(`b`.`category_id` = `c`.`id`))
mariadb-version=100432
