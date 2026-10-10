<?php
declare(strict_types=1);
$config = require __DIR__ . '/config.php';
session_name('shubnikov_web3');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
function db(bool $lab = false): PDO {
    global $config;
    return new PDO('mysql:host=127.0.0.1;port=3307;dbname=shubnikov_web3;charset=utf8mb4',
        $lab ? $config['lab_user'] : $config['user'],
        $lab ? $config['lab_password'] : $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]);
}
function h(?string $value): string { return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(24)); }
function csrf(): void {
    if (!hash_equals(token(), (string)($_POST['csrf'] ?? ''))) { http_response_code(403); exit('CSRF validation failed'); }
}
function go(string $path): never { header('Location: ' . $path); exit; }
function user(): ?array {
    if (!isset($_SESSION['user_id'])) return null;
    $q = db()->prepare('SELECT id,name,email FROM users WHERE id=?');
    $q->execute([$_SESSION['user_id']]); return $q->fetch(PDO::FETCH_ASSOC) ?: null;
}
function need_user(): array { $u = user(); if (!$u) go('/login.php'); return $u; }
function head(string $title): void {
    $u = user();
    echo '<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . h($title) . ' | Шубников А.А.</title><link rel="stylesheet" href="/assets/styles.css"></head><body><header><a class="brand" href="/index.php">Шубников А.А.</a><nav aria-label="Основная навигация">';
    foreach (['/index.php'=>'Главная','/posts.php'=>'Посты','/profile.php'=>'Профиль','/lab/index.php'=>'Исследования'] as $href=>$label) {
        echo '<a href="'.$href.'">'.$label.'</a>';
    }
    if ($u) echo '<form method="post" action="/logout.php"><input type="hidden" name="csrf" value="'.h(token()).'"><button class="link" type="submit">Выйти</button></form>';
    else echo '<a href="/login.php">Вход</a><a href="/registration.php">Регистрация</a>';
    echo '</nav></header><main>';
}
function foot(): void { echo '</main><footer>Шубников А.А. · WEB, лабораторная №3 · Учебный стенд</footer><script src="/assets/app.js"></script></body></html>'; }
function posts(array $rows): void {
    foreach ($rows as $p) {
        echo '<article class="post"><p class="meta">'.h($p['name']).' · '.h($p['created_at']).'</p><h2>'.h($p['title']).'</h2><p class="body">'.nl2br(h($p['body'])).'</p>';
        if ($p['image']) echo '<img class="post-image" src="/uploads/'.h($p['image']).'" alt="Изображение публикации">';
        echo '</article>';
    }
    if (!$rows) echo '<p class="empty">Публикаций пока нет.</p>';
}
