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
   array(
      'id' => 1,
      'code' => '3-38HTE-8',
      'fields' => array(
         3 => array(
            'id' => 8,
            'value' => 5,
            'subtask' => 3
         ),
         1 => array(
            'id' => 9,
            'value' => 3,
            'subtask' => 1
         ),
         2 => array(
            'id' => 11,
            'value' => 2,
            'subtask' => 2
         ),
      ),
   ),
   array(
      'id' => 2,
      'code' => '4-56EWQ-2',
      'fields' => array(
         1 => array(
            'id' => 1,
            'value' => 2,
            'subtask' => 1
         ),
         3 => array(
            'id' => 2,
            'value' => 4,
            'subtask' => 3
         ),
         2 => array(
            'id' => 3,
            'value' => 3,
            'subtask' => 2
         ),
      ),
   ),
);
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