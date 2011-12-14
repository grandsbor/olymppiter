SET NAMES utf8;

CREATE TABLE IF NOT EXISTS `contests` (
    `contest_id`   SMALLINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `parent_id`    SMALLINT UNSIGNED NOT NULL,
    `contest_name` VARCHAR(255) NOT NULL,
    INDEX(parent_id)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `tasks` (
    `task_id`    MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `contest_id` SMALLINT UNSIGNED NOT NULL,
    `task_name`  VARCHAR(255) NOT NULL,
    `status`     TINYINT(1) NOT NULL
    INDEX(contest_id),
    INDEX(`status`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `students` (
    `student_id`   MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `student_name` VARCHAR(255) NOT NULL
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `contestants` (
    `contestant_id` MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `student_id`    MEDIUMINT UNSIGNED NOT NULL,
    `contest_id`    SMALLINT UNSIGNED NOT NULL,
    `code`          VARCHAR(9) NOT NULL,
    INDEX(student_id),
    INDEX(contest_id),
    INDEX(code)
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
    `judge_name`  VARCHAR(255) NOT NULL,
    `judge_email` VARCHAR(255) NOT NULL
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `judges_by_tasks` (
    `judge_id` SMALLINT UNSIGNED NOT NULL,
    `task_id`  MEDIUMINT UNSIGNED NOT NULL,
    INDEX(judge_id),
    INDEX(task_id)
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
    `judge_id`    SMALLINT UNSIGNED NOT NULL,
    `code`        VARCHAR(9) NOT NULL,
    INDEX(task_id),
    INDEX(code),
    INDEX(judge_id)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `marks_tmp` (
    `mark_id`     INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `subtask_id`  MEDIUMINT UNSIGNED NOT NULL,
    `solution_id` INT UNSIGNED NOT NULL,
    `mark_value`  DECIMAL(3,1) UNSIGNED NOT NULL,
    INDEX(subtask_id),
    INDEX(solution_id)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `final_marks` (
    `mark_id`     INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `subtask_id`  MEDIUMINT UNSIGNED NOT NULL,
    `solution_id` INT UNSIGNED NOT NULL,
    `mark_value`  DECIMAL(3,1) UNSIGNED NOT NULL,
    INDEX(subtask_id),
    INDEX(solution_id)
) ENGINE = INNODB;
