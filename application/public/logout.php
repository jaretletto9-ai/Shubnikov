<?php require dirname(__DIR__).'/bootstrap.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); exit('POST required'); }
csrf(); $_SESSION=[]; session_destroy();
setcookie(session_name(),'', ['expires'=>time()-3600,'path'=>'/','httponly'=>true,'samesite'=>'Lax']);
go('/login.php');
