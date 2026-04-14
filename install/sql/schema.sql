CREATE TABLE IF NOT EXISTS users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS profiles (
    profile_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    phone VARCHAR(30) NULL,
    city VARCHAR(120) NULL,
    state VARCHAR(120) NULL,
    country VARCHAR(120) NULL,
    website VARCHAR(255) NULL,
    linkedin VARCHAR(255) NULL,
    github VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_profiles_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS templates (
    template_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(60) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS resumes (
    resume_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    template_id INT UNSIGNED NULL,
    title VARCHAR(180) NOT NULL,
    personal_data TEXT NULL,
    objective TEXT NULL,
    professional_summary TEXT NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_resumes_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_resumes_template FOREIGN KEY (template_id) REFERENCES templates(template_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS resume_sections (
    resume_section_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resume_id INT UNSIGNED NOT NULL,
    section_key VARCHAR(80) NOT NULL,
    section_title VARCHAR(120) NOT NULL,
    content LONGTEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_resume_sections_resume FOREIGN KEY (resume_id) REFERENCES resumes(resume_id) ON DELETE CASCADE,
    UNIQUE KEY uq_resume_section (resume_id, section_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS resume_experiences (
    experience_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resume_id INT UNSIGNED NOT NULL,
    company VARCHAR(160) NOT NULL,
    role VARCHAR(160) NOT NULL,
    start_period VARCHAR(30) NULL,
    end_period VARCHAR(30) NULL,
    description TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_resume_experiences_resume FOREIGN KEY (resume_id) REFERENCES resumes(resume_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS resume_educations (
    education_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resume_id INT UNSIGNED NOT NULL,
    institution VARCHAR(180) NOT NULL,
    degree VARCHAR(180) NOT NULL,
    start_period VARCHAR(30) NULL,
    end_period VARCHAR(30) NULL,
    description TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_resume_educations_resume FOREIGN KEY (resume_id) REFERENCES resumes(resume_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS resume_courses (
    course_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resume_id INT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    institution VARCHAR(180) NULL,
    completion_year VARCHAR(10) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_resume_courses_resume FOREIGN KEY (resume_id) REFERENCES resumes(resume_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS resume_skills (
    skill_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resume_id INT UNSIGNED NOT NULL,
    skill VARCHAR(120) NOT NULL,
    level VARCHAR(60) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_resume_skills_resume FOREIGN KEY (resume_id) REFERENCES resumes(resume_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS resume_languages (
    language_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resume_id INT UNSIGNED NOT NULL,
    language VARCHAR(120) NOT NULL,
    level VARCHAR(60) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_resume_languages_resume FOREIGN KEY (resume_id) REFERENCES resumes(resume_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS resume_certifications (
    certification_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resume_id INT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    issuer VARCHAR(180) NULL,
    issue_year VARCHAR(10) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_resume_certifications_resume FOREIGN KEY (resume_id) REFERENCES resumes(resume_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS resume_projects (
    project_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resume_id INT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    role VARCHAR(180) NULL,
    project_link VARCHAR(255) NULL,
    description TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_resume_projects_resume FOREIGN KEY (resume_id) REFERENCES resumes(resume_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS resume_links (
    link_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resume_id INT UNSIGNED NOT NULL,
    label VARCHAR(120) NOT NULL,
    url VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_resume_links_resume FOREIGN KEY (resume_id) REFERENCES resumes(resume_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS resume_versions (
    version_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resume_id INT UNSIGNED NOT NULL,
    version_label VARCHAR(100) NOT NULL,
    payload LONGTEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_resume_versions_resume FOREIGN KEY (resume_id) REFERENCES resumes(resume_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    setting_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(120) NOT NULL UNIQUE,
    `value` LONGTEXT NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 0,
    autoload TINYINT(1) NOT NULL DEFAULT 0,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ad_blocks (
    ad_block_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    position_code VARCHAR(80) NOT NULL,
    content_html TEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    display_order INT NOT NULL DEFAULT 0,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ad_position (position_code, is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logs (
    log_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    context VARCHAR(80) NOT NULL,
    level VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    metadata LONGTEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_logs_level (level),
    INDEX idx_logs_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_sessions (
    session_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    token VARCHAR(120) NOT NULL UNIQUE,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_sessions_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_resets (
    password_reset_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token VARCHAR(120) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
