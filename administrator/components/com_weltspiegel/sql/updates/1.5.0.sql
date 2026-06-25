CREATE TABLE IF NOT EXISTS `#__ws_cinetixx_movies`
(
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `movie_id`    VARCHAR(32) NOT NULL DEFAULT '',
    `trailer_id`  VARCHAR(32),
    `poster`      VARCHAR(512),
    `poster_big`  VARCHAR(512),
    CONSTRAINT idx_ws_movies_movie_id UNIQUE (`movie_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;
