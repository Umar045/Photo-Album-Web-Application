-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS photo_album;
USE photo_album;

-- Create photos table
CREATE TABLE IF NOT EXISTS photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    filename VARCHAR(255) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_filename (filename)
);

-- Create indexes for better search performance
CREATE INDEX idx_title ON photos(title);
CREATE INDEX idx_upload_date ON photos(upload_date);

-- Insert test data (optional - uncomment if you want to test)
/*
INSERT INTO photos (title, description, filename) VALUES
('Test Photo 1', 'This is a test photo description', 'test1.jpg'),
('Sunset Photo', 'Beautiful sunset at the beach', 'sunset.jpg'),
('Family Picture', 'Family gathering at Christmas', 'family.jpg');
*/

-- Verify table structure
DESCRIBE photos;

-- Verify indexes
SHOW INDEX FROM photos;

-- Test queries
SELECT * FROM photos ORDER BY upload_date DESC LIMIT 5;
SELECT * FROM photos WHERE title LIKE '%test%' OR description LIKE '%test%';