<?php
require_once __DIR__.'/../src/lib/db.php';
require_once __DIR__.'/../src/lib/auth.php';
start_secure_session();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 헬스체크
if ($path === '/health') {
  try { db()->query('SELECT 1'); http_response_code(200); echo 'OK'; }
  catch (Throwable $e) { http_response_code(500); echo 'DB_FAIL'; }
  exit;
}

if ($path === '/login' && $_SERVER['REQUEST_METHOD']==='GET') { include __DIR__.'/../src/views/login.php'; exit; }
if ($path === '/login' && $_SERVER['REQUEST_METHOD']==='POST') {
  require_once __DIR__.'/../src/lib/db.php';
  $u = trim($_POST['username'] ?? '');
  $p = $_POST['password'] ?? '';
  $stmt = db()->prepare('SELECT id,username,password,role FROM users WHERE username=?');
  $stmt->execute([$u]);
  $row = $stmt->fetch();
  if ($row && password_verify($p, $row['password'])) {
    session_regenerate_id(true);
    $_SESSION['user'] = ['id'=>$row['id'],'username'=>$row['username'],'role'=>$row['role']];
    header('Location: /'); exit;
  }
  $error = '로그인 실패';
  include __DIR__.'/../src/views/login.php'; exit;
}
if ($path === '/logout') { session_destroy(); header('Location: /'); exit; }

$user = current_user();
?>
<!doctype html><html><head><meta charset="utf-8">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<title>APMVulnNote</title></head>
<body class="p-4">
  <nav class="mb-3 d-flex gap-3">
    <a href="/">Home</a>
    <a href="/health">Health</a>
    <?php if($user): ?>
      <span class="text-muted">Hi, <?=$user['username']?></span>
      <a href="/logout">Logout</a>
    <?php else: ?>
      <a href="/login">Login</a>
    <?php endif; ?>
  </nav>
  <h1>APMVulnNote</h1>
  <p>스캐폴딩 완료! <code>/health</code> 체크로 DB 연결 상태를 확인하세요.</p>
</body></html>