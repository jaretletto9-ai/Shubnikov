<?php require dirname(__DIR__).'/bootstrap.php';
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 csrf(); $q=db()->prepare('SELECT id,password_hash FROM users WHERE email=?'); $q->execute([trim((string)($_POST['email']??''))]); $found=$q->fetch(PDO::FETCH_ASSOC);
 if ($found && password_verify((string)($_POST['password']??''),$found['password_hash'])) { session_regenerate_id(true); $_SESSION['user_id']=(int)$found['id']; go('/profile.php'); }
 $error='Неверный email или пароль.';
}
head('Вход'); ?>
<section class="form-section"><h1>Вход</h1><form method="post"><input type="hidden" name="csrf" value="<?=h(token())?>">
<label>Email<input type="email" name="email" required autocomplete="username"></label>
<label>Пароль<input type="password" name="password" required autocomplete="current-password"></label>
<p class="error" role="alert"><?=h($error)?></p><button class="button">Войти</button></form><p><a href="/registration.php">Создать профиль</a></p></section><?php foot(); ?>
