use devicer;

-- full info on each product
SELECT 
    p.product_id,
    p.name AS product_name,
    p.price,
    p.category,
    p.manufacturer,
    p.subcategory,
    p.description,
    p.stock,
    p.color,
    p.weight,
    p.warranty,
    GROUP_CONCAT(CONCAT(c.characteristic_name, ': ', c.characteristic_value) SEPARATOR '; ') AS characteristics
FROM Product p
LEFT JOIN Characteristics c ON p.product_id = c.product_id
GROUP BY p.product_id;

-- show all products from certain category
SELECT 
    product_id,
    name,
    price,
    manufacturer,
    subcategory,
    description,
    stock
FROM Product
WHERE category = 'Кавомашини';


-- top 5 products sold
SELECT 
    p.product_id,
    p.name,
    p.category,
    p.manufacturer,
    SUM(od.quantity) AS total_quantity_sold
FROM Product p
JOIN Order_details od ON p.product_id = od.product_id
GROUP BY p.product_id, p.name, p.category, p.manufacturer
ORDER BY total_quantity_sold DESC
LIMIT 5;


-- all products ever bought by client /w total
SELECT 
    c.customer_id,
    c.first_name,
    c.last_name,
    p.product_id,
    p.name AS product_name,
    od.quantity,
    p.price,
    (od.quantity * p.price) AS total_per_product,
    SUM(od.quantity * p.price) OVER (PARTITION BY c.customer_id) AS total_spent
FROM Customer c
JOIN `Order` o ON c.customer_id = o.customer_id
JOIN Order_details od ON o.order_id = od.order_id
JOIN Product p ON od.product_id = p.product_id
WHERE c.customer_id = 9;


-- top categories
SELECT 
    p.category,
    SUM(od.quantity) AS total_quantity_sold
FROM Product p
JOIN Order_details od ON p.product_id = od.product_id
GROUP BY p.category
ORDER BY total_quantity_sold DESC;

-- list of clients who made more than xyz purchases
SELECT 
    c.customer_id,
    c.first_name,
    c.last_name,
    c.email,
    COUNT(DISTINCT o.order_id) AS order_count
FROM Customer c
JOIN `Order` o ON c.customer_id = o.customer_id
GROUP BY c.customer_id, c.first_name, c.last_name, c.email
HAVING order_count > 1;


-- best selling product from each manufacturer
WITH RankedProducts AS (
    SELECT 
        p.manufacturer,
        p.product_id,
        p.name,
        SUM(od.quantity) AS total_quantity_sold,
        ROW_NUMBER() OVER (PARTITION BY p.manufacturer ORDER BY SUM(od.quantity) DESC) AS rn
    FROM Product p
    JOIN Order_details od ON p.product_id = od.product_id
    GROUP BY p.manufacturer, p.product_id, p.name
)
SELECT 
    manufacturer,
    product_id,
    name,
    total_quantity_sold
FROM RankedProducts
WHERE rn = 1;


-- show clients that spent over 100k
SELECT 
    c.customer_id,
    c.first_name,
    c.last_name,
    c.email,
    SUM(od.quantity * p.price) AS total_spent
FROM Customer c
JOIN `Order` o ON c.customer_id = o.customer_id
JOIN Order_details od ON o.order_id = od.order_id
JOIN Product p ON od.product_id = p.product_id
GROUP BY c.customer_id, c.first_name, c.last_name, c.email
HAVING total_spent > 100000;


-- products that were never bought (yet)
SELECT 
    p.product_id,
    p.name,
    p.category,
    p.manufacturer,
    p.stock
FROM Product p
LEFT JOIN Order_details od ON p.product_id = od.product_id
WHERE od.product_id IS NULL;


