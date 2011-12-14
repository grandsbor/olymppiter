<?php
require_once('lib/header.php');
require_once('lib/lib_main.php');

$judge_id = (int)$_GET['judge_id'];
$task_id = (int)$_GET['task_id'];

$smarty->assign('task_id',$task_id);
$smarty->assign('judge_id',$judge_id);

$cols = get_subtasks($task_id);

$smarty->assign('cols',json_encode($cols));

$rows = get_temporary_marks($task_id,$judge_id);
$rows_reordered = array();
foreach($rows as $row) {
   $fields = array();
   foreach($cols as $col) {
      $fields[] = $row['fields'][$col['id']];
   }
   $rows_reordered[] = array('id' => $row['id'], 'code' => $row['code'], 'fields' => $fields);
}

$smarty->assign('rows',json_encode($rows_reordered));

$smarty->display('one_judge_table.tpl');