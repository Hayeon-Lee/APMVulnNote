<?php
function start_secure_session() {
  // 개발용: HTTPS 아니어도 테스트 가능하도록 Secure옵션은 일단 보류, 나중에 prod에서 켬
  session_set_cookie_params([
    'lifetime' => 0, 'path' => '/', 'httponly' => true,
    'samesite' => 'Lax'
  ]);
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
}

function current_user() { return $_SESSION['user'] ?? null; }
function require_login() {
  if (!current_user()) { header('Location: /login'); exit; }
}