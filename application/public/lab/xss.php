<?php require dirname(__DIR__,2).'/bootstrap.php'; need_user();
if ($_SERVER['REQUEST_METHOD']==='POST') { csrf(); $title=(string)($_POST['title']??''); if (strlen($title)<2000) { $q=db()->prepare('INSERT INTO lab_notes(title) VALUES(?)'); $q->execute([$title]); } go('/lab/xss.php'); }
$rows=db()->query('SELECT * FROM lab_notes ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC); head('Хранимая XSS'); ?>
<section><h1>Хранимая XSS</h1><p class="notice">Учебные записи</p><form method="post"><input type="hidden" name="csrf" value="<?=h(token())?>"><label>Заголовок<input name="title" required maxlength="2000"></label><button class="button">Добавить</button></form>
<?php foreach ($rows as $p): ?>
<article class="post"><h2><?=$p['title']?></h2></article>
<?php endforeach; ?></section><?php foot(); ?>
