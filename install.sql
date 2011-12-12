SET NAMES utf8;

CREATE TABLE IF NOT EXISTS `tasks` (
    `task_id`   MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `task_name` VARCHAR(255) NOT NULL
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `subtasks` (
    `subtask_id` MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `task_id`    MEDIUMINT UNSIGNED NOT NULL,
    `max_mark`   TINYINT UNSIGNED NOT NULL,
    `orderby`    TINYINT UNSIGNED NOT NULL,
    `comment`   TEXT NOT NULL,
    INDEX(task_id),
    INDEX(orderby)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `judges` (
    `judge_id`    SMALLINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `judge_email` VARCHAR(255) NOT NULL
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `solutions` (
    `solution_id`   INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `task_id`       MEDIUMINT UNSIGNED NOT NULL,
    `contestant_id` MEDIUMINT UNSIGNED NOT NULL,
    `comment`       TEXT NOT NULL,
    INDEX(task_id),
    INDEX(contestant_id)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `solutions_tmp` (
    `solution_id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `task_id`     MEDIUMINT UNSIGNED NOT NULL,
    `code`        VARCHAR(9) NOT NULL,
    INDEX(task_id),
    INDEX(code)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `marks_tmp` (
    `mark_id`     INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `subtask_id`  MEDIUMINT UNSIGNED NOT NULL,
    `solution_id` INT UNSIGNED NOT NULL,
    `judge_id`    SMALLINT UNSIGNED NOT NULL,
    `mark_value`  TINYINT UNSIGNED NOT NULL,
    INDEX(subtask_id),
    INDEX(solution_id),
    INDEX(judge_id)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `final_marks` (
    `mark_id`     INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `subtask_id`  MEDIUMINT UNSIGNED NOT NULL,
    `solution_id` INT UNSIGNED NOT NULL,
    `mark_value`  TINYINT UNSIGNED NOT NULL,
    INDEX(subtask_id),
    INDEX(solution_id)
) ENGINE = INNODB;
