<?php require dirname(__DIR__).'/bootstrap.php';
$u=need_user(); $q=db()->prepare('SELECT p.*,u.name FROM posts p JOIN users u ON u.id=p.user_id WHERE p.user_id=? ORDER BY p.id DESC'); $q->execute([$u['id']]);
head('Профиль'); ?>
<section><p class="eyebrow">Профиль</p><h1><?=h($u['name'])?></h1><p><?=h($u['email'])?></p>
<button class="button secondary" id="details-button" aria-controls="details" aria-expanded="false">Подробнее</button>
<div id="details" hidden class="details"><h2>Об авторе</h2><p>Изучаю веб-разработку и безопасность компьютерных сетей.</p></div>
<p><a href="/posts.php">Создать публикацию</a></p></section><section><h2>Мои публикации</h2><?php posts($q->fetchAll(PDO::FETCH_ASSOC)); ?></section><?php foot(); ?>
