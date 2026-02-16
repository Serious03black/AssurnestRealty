-- Real-Estate Sales Management System
CREATE DATABASE property_sales_system;
USE property_sales_system;


-- ADMINS 
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_name VARCHAR(100),
    user VARCHAR(50),
    password VARCHAR(255)
);

--  EMPLOYEES 
CREATE TABLE employees (
    emp_id INT AUTO_INCREMENT PRIMARY KEY,
    emp_name VARCHAR(100),
    emp_address TEXT,
    mobile_no VARCHAR(15),
    email VARCHAR(100),
    monthly_rank INT,
    total_properties_sold INT,
    commission DECIMAL(10,2),
    password VARCHAR(255),
    referral_bonus int,
    enrollment_date DATE NOT NULL,
    status ENUM('approved','rejected','pending') DEFAULT 'pending'
);

-- CABDRIVERS 
CREATE TABLE cab_drivers (
    driver_id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    driver_name VARCHAR(100),
    car_no VARCHAR(20),
    address TEXT,
    mobile_no VARCHAR(15),
    email VARCHAR(100),
    monthly_rank INT,
    total_properties_sold INT NOT NULL,
    commission DECIMAL(10,2),
    password VARCHAR(255),
    referral_id INT,
	referral_bonus INT,
    enrollment_date DATE NOT NULL,
    status ENUM('approved','rejected','pending') DEFAULT 'pending'
);


-- PROPERTIES 
CREATE TABLE properties (
    property_id INT AUTO_INCREMENT PRIMARY KEY,
    property_type VARCHAR(50),
    property_name VARCHAR(100),
    admin_id INT,
    description TEXT,
    location_state VARCHAR(50),
    location_city VARCHAR(100),
    location_area VARCHAR(100),
    full_location TEXT,
    price DECIMAL(12,2),
    commission DECIMAL(10,2),
    image1 VARCHAR(255),
    image2 VARCHAR(255),
    image3 VARCHAR(255),
    image4 VARCHAR(255),
    image5 VARCHAR(255),
    status ENUM('available','sold','maintainence') DEFAULT 'available',
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id)
);

-- PROPERTY SALES 
CREATE TABLE property_sales (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    emp_id INT,
    driver_id INT,
    sale_date DATE,
    sale_price DECIMAL(12,2),
    FOREIGN KEY (property_id) REFERENCES properties(property_id),
    FOREIGN KEY (emp_id) REFERENCES employees(emp_id),
    FOREIGN KEY (driver_id) REFERENCES cab_drivers(driver_id)
);

-- MONTHLY SALES 
CREATE TABLE monthly_sales (
    monthly_sale_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_type ENUM('employee','driver'),
    seller_id INT,
    sale_id INT,
    year INT,
    month INT,
    properties_sold INT,
    total_commission DECIMAL(10,2)
);

-- MONTHLY RANKING 
CREATE TABLE monthly_ranking (
    rank_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_type ENUM('employee','driver'),
    seller_id INT,
    monthly_sale_id INT,
    year INT,
    month INT,
    rank_position INT,
    FOREIGN KEY (monthly_sale_id) REFERENCES monthly_sales(monthly_sale_id)
);


-- 1. See commission earned by EMPLOYEES
SELECT 
    emp_id,
    emp_name,
    total_properties_sold,
    commission AS total_commission_earned
FROM employees
ORDER BY commission DESC;

-- 2. See commission earned by CAB DRIVERS (personal sales commission only)
SELECT 
    driver_id,
    driver_name,
    total_properties_sold,
    commission          AS personal_commission_earned,
    referral_bonus       AS extra_referral_bonus,
    commission + referral_bonus AS grand_total
FROM cab_drivers
ORDER BY commission DESC;


-- Top 10 earners (personal + referral)
SELECT driver_id, driver_name, total_properties_sold, commission, referral_bonus,
       commission + referral_bonus AS total_earnings
FROM cab_drivers
ORDER BY total_earnings DESC
LIMIT 10;

-- Referral bonus leaders
SELECT driver_id, driver_name, referral_bonus
FROM cab_drivers
ORDER BY referral_bonus DESC
LIMIT 8;

-- Sold count
SELECT COUNT(*) AS sold_properties FROM properties WHERE status = 'sold';

