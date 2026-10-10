<?php require dirname(__DIR__,2).'/bootstrap.php'; need_user(); $rows=db()->query('SELECT * FROM lab_notes ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC); head('XSS: исправленный вариант'); ?>
<section><h1>XSS: исправленный вариант</h1><?php foreach ($rows as $p): ?><article class="post"><h2><?=h($p['title'])?></h2></article><?php endforeach; ?></section><?php foot(); ?>
