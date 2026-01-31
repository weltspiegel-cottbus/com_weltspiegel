CREATE TABLE IF NOT EXISTS `#__ws_cinetixx_events`
(
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `event_id`    VARCHAR(32) NOT NULL DEFAULT '',
    `trailer_id`  VARCHAR(32),
    `poster`      VARCHAR(512),
    `poster_big`  VARCHAR(512),
    CONSTRAINT idx_event_id UNIQUE (`event_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;