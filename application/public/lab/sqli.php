<?php require dirname(__DIR__,2).'/bootstrap.php'; need_user();
header('Content-Type: application/json; charset=utf-8');
$id=(string)($_GET['test_id']??'1');
if (strlen($id)>2000) { http_response_code(400); exit('{}'); }
// Intentionally vulnerable. Only a lab account and fabricated demo_users records.
try { $rows=db(true)->query('SELECT id,login,email,password FROM demo_users WHERE id='.$id)->fetchAll(PDO::FETCH_ASSOC); echo json_encode($rows,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT); }
catch (PDOException $e) { http_response_code(400); echo json_encode(['error'=>$e->getMessage()]); }
