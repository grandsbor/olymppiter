<?php
$contest_id = 16;
header("Content-type: application/csv");
header("Content-Disposition: attachment; filename=\"result-full-" . $contest_id . ".csv\"");
require_once('lib/header.php');
require_once('lib/lib_main.php');

function get_full_result($cont_id) {
    global $pdo_db;
    $s = 'select code, tasks.task_id, ceil(sum(mark_value)) as mark from final_marks, subtasks, tasks, contestants, solutions where final_marks.subtask_id = subtasks.subtask_id and subtasks.task_id = tasks.task_id and (tasks.contest_id = 16 or tasks.contest_id = 17) and solutions.contestant_id = contestants.contestant_id and solutions.solution_id = final_marks.solution_id group by code,tasks.task_id';
    //echo $s;
    return fetch($s);
}

   $res = get_full_result($contest_id);
  
  $c = '';
  $rx = array();
  foreach($res as $r){
      if (($c != '') && ($r['code'] != $c)) {
	echo $c.';';
        $i = 9;
        while($i <= 36) {
		if (array_key_exists($i, $rx)) {
			echo $rx[$i].';';
		}else{
			echo ';';
		}
        	$i = $i + 1;
	}
	  echo "\n";
      }
      if ($r['code'] != $c) {
	  $c = $r['code'];
  	 $rx = array();
      }
      $rx[$r['task_id']] = $r['mark'];
      //echo $r['code'] . ';' . $r['task_id'] . ';' . $r['mark'] . "\n";
   }
