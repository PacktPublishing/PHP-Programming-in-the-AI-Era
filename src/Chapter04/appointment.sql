CREATE TABLE `appointment` (
    `id`                  INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`               CHAR(16)         NOT NULL DEFAULT '',
    `location`            VARCHAR(255)     NOT NULL DEFAULT '',
    `contact_info`        VARCHAR(255)     NOT NULL DEFAULT '',
    `start_date_and_time` DATETIME         NOT NULL,
    `end_date_and_time`   DATETIME         NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
