USE studyplanner;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY users_email_unique (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS subjects (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(1000) NOT NULL DEFAULT '',
    color CHAR(7) NOT NULL DEFAULT '#26745c',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY subjects_user_name_unique (user_id, name),
    CONSTRAINT subjects_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB;

ALTER TABLE tasks
    ADD COLUMN user_id BIGINT UNSIGNED NULL AFTER id,
    ADD COLUMN subject_id BIGINT UNSIGNED NULL AFTER user_id,
    ADD COLUMN description VARCHAR(2000) NOT NULL DEFAULT '' AFTER title,
    ADD COLUMN status ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending' AFTER priority,
    ADD INDEX tasks_user_status_index (user_id, status),
    ADD INDEX tasks_subject_id_index (subject_id),
    ADD CONSTRAINT tasks_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    ADD CONSTRAINT tasks_subject_id_foreign FOREIGN KEY (subject_id) REFERENCES subjects (id) ON DELETE SET NULL;

UPDATE tasks SET status = IF(is_completed = 1, 'completed', 'pending');