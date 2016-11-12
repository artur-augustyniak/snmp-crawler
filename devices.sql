CREATE TABLE device (
         id INT NOT NULL AUTO_INCREMENT,
         dev_ip VARCHAR(15) NOT NULL,
         hw_parent_port_name VARCHAR(255),
         dev_name TEXT,
         dev_type_hex TEXT,
         dev_type_name TEXT,
         found_by_protocol VARCHAR(5),
         reached_from_dev_id INT  DEFAULT NULL,
         PRIMARY KEY (`id`, `dev_ip`, `hw_parent_port_name`),
         CONSTRAINT `self_ref` FOREIGN KEY (`reached_from_dev_id`) REFERENCES `device` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
)ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;


CREATE TABLE device (
         id INT NOT NULL AUTO_INCREMENT,
         scan_timestamp INT UNSIGNED NOT NULL,
         dev_ip VARCHAR(15) NOT NULL,
         hw_parent_port_name VARCHAR(255),
         dev_name TEXT,
         dev_type_hex TEXT,
         dev_type_name TEXT,
         found_by_protocol VARCHAR(5),
         reached_from_dev_id INT  DEFAULT NULL,
         UNIQUE (id),
         PRIMARY KEY (`scan_timestamp`, `dev_ip`),
         CONSTRAINT `self_ref` FOREIGN KEY (`reached_from_dev_id`) REFERENCES `device` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
)ENGINE=InnoDB  DEFAULT CHARSET=utf8;


INSERT INTO device(scan_timestamp, dev_ip ,hw_parent_port_name, dev_name, dev_type_hex, dev_type_name, found_by_protocol, reached_from_dev_id) VALUES ('1478972807', '192.168.1.1', 'ROOT', 'Zjebany róter', '0x400', 'Two-Port MAC Relay.', 'CDP', NULL);