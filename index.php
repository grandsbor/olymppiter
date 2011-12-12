<?php
require_once('lib/header.php');
$cols = array(
   array(
      'id' => 1,
      'name' => 'Колонка 1',
      'max' => 5
   ),
   array(
      'id' => 2,
      'name' => 'Колонка 2',
      'max' => 3
   ),
   array(
      'id' => 3,
      'name' => 'Колонка 3',
      'max' => 7
   ),
);
$smarty->assign('cols',json_encode($cols));
$rows = array(
   
);
$smarty->display('one_judge_table.tpl');