<?php
$task_id = (int)$_GET['task_id'];
header("Content-type: application/csv");
header("Content-Disposition: attachment; filename=\"result-task-" . $task_id . ".csv\"");
require_once('lib/header.php');
require_once('lib/lib_main.php');

if($task_id) {
   $res = get_task_result($task_id);
   
   foreach($res as $code => $mark){
      echo $code . ';' . $mark . "\n";
   }
}
