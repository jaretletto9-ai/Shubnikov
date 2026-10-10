<?php require dirname(__DIR__,2).'/bootstrap.php'; need_user();
header('Content-Type: application/json; charset=utf-8');
$id=filter_input(INPUT_GET,'test_id',FILTER_VALIDATE_INT);
if (!$id || $id<1) { http_response_code(400); exit('{"error":"Integer id required"}'); }
$q=db(true)->prepare('SELECT id,login,email,password FROM demo_users WHERE id=?'); $q->execute([$id]); echo json_encode($q->fetchAll(PDO::FETCH_ASSOC),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
