CREATE TABLE services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  mobile_number VARCHAR(20) UNIQUE,
  network ENUM('O2', 'EE', 'Plan.com'),
  start_date DATE,
  end_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE service_products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  service_id INT,
  type ENUM('1GB', '3GB', '5GB', '25GB', '60GB', '100GB'),
  price DECIMAL(10, 2),
  start_date DATE,
  end_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);
