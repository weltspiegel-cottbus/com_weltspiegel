ALTER TABLE `#__ws_cinetixx_events`
    ADD COLUMN `poster` VARCHAR(512) AFTER `trailer_id`,
    ADD COLUMN `poster_big` VARCHAR(512) AFTER `poster`;
