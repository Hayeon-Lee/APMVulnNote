<?php
require_once __DIR__.'/../src/lib/db.php';

$pdo = db();

// 비밀번호 해시는 PHP에서 처리 (bcrypt/argon 등 PHP 기본)
$adminHash = password_hash('admin', PASSWORD_DEFAULT);
$user1Hash = password_hash('user1', PASSWORD_DEFAULT);

// admin이 이미 있으면 무시하려면 try/catch 또는 IGNORE 사용
$pdo->prepare("INSERT IGNORE INTO users(username,password,role) VALUES (?,?,?)")
    ->execute(['admin', $adminHash, 'admin']);

$pdo->prepare("INSERT IGNORE INTO users(username,password,role) VALUES (?,?,?)")
    ->execute(['user1', $user1Hash, 'user']);

// 데모 글
$pdo->exec("INSERT INTO posts(user_id,title,body) VALUES
  (1,'Welcome','첫 글입니다.'),
  (2,'Hello','테스트 포스트')
");

echo "Seeded\n";