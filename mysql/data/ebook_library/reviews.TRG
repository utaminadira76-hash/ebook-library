TYPE=TRIGGERS
triggers='CREATE DEFINER=`root`@`localhost` TRIGGER tr_after_review_update\nAFTER INSERT ON reviews\nFOR EACH ROW\nBEGIN\n    -- Update rating cache di tabel books (jika ada kolom untuk itu)\n    -- Atau bisa di-handle melalui view\n    -- Statement kosong karena hanya placeholder\n    -- Tambahkan logika di sini jika diperlukan nanti\nEND'
sql_modes=1098907648
definers='root@localhost'
client_cs_names='utf8mb4'
connection_cl_names='utf8mb4_unicode_ci'
db_cl_names='utf8mb4_unicode_ci'
created=1768124043804932
