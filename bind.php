<?php

if (php_sapi_name() != 'cli')
    die("This script is for CLI only");

if ($argc != 3) {
    die("Usage: $argv[0] <task id> <judge id>\n");
}

$config = parse_ini_file(dirname(__FILE__) . '/config.ini', true);

$pdo_db = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8', $config['mysql']['host'], $config['mysql']['dbname']), $config['mysql']['user'], $config['mysql']['passwd']);
$pdo_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$task_id = intval($argv[1]);
$judge_id = intval($argv[2]);

$res = $pdo_db->query("SELECT judge_name FROM judges WHERE judge_id = $judge_id");
$judge_name = $res->fetchAll()[0]["judge_name"];

$res = $pdo_db->query("SELECT task_name FROM tasks WHERE task_id = $task_id");
$task_name = $res->fetchAll()[0]["task_name"];

$pdo_db->query("INSERT INTO judges_by_tasks VALUES ($judge_id, $task_id)");
echo "Done: $judge_name is now a judge for $task_name\n";
