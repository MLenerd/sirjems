

CREATE TABLE `login_attempts` (
  `ip_address` varchar(45) NOT NULL,
  `attempts` int DEFAULT '0',
  `last_attempt_time` datetime DEFAULT NULL,
  PRIMARY KEY (`ip_address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `rack_allocations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `stock_id` int NOT NULL,
  `rack_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO rack_allocations VALUES("1","1","1","150");
INSERT INTO rack_allocations VALUES("2","2","1","100");
INSERT INTO rack_allocations VALUES("3","5","1","100");
INSERT INTO rack_allocations VALUES("4","6","1","120");
INSERT INTO rack_allocations VALUES("5","3","2","300");
INSERT INTO rack_allocations VALUES("6","4","2","300");
INSERT INTO rack_allocations VALUES("7","16","2","50");
INSERT INTO rack_allocations VALUES("8","17","2","40");
INSERT INTO rack_allocations VALUES("9","18","2","70");
INSERT INTO rack_allocations VALUES("10","19","2","70");
INSERT INTO rack_allocations VALUES("11","20","2","90");
INSERT INTO rack_allocations VALUES("12","13","3","50");
INSERT INTO rack_allocations VALUES("13","14","3","80");
INSERT INTO rack_allocations VALUES("14","15","3","45");
INSERT INTO rack_allocations VALUES("15","7","4","60");
INSERT INTO rack_allocations VALUES("16","8","4","60");
INSERT INTO rack_allocations VALUES("17","9","4","80");
INSERT INTO rack_allocations VALUES("18","10","4","100");
INSERT INTO rack_allocations VALUES("19","11","5","20");
INSERT INTO rack_allocations VALUES("20","12","5","15");


CREATE TABLE `racks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO racks VALUES("1","Aisle 1 - Canned & Condiments");
INSERT INTO racks VALUES("2","Aisle 2 - Snacks & Beverages");
INSERT INTO racks VALUES("3","Aisle 3 - Household & Laundry");
INSERT INTO racks VALUES("4","Aisle 4 - Personal Care");
INSERT INTO racks VALUES("5","Counter Area");


CREATE TABLE `stocks` (
  `item` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `bar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `location` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `price` float(255,2) NOT NULL,
  `id` int NOT NULL AUTO_INCREMENT,
  `stock` int DEFAULT NULL,
  `notified` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO stocks VALUES("Magic Sarap Granules 8g (Pack of 12)","PG-021","Condiments","","55.00","1","200","0");
INSERT INTO stocks VALUES("Knorr Sinigang sa Sampalok Mix 40g","PG-022","Condiments","","25.00","2","150","0");
INSERT INTO stocks VALUES("Kopiko Blanca 3-in-1 Coffee 30g","PG-023","Beverages","","12.00","3","500","0");
INSERT INTO stocks VALUES("Great Taste White Coffee 30g","PG-024","Beverages","","11.00","4","450","0");
INSERT INTO stocks VALUES("San Marino Corned Tuna 180g","PG-025","Canned Goods","","36.00","5","100","0");
INSERT INTO stocks VALUES("Holiday Corned Beef 160g","PG-026","Canned Goods","","32.00","6","120","0");
INSERT INTO stocks VALUES("Head & Shoulders Cool Menthol 170ml","PG-027","Toiletries","","135.00","7","60","0");
INSERT INTO stocks VALUES("Cream Silk Conditioner Standout Straight","PG-028","Toiletries","","110.00","8","60","0");
INSERT INTO stocks VALUES("Close-Up Red Hot Toothpaste 145ml","PG-029","Toiletries","","95.00","9","80","0");
INSERT INTO stocks VALUES("Whisper Cottony Soft Napkin (8s)","PG-030","Toiletries","","45.00","10","100","0");
INSERT INTO stocks VALUES("EQ Dry Diapers Large (30s)","PG-031","Baby Care","","320.00","11","40","0");
INSERT INTO stocks VALUES("Cerelac Wheat & Banana 250g","PG-032","Baby Care","","145.00","12","30","0");
INSERT INTO stocks VALUES("Ariel Detergent Powder 1kg","PG-033","Laundry","","190.00","13","50","0");
INSERT INTO stocks VALUES("Zonrox Bleach Original 1L","PG-034","Laundry","","38.00","14","80","0");
INSERT INTO stocks VALUES("Vernel Fabric Conditioner 1L","PG-035","Laundry","","150.00","15","45","0");
INSERT INTO stocks VALUES("Cobra Energy Drink 240ml","PG-036","Beverages","","18.00","16","100","0");
INSERT INTO stocks VALUES("C2 Green Tea Apple 500ml","PG-037","Beverages","","25.00","17","80","0");
INSERT INTO stocks VALUES("Piattos Cheese 85g","PG-038","Snacks","","32.00","18","70","0");
INSERT INTO stocks VALUES("Nova Country Cheddar 78g","PG-039","Snacks","","32.00","19","70","0");
INSERT INTO stocks VALUES("Ding Dong Mixed Nuts 100g","PG-040","Snacks","","28.00","20","90","0");


CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','staff') DEFAULT 'staff',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `failed_attempts` int DEFAULT '0',
  `last_failed_time` datetime DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO users VALUES("3","Lenerd","Mcgowan","$2y$10$9JcCCukpTJXoOvYQFspicOMhMGasQAUdUM8j8Qz3ywGrd3akgfClW","lenerd@gmail.com","staff","2025-12-22 18:58:42","0","0000-00-00 00:00:00","approved");
INSERT INTO users VALUES("4","Sample","Admin","$2y$10$cFNYl8RQc7ok/97IS8J9QOugzR84ZY/K8AO/CMff7S4PIQ1xjdMZ.","admin@gmail.com","admin","2025-12-22 19:01:59","0","0000-00-00 00:00:00","approved");
INSERT INTO users VALUES("5","Sample","Staff","$2y$10$1gNmOKW8bmXmw/sh4PyNcO0ouK/qI5qObOK936Szy8ElxwPI1gKUq","Staff@gmail.com","staff","2025-12-22 19:07:14","0","0000-00-00 00:00:00","rejected");
