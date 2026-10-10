<?php require dirname(__DIR__).'/bootstrap.php';
$u = user();
$rows = db()->query('SELECT p.*,u.name FROM posts p JOIN users u ON p.user_id=u.id ORDER BY p.id DESC LIMIT 30')->fetchAll(PDO::FETCH_ASSOC);
head('Главная'); ?>
<section class="intro"><p class="eyebrow">Портфолио</p><h1>Шубников А.А.</h1><p class="lead">Веб-технологии, компьютерные сети и информационная безопасность.</p>
<?php if ($u): ?><p>С возвращением, <?=h($u['name'])?>.</p><a class="button" href="/posts.php">Новая публикация</a><?php else: ?><a class="button" href="/registration.php">Создать профиль</a><?php endif; ?></section>
<section><h2>Последние публикации</h2><?php posts($rows); ?></section>
<?php foot(); ?>
