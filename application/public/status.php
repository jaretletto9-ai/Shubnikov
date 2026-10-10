<?php require dirname(__DIR__).'/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['author'=>'Шубников А.А.','php'=>PHP_VERSION,'database'=>db()->query('SELECT DATABASE()')->fetchColumn(),'mariadb'=>db()->query('SELECT VERSION()')->fetchColumn(),'database_connection'=>'OK'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
