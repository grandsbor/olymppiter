<?php
require_once('lib/header.php');

$action = $_GET['action'];

switch($action) {
    case "save_marks":
        $result = save_temporary_marks(
            (int)$_GET['task_id'],
            (int)$_GET['judge_id'],
            array(array('id' => $_GET['id'], 'code' => $_GET['code'], 'marks' => $_GET['mark']))
        );
        if ($result) {
            echo json_encode(array('result' => true, 'id' => $result[0]));
        } else {
            echo json_encode(array('result' => false));
        }
        break;
}
