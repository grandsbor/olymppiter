<?php
require_once('lib/header.php');
require_once('lib/lib_main.php');

$task_id = (int)$_GET['task_id'];

$smarty->assign('task_id',$task_id);

$cols = get_subtasks($task_id);

$smarty->assign('cols',$cols);

$smarty->display('compare_table.tpl');