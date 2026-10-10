<?php require dirname(__DIR__,2).'/bootstrap.php'; need_user(); $message=''; $url='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 csrf(); $f=$_FILES['image']??null;
 if (!$f || $f['error']!==UPLOAD_ERR_OK || $f['size']>100000) $message='Ошибка файла (лимит 100 КБ).';
 elseif ($f['type']!=='image/jpeg') { http_response_code(400); $message='Ожидается image/jpeg.'; }
 else {
  // Intentionally trusts the client MIME and retains the supplied extension.
  $name=basename($f['name']);
  if(!preg_match('/^[a-zA-Z0-9_.-]+$/',$name)) $message='Недопустимое имя.';
  else { $url='/lab/uploads/'.bin2hex(random_bytes(4)).'-'.$name; move_uploaded_file($f['tmp_name'],__DIR__.'/uploads/'.basename($url)); $message='Файл принят.'; }
 }
}
head('Загрузка файлов'); ?>
<section><h1>Загрузка файлов</h1><p class="notice">Изолированный учебный пример</p><form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=h(token())?>"><label>Изображение<input type="file" name="image" required accept="image/jpeg"></label><button class="button">Загрузить</button></form><p role="status"><?=h($message)?></p><?php if($url): ?><a id="uploaded-file" href="<?=h($url)?>">Открыть файл</a><?php endif; ?></section><?php foot(); ?>
