<?php
require_once('lib/header.php');

$action = $_GET['action'];

switch($action) {
   case "save_marks":
      echo json_encode(array('result'=>true));
      break;
}