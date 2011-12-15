<?php
require_once('lib/header.php');
require_once('lib/lib_main.php');

$task_id = (int)$_GET['task_id'];
$smarty->assign('task_id',$task_id);

$cols = get_subtasks($task_id);
$smarty->assign('cols',$cols);

$smarty->assign('judges',get_judges_for_task($task_id));

$marks = get_aggregate_marks($task_id);
//var_dump($marks);
$smarty->assign('data',$marks);

$smarty->display('compare_table.tpl');