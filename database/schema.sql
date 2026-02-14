CREATE DATABASE IF NOT EXISTS school_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_cms;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS page_sections (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_slug VARCHAR(60) NOT NULL,
    section_key VARCHAR(80) NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    is_enabled TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_page_section (page_slug, section_key),
    KEY idx_page_enabled (page_slug, is_enabled)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS gallery_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_gallery_active (is_active)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS home_banners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    subtitle VARCHAR(255) DEFAULT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_banner_active_sort (is_active, sort_order)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admission_inquiries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(120) NOT NULL,
    parent_name VARCHAR(120) NOT NULL,
    class_applying VARCHAR(50) NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    email VARCHAR(150) NOT NULL,
    address VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_status (status),
    KEY idx_created_at (created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS custom_pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,
    excerpt TEXT DEFAULT NULL,
    content LONGTEXT NOT NULL,
    hero_image VARCHAR(255) DEFAULT NULL,
    is_enabled TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_custom_pages_enabled_sort (is_enabled, sort_order)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS menu_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id INT UNSIGNED DEFAULT NULL,
    label VARCHAR(140) NOT NULL,
    item_type ENUM('static','custom_page','custom_path','external') NOT NULL DEFAULT 'static',
    link_value VARCHAR(255) DEFAULT NULL,
    page_id INT UNSIGNED DEFAULT NULL,
    icon_class VARCHAR(120) DEFAULT NULL,
    open_in_new_tab TINYINT(1) NOT NULL DEFAULT 0,
    is_enabled TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_menu_parent_sort (parent_id, sort_order),
    KEY idx_menu_enabled (is_enabled),
    KEY idx_menu_page (page_id),
    CONSTRAINT fk_menu_parent FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    CONSTRAINT fk_menu_page FOREIGN KEY (page_id) REFERENCES custom_pages(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO site_settings (setting_key, setting_value) VALUES
('school_name', 'Greenfield Public School'),
('school_tagline', 'Inspiring minds. Building character.'),
('primary_color', '#0b6efd'),
('default_mode', 'light'),
('contact_phone', '+1 000-000-0000'),
('contact_email', 'info@school.edu'),
('contact_address', '123 Education Avenue, City')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

INSERT INTO page_sections (page_slug, section_key, title, content, sort_order, is_enabled) VALUES
('home', 'welcome', 'A Future-Ready Learning Environment', 'Our school blends academics, values, and life skills to develop confident learners ready for tomorrow.', 1, 1),
('home', 'holistic_growth', 'Holistic Growth for Every Child', 'From STEM labs and sports programs to arts and leadership opportunities, every student explores their full potential.', 2, 1),
('home_facilities', 'smart_classrooms', 'Smart Classrooms', 'Interactive smart classrooms with student-centered teaching aids for engaging everyday learning.', 1, 1),
('home_facilities', 'science_lab', 'Science and Innovation Labs', 'Well-equipped labs that encourage experimentation, analytical thinking, and problem-solving skills.', 2, 1),
('home_facilities', 'sports_arena', 'Sports and Activity Arena', 'Indoor and outdoor activity zones that build discipline, fitness, teamwork, and leadership.', 3, 1),
('about', 'mission', 'Our Mission', 'To nurture compassionate, curious, and capable learners through high-quality education and strong values.', 1, 1),
('about', 'vision', 'Our Vision', 'To be a trusted school that empowers students to excel academically and socially in a changing world.', 2, 1),
('facilities', 'smart_classrooms', 'Smart Classrooms', 'Tech-enabled classrooms support interactive and personalized learning experiences.', 1, 1),
('facilities', 'library', 'Library and Reading Zones', 'A curated reading ecosystem that builds strong comprehension and lifelong reading habits.', 2, 1),
('infrastructure', 'campus', 'Safe and Green Campus', 'A secure, well-maintained campus with monitored entry points, hygiene zones, and open learning spaces.', 1, 1),
('infrastructure', 'labs', 'Science and Computer Labs', 'Dedicated labs designed for practical learning, innovation, and collaborative projects.', 2, 1)
ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content), sort_order = VALUES(sort_order), is_enabled = VALUES(is_enabled);
