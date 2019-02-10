<?php
$config = parse_ini_file(dirname(__FILE__) . '/config.ini', true);

$pdo_db = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8', $config['mysql']['host'], $config['mysql']['dbname']), $config['mysql']['user'], $config['mysql']['passwd']);
$pdo_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
