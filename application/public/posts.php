<?php require dirname(__DIR__).'/bootstrap.php';
$u=need_user(); $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 csrf(); $title=trim((string)($_POST['title']??'')); $body=trim((string)($_POST['body']??'')); $image=null;
 if ($title==='' || strlen($title)>200 || $body==='' || strlen($body)>20000) $error='Проверьте заголовок и текст.';
 if (!$error && isset($_FILES['image']) && $_FILES['image']['error']!==UPLOAD_ERR_NO_FILE) {
  $f=$_FILES['image']; $allowed=['image/png'=>'png','image/jpeg'=>'jpg'];
  $mime=$f['error']===UPLOAD_ERR_OK ? (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']) : '';
  if ($f['error']!==UPLOAD_ERR_OK || $f['size']>2000000 || !isset($allowed[$mime]) || !getimagesize($f['tmp_name'])) $error='Нужен PNG или JPEG размером до 2 МБ.';
  else { $image=bin2hex(random_bytes(16)).'.'.$allowed[$mime]; if(!move_uploaded_file($f['tmp_name'],__DIR__.'/uploads/'.$image)) $error='Не удалось сохранить изображение.'; }
 }
 if (!$error) { $q=db()->prepare('INSERT INTO posts(user_id,title,body,image) VALUES(?,?,?,?)'); $q->execute([$u['id'],$title,$body,$image]); go('/posts.php'); }
}
$q=db()->prepare('SELECT p.*,u.name FROM posts p JOIN users u ON u.id=p.user_id WHERE p.user_id=? ORDER BY p.id DESC'); $q->execute([$u['id']]);
head('Посты'); ?>
<section><h1>Мои публикации</h1><form method="post" enctype="multipart/form-data" class="editor"><input type="hidden" name="csrf" value="<?=h(token())?>">
<label>Заголовок<input name="title" required maxlength="200" value="<?=h($_POST['title']??'')?>"></label>
<label>Текст<textarea name="body" required maxlength="20000" rows="4"><?=h($_POST['body']??'')?></textarea></label>
<label>Изображение<input name="image" type="file" accept="image/png,image/jpeg"></label>
<p class="error" role="alert"><?=h($error)?></p><button class="button">Опубликовать</button></form>
<?php posts($q->fetchAll(PDO::FETCH_ASSOC)); ?></section><?php foot(); ?>
