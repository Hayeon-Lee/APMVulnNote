<?php
require_once __DIR__.'/../src/lib/db.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 헬스체크
if ($path === '/health') {
  try { db()->query('SELECT 1'); http_response_code(200); echo 'OK'; }
  catch (Throwable $e) { http_response_code(500); echo 'DB_FAIL'; }
  exit;
}

// 기본 페이지(임시)
?><!doctype html>
<html><head><meta charset="utf-8">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<title>APMVulnNote</title></head>
<body class="p-4">
  <h1>APMVulnNote</h1>
  <p>스캐폴딩 완료! <code>/health</code> 체크로 DB 연결 상태를 확인하세요.</p>
</body></html>