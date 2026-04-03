CREATE TABLE application_languages (
    application_id INT(10) UNSIGNED NOT NULL,
    language_id INT(10) UNSIGNED NOT NULL,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES programming_languages(id),
    PRIMARY KEY (application_id, language_id)
);