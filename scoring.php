<?php

if (php_sapi_name() != 'cli')
    die("This script is for CLI only");

if ($argc % 2 == 1 || $argc < 4 || !is_numeric($argv[1])) {
    die("Usage: php $argv[0] <task id> <subtask1_name> <subtask1_max_mark> [<subtask2_name> <subtask2_max_mark> [...]]\n");
}

require_once('db.php');


function update_marks($task_id, $subtasks) {
    global $pdo_db;
    $res = $pdo_db->query("SELECT task_name FROM tasks WHERE task_id = $task_id");
    $task_name = $res->fetchAll()[0]["task_name"];
    print "Updating subtasks for task: $task_name...";

    $pdo_db->query("DELETE FROM marks_tmp WHERE subtask_id IN (SELECT subtask_id FROM subtasks WHERE task_id = $task_id)");
    $pdo_db->query("DELETE FROM final_marks WHERE subtask_id IN (SELECT subtask_id FROM subtasks WHERE task_id = $task_id)");
    $pdo_db->query("DELETE FROM subtasks WHERE task_id = $task_id");

    $order = 1;
    foreach ($subtasks as $st) {
        $pdo_db->query("INSERT INTO subtasks VALUES(NULL, $task_id, $st[1], ".($order++).", ".$pdo_db->quote($st[0]).")");
    }

    print "Ok\n";
}


$pdo_db->beginTransaction();
$task_id = intval($argv[1]);
if ($task_id <= 36) {
    die("Editing old tasks blocked just in case\n");
}
$subtasks = array();
$total_mark = 0;
for ($i = 2; $i < $argc; $i += 2) {
    $mark = intval($argv[$i+1]);
    $subtasks[] = array($argv[$i], $mark);
    $total_mark += $mark;
}
if ($total_mark != 24) {
    die("Marks do not sum up tp 24\n");
}
update_marks($task_id, $subtasks);
$pdo_db->commit();
