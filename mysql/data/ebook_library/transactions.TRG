TYPE=TRIGGERS
triggers='CREATE DEFINER=`root`@`localhost` TRIGGER tr_after_transaction_success\nAFTER UPDATE ON transactions\nFOR EACH ROW\nBEGIN\n    IF NEW.status = \'success\' AND OLD.status != \'success\' THEN\n        UPDATE users \n        SET \n            membership_status = \'premium\',\n            membership_expiry = NEW.expiry_date\n        WHERE id = NEW.user_id;\n        \n        -- Log aktivitas\n        INSERT INTO activity_logs (user_id, action, details)\n        VALUES (NEW.user_id, \'membership_upgrade\', \n                CONCAT(\'Upgrade to premium via transaction \', NEW.transaction_code));\n    END IF;\nEND'
sql_modes=1098907648
definers='root@localhost'
client_cs_names='utf8mb4'
connection_cl_names='utf8mb4_unicode_ci'
db_cl_names='utf8mb4_unicode_ci'
created=1768124043761301
