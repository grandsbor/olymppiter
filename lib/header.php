<?php
if (!headers_sent()) {
    session_start();
    header("Content-type: text/html; charset=utf-8");
}

$config = parse_ini_file(dirname(__FILE__) . '/../config.ini', true);
?>
