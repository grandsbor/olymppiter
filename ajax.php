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
        if (is_array($result)) {
            echo json_encode(array('result' => true, 'id' => $result[0]));
        } else {
            echo json_encode(array('result' => false, 'message' => $result));
        }
        break;
    case "save_aggr":
        $result = save_aggregate_string(
            (int)$_GET['task_id'],
            (int)$_GET['id'],
            $_GET['code'],
            $_GET['marks']
        );
        if (is_int($result)) {
            echo json_encode(array('result' => true, 'id' => $result));
        } else {
            echo json_encode(array('result' => false, 'message' => $result));
        }
        break;
    case "delete_marks":
        echo json_encode(array('result' => delete_temporary_solution((int)$_GET['id'])));
        break;
}
