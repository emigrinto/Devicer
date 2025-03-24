

CREATE TABLE `Admin` (
    `admin_id` INT AUTO_INCREMENT NOT NULL UNIQUE,
    `admin_username` VARCHAR(50) NOT NULL,
    `admin_password` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    PRIMARY KEY (`admin_id`)
);


CREATE TABLE `Customer` (
    `customer_id` INT AUTO_INCREMENT NOT NULL UNIQUE,
    `customer_username` VARCHAR(50),
    `customer_password` VARCHAR(255),
    `discount` BOOLEAN NOT NULL,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `address` VARCHAR(255) NOT NULL,
    `gender` ENUM('Чоловік', 'Жінка', 'Інше') NOT NULL,
    `birthdate` DATE NOT NULL,
    `phone_number` VARCHAR(13) NOT NULL,
    PRIMARY KEY (`customer_id`)
);


CREATE TABLE `Product` (
    `product_id` INT AUTO_INCREMENT NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `manufacturer` VARCHAR(100) NOT NULL,
    `subcategory` VARCHAR(100),
    `description` TEXT NOT NULL,
    `stock` INT NOT NULL,
    `color` VARCHAR(50),
    `weight` DECIMAL(6,2),
    `warranty` VARCHAR(50),
    PRIMARY KEY (`product_id`)
);

-- Many-to-One (Many Characteristics belong to one Product)
CREATE TABLE `Characteristics` (
    `characteristic_id` INT AUTO_INCREMENT NOT NULL UNIQUE,
    `product_id` INT NOT NULL,
    `characteristic_name` VARCHAR(100) NOT NULL,
    `characteristic_value` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`characteristic_id`),
    FOREIGN KEY (`product_id`) REFERENCES `Product`(`product_id`) ON DELETE CASCADE
);


-- Many-to-One (Many Images can belong to one Product)
CREATE TABLE `Images` (
    `image_id` INT AUTO_INCREMENT NOT NULL UNIQUE,
    `product_id` INT NOT NULL,
    `image_url` VARCHAR(255) NOT NULL,
    `is_primary` BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (`image_id`),
    FOREIGN KEY (`product_id`) REFERENCES `Product`(`product_id`) ON DELETE CASCADE
);


-- Many-to-One (Many Orders can belong to one Customer)
CREATE TABLE `Order` (
    `order_id` INT AUTO_INCREMENT NOT NULL UNIQUE,
    `customer_id` INT,
    `order_date` DATETIME NOT NULL,
    PRIMARY KEY (`order_id`),
    FOREIGN KEY (`customer_id`) REFERENCES `Customer`(`customer_id`)
);

--  One-to-One (Each Invoice is linked to one Order)
CREATE TABLE `Invoice` (
    `invoice_id` INT AUTO_INCREMENT NOT NULL UNIQUE,
    `order_id` INT NOT NULL,
    `total` DECIMAL(10,2) NOT NULL,
    `invoice_date` DATETIME NOT NULL,
    PRIMARY KEY (`invoice_id`),
    FOREIGN KEY (`order_id`) REFERENCES `Order`(`order_id`)
);


-- Many-to-One (Many Wishlist entries can belong to one Customer)
-- Many-to-One (Many Wishlist entries can reference one Product)
CREATE TABLE `Wishlist` (
    `wishlist_id` INT AUTO_INCREMENT NOT NULL UNIQUE,
    `customer_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `added_date` DATETIME NOT NULL,
    PRIMARY KEY (`wishlist_id`),
    FOREIGN KEY (`customer_id`) REFERENCES `Customer`(`customer_id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `Product`(`product_id`) ON DELETE CASCADE
);


-- Many-to-One (Many Order_details entries can belong to one Order)
-- Many-to-One (Many Order_details entries can reference one Product)
CREATE TABLE `Order_details` (
    `order_details_id` INT AUTO_INCREMENT NOT NULL UNIQUE,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    PRIMARY KEY (`order_details_id`),
    FOREIGN KEY (`order_id`) REFERENCES `Order`(`order_id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `Product`(`product_id`) ON DELETE CASCADE
);


-- Many-to-One (Many Reviews can belong to one Product)
-- Many-to-One (Many Reviews can belong to one Customer)
CREATE TABLE `Reviews` (
    `review_id` INT AUTO_INCREMENT NOT NULL UNIQUE,
    `product_id` INT NOT NULL,
    `customer_id` INT NOT NULL,
    `rating` INT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `comment` TEXT,
    `review_date` DATETIME NOT NULL,
    PRIMARY KEY (`review_id`),
    FOREIGN KEY (`product_id`) REFERENCES `Product`(`product_id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `Customer`(`customer_id`) ON DELETE CASCADE
);


-- Many-to-One (Many Cart entries can belong to one Customer)
-- Many-to-One (Many Cart entries can reference one Product)
CREATE TABLE `Cart` (
    `cart_id` INT AUTO_INCREMENT NOT NULL UNIQUE,
    `customer_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    `added_date` DATETIME NOT NULL,
    PRIMARY KEY (`cart_id`),
    FOREIGN KEY (`customer_id`) REFERENCES `Customer`(`customer_id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `Product`(`product_id`) ON DELETE CASCADE
);

