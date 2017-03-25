<?php
require_once('lib/header.php');
require_once('lib/lib_main.php');

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'login':
            $judge_id = (int)$_POST['judge_id'];
            $passwd = $_POST['passwd'];
            if (check_judge_passwd($judge_id, $passwd)) {
                $_SESSION['judge_id'] = $judge_id;
                header("Location:index.php");
            }
            else {
                print "Login failed, <a href=\"login.php\">try again</a>";
            }
            break;
        case 'logout':
            unset($_SESSION['judge_id']);
            header("Location:index.php");
            break;
    }
}
else {
    $smarty->assign('judges', get_all_judges());
    $smarty->display('login.tpl');
}
