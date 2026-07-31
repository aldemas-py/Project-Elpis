-- ============================================================
-- Elpis Counselling Centre - Database Schema
-- ============================================================
CREATE DATABASE IF NOT EXISTS elpis_counselling;
USE elpis_counselling;
-- ------------------------------------------------------------
-- Admin Users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- Default admin: username = admin, password = admin123 (CHANGE IN PRODUCTION!)
INSERT INTO admin_users (username, password_hash)
VALUES (
        'admin',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    );
-- ------------------------------------------------------------
-- Services
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    icon VARCHAR(100) DEFAULT 'heart',
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
INSERT INTO services (title, description, icon, display_order)
VALUES (
        'Career Counselling',
        'Guidance and support for career transitions, job search strategies, and professional development planning.',
        'briefcase',
        1
    ),
    (
        'Couple Counselling',
        'Strengthen your relationship through improved communication, conflict resolution, and mutual understanding.',
        'heart',
        2
    ),
    (
        'Children & Adolescent Counselling',
        'Specialized support for young people dealing with family, school, or personal challenges.',
        'child',
        3
    ),
    (
        'Pregnancy Crisis Support',
        'Compassionate care and guidance for those navigating unplanned pregnancy or pregnancy-related concerns.',
        'baby',
        4
    ),
    (
        'Addiction Counselling',
        'Recovery support for drug, alcohol, food, gambling, social media, and other addictive behaviours.',
        'shield',
        5
    ),
    (
        'Family Counselling',
        'Improve family dynamics, communication patterns, and strengthen bonds between family members.',
        'users',
        6
    ),
    (
        'Group Counselling',
        'Therapeutic group sessions that provide mutual support and shared growth experiences.',
        'people',
        7
    ),
    (
        'HIV/AIDS Counselling',
        'Confidential support for individuals and families affected by HIV/AIDS, including testing guidance.',
        'ribbon',
        8
    ),
    (
        'Loss, Grief & Bereavement Counselling',
        'Navigate the difficult journey of loss with compassionate, professional support.',
        'flower',
        9
    ),
    (
        'Marriage Counselling',
        'Deepen your marital bond, address challenges, and build a stronger partnership.',
        'ring',
        10
    ),
    (
        'Mid-Life Crisis Counselling',
        'Navigate life transitions, identity questions, and existential concerns during mid-life.',
        'compass',
        11
    ),
    (
        'Parent-Child Relationship Counselling',
        'Improve understanding and connection between parents and children across all ages.',
        'tree',
        12
    ),
    (
        'Premarital Counselling',
        'Build a strong foundation for your marriage with evidence-based premarital guidance.',
        'star',
        13
    ),
    (
        'Peer Counselling',
        'Trained peer support for those who benefit from shared lived experiences.',
        'handshake',
        14
    ),
    (
        'Psycho-Education',
        'Workshops on anger management, stress management, assertiveness, and problem-solving skills.',
        'book',
        15
    ),
    (
        'Personality Issues Support',
        'Therapeutic support for anxiety, phobias, and related personality concerns.',
        'brain',
        16
    ),
    (
        'Sexual Problems Counselling',
        'Confidential, sensitive support for sexual health and relationship concerns.',
        'lock',
        17
    ),
    (
        'Suicide Intervention',
        'Immediate, compassionate crisis intervention and ongoing support for those in distress.',
        'phone',
        18
    ),
    (
        'Trauma Counselling',
        'Trauma-informed care using evidence-based modalities including EMDR and narrative therapy.',
        'umbrella',
        19
    );
-- ------------------------------------------------------------
-- Appointments (Booking Inquiries)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    service VARCHAR(200) DEFAULT NULL,
    preferred_date DATE DEFAULT NULL,
    message TEXT,
    status ENUM('pending', 'contacted', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- ------------------------------------------------------------
-- Articles (Educational Materials)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content LONGTEXT,
    excerpt TEXT,
    author VARCHAR(100) DEFAULT 'Elpis Counselling Centre',
    image VARCHAR(255) DEFAULT NULL,
    category VARCHAR(100) DEFAULT 'General',
    is_published TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- ------------------------------------------------------------
-- Events
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time VARCHAR(50) DEFAULT NULL,
    venue VARCHAR(255) DEFAULT NULL,
    price DECIMAL(10, 2) DEFAULT 0.00,
    image VARCHAR(255) DEFAULT NULL,
    max_participants INT DEFAULT NULL,
    is_published TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- ------------------------------------------------------------
-- Event Bookings (M-Pesa Payments)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS event_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    mpesa_code VARCHAR(50) DEFAULT NULL,
    amount DECIMAL(10, 2) DEFAULT 0.00,
    status ENUM('pending', 'confirmed', 'cancelled', 'refunded') DEFAULT 'pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- ------------------------------------------------------------
-- Testimonials
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    client_role VARCHAR(100) DEFAULT NULL,
    content TEXT NOT NULL,
    rating TINYINT DEFAULT 5,
    image VARCHAR(255) DEFAULT NULL,
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- ------------------------------------------------------------
-- Contact Messages
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;