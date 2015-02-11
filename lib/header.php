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

$db = mysql_connect($config['mysql']['host'], $config['mysql']['user'], $config['mysql']['passwd']) or die ("Unable to connect to mysql server");
if (!mysql_query("USE ".$config['mysql']['dbname'])) {
    die ("Unable to open mysql database");
}
mysql_query("SET names utf8");
?>
