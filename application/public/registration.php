<?php require dirname(__DIR__).'/bootstrap.php';
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 csrf();
 $name=trim((string)($_POST['name']??'')); $email=trim((string)($_POST['email']??''));
 $password=(string)($_POST['password']??'');
 if (strlen($name)<2 || strlen($name)>100 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>254 || strlen($password)<8 || strlen($password)>72 || $password!==($_POST['confirmation']??'')) $error='Проверьте имя, email и совпадение паролей (8–72 байта).';
 else {
  try {
   $connection=db(); $q=$connection->prepare('INSERT INTO users(name,email,password_hash) VALUES(?,?,?)');
   $q->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
   session_regenerate_id(true); $_SESSION['user_id']=(int)$connection->lastInsertId(); go('/profile.php');
  } catch (PDOException $e) { if ($e->getCode()==='23000') $error='Этот email уже зарегистрирован.'; else throw $e; }
 }
}
head('Регистрация'); ?>
<section class="form-section"><h1>Регистрация</h1><form method="post"><input type="hidden" name="csrf" value="<?=h(token())?>">
<label>Имя<input name="name" required maxlength="100" autocomplete="name" value="<?=h($_POST['name']??'')?>"></label>
<label>Email<input type="email" name="email" required maxlength="254" autocomplete="email" value="<?=h($_POST['email']??'')?>"></label>
<label>Пароль<input type="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password"></label>
<label>Повтор пароля<input type="password" name="confirmation" required minlength="8" maxlength="72" autocomplete="new-password"></label>
<p class="error" role="alert"><?=h($error)?></p><button class="button">Создать профиль</button></form></section><?php foot(); ?>
