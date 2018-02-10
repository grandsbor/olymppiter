<?php
if (!headers_sent()) {
    session_start();
    header("Content-type: text/html; charset=utf-8");
}
error_reporting(E_ALL);
//ini_set('display_errors',1);

$config = parse_ini_file(dirname(__FILE__) . '/../config.ini', true);

require_once('lib_main.php');

//init Smarty
require_once('Smarty.class.php');

$smarty = new Smarty();
$smarty->template_dir = $config['smarty']['template_dir'];
$smarty->compile_dir  = $config['smarty']['compile_dir'];
$smarty->config_dir   = $config['smarty']['config_dir'];
$smarty->cache_dir    = $config['smarty']['cache_dir'];

$pdo_db = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8', $config['mysql']['host'], $config['mysql']['dbname']), $config['mysql']['user'], $config['mysql']['passwd']);
$pdo_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo_db->query("SET NAMES utf8");
?>
