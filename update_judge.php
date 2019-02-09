<?php

if (php_sapi_name() != 'cli')
    die("This script is for CLI only");

if ($argc != 2) {
    die("Usage: $argv[0] <judge id> (to generate new password) or $argv[0] \"<judge name>\" (to add new judge, mind the quotes!)\n");
}

$config = parse_ini_file(dirname(__FILE__) . '/config.ini', true);

$pdo_db = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8', $config['mysql']['host'], $config['mysql']['dbname']), $config['mysql']['user'], $config['mysql']['passwd']);
$pdo_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


function update_pwd($judge_id) {
    global $pdo_db;
    $pwd = '';
    for ($i = 0; $i < 8; ++$i) {
        $pwd .= chr(65 + rand(0, 25));
    }
    $pdo_db->query("UPDATE judges SET pwd_hash = '".md5($pwd)."' WHERE judge_id = $judge_id LIMIT 1");
    print "Judge id: $judge_id\nPassword: $pwd\n";
}


$pdo_db->beginTransaction();
if (is_numeric($argv[1])) {
    // treat as judge id, generate new password
    update_pwd(intval($argv[1]));
} else {
    print "Adding " . $argv[1]. "\n";
    // treat as judge name, add judge
    $pdo_db->query("INSERT INTO judges VALUES(NULL, '".$argv[1]."', '', '')");
    update_pwd($pdo_db->lastInsertId());
}
$pdo_db->commit();
