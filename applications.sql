CREATE TABLE applications (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    biography TEXT,
    contract_accepted TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);