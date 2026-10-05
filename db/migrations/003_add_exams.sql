USE studyplanner;

CREATE TABLE IF NOT EXISTS exams (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NULL,
    title VARCHAR(120) NOT NULL,
    exam_at DATETIME NOT NULL,
    status ENUM('scheduled', 'completed', 'missed') NOT NULL DEFAULT 'scheduled',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX exams_user_datetime_index (user_id, exam_at),
    INDEX exams_subject_id_index (subject_id),
    CONSTRAINT exams_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT exams_subject_id_foreign FOREIGN KEY (subject_id) REFERENCES subjects (id) ON DELETE SET NULL
) ENGINE=InnoDB;