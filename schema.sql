CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    missed_call_number BIGINT,
    mobile_number BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    message_sent ENUM('1', '0') DEFAULT '0'
);

create table coupons(
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon VARCHAR(255),
    type VARCHAR(255),
    user_id VARCHAR(255) DEFAULT NULL,
    is_used ENUM('1','0') DEFAULT '0',
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE gift_card(
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255),
    amount BIGINT,
    Gift_card_number BIGINT,
    State VARCHAR(255),
    validity DATE,
    card_pin BIGINT,
    terms_condition VARCHAR(500),
    currency_code VARCHAR(255),
    user_id INT DEFAULT NULL,
    is_used ENUM('1','0') DEFAULT '0',
    redemeed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE register_user(
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    phone_number BIGINT,
    age INT,
    state VARCHAR(255),
    daily_limit INT DEFAULT NULL,
    campaign_limit INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
);

CREATE TABLE state_detail(
    id INT AUTO_INCREMENT PRIMARY KEY,
    state VARCHAR(255),
    quart INT,
    pint INT,
    nip INT,
    isActive ENUM('0','1') DEFAULT '0'
);

create table batchcode(
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_code VARCHAR(255),
    state VARCHAR(255),
    user_id INT,
    redemmed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active INT
);