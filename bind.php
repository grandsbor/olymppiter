<?php

if (php_sapi_name() != 'cli')
    die("This script is for CLI only");

if ($argc < 3) {
    die("Usage: $argv[0] <task id> [-]<judge id>\n");
}

require_once('db.php');

$task_id = intval($argv[1]);
$judge_id = intval($argv[2]);
$delete = $judge_id < 0;
$judge_id = abs($judge_id);

$res = $pdo_db->query("SELECT judge_name FROM judges WHERE judge_id = $judge_id");
$judge_name = $res->fetchAll()[0]["judge_name"];

$res = $pdo_db->query("SELECT task_name FROM tasks WHERE task_id = $task_id");
$task_name = $res->fetchAll()[0]["task_name"];

if ($delete) {
    $pdo_db->query("DELETE FROM judges_by_tasks WHERE judge_id = $judge_id AND task_id = $task_id");
    echo "Done: $judge_name is now NOT a judge for $task_name\n";
} else {
    $pdo_db->query("INSERT INTO judges_by_tasks VALUES ($judge_id, $task_id)");
    echo "Done: $judge_name is now a judge for $task_name\n";
}
