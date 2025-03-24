CREATE TABLE IF NOT EXISTS `Admin` (
	`admin_id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`admin_username` varchar(50) NOT NULL,
	`admin_password` varchar(255) NOT NULL,
	`first_name` varchar(50) NOT NULL,
	`last_name` int NOT NULL,
	`email` varchar(100) NOT NULL UNIQUE,
	PRIMARY KEY (`admin_id`)
);

CREATE TABLE IF NOT EXISTS `Product` (
	`product_id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`name` varchar(100) NOT NULL,
	`price` decimal(10,2) NOT NULL,
	`category` varchar(50) NOT NULL,
	`description` text NOT NULL,
	`stock` int NOT NULL,
	PRIMARY KEY (`product_id`)
);

CREATE TABLE IF NOT EXISTS `Invoice` (
	`invoice_id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`order_id` int NOT NULL,
	`total` decimal(10,2) NOT NULL,
	`invoice_date` datetime NOT NULL,
	PRIMARY KEY (`invoice_id`)
);

CREATE TABLE IF NOT EXISTS `Order` (
	`order_id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`customer_id` int,
	`order_date` datetime NOT NULL,
	PRIMARY KEY (`order_id`)
);

CREATE TABLE IF NOT EXISTS `Wishlist` (
	`wishlist_id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`customer_id` int NOT NULL,
	`product_id` int NOT NULL,
	`added_date` datetime NOT NULL,
	PRIMARY KEY (`wishlist_id`)
);

CREATE TABLE IF NOT EXISTS `Order_details` (
	`order_details_id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`order_id` int NOT NULL,
	`product_id` int NOT NULL,
	`quantity` int NOT NULL,
	PRIMARY KEY (`order_details_id`)
);

CREATE TABLE IF NOT EXISTS `Customer` (
	`customer_id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`customer_username` varchar(50),
	`customer_password` varchar(255),
	`discount` boolean NOT NULL,
	`first_name` varchar(50) NOT NULL,
	`last_name` int NOT NULL,
	`email` varchar(100) NOT NULL UNIQUE,
	`ADD` int NOT NULL,
	`gender` ENUM('Чоловік', 'Жінка', 'Інше') NOT NULL,
	`birthdate` date NOT NULL,
	`phone_number` varchar(13) NOT NULL,
	PRIMARY KEY (`customer_id`)
);


ALTER TABLE `Invoice` ADD CONSTRAINT `Invoice_fk1` FOREIGN KEY (`order_id`) REFERENCES `Order`(`order_id`);
ALTER TABLE `Order` ADD CONSTRAINT `Order_fk1` FOREIGN KEY (`customer_id`) REFERENCES `Customer`(`customer_id`);
ALTER TABLE `Wishlist` ADD CONSTRAINT `Wishlist_fk1` FOREIGN KEY (`customer_id`) REFERENCES `Customer`(`customer_id`);

ALTER TABLE `Wishlist` ADD CONSTRAINT `Wishlist_fk2` FOREIGN KEY (`product_id`) REFERENCES `Product`(`product_id`);
ALTER TABLE `Order_details` ADD CONSTRAINT `Order_details_fk1` FOREIGN KEY (`order_id`) REFERENCES `Order`(`order_id`);

ALTER TABLE `Order_details` ADD CONSTRAINT `Order_details_fk2` FOREIGN KEY (`product_id`) REFERENCES `Product`(`product_id`);
