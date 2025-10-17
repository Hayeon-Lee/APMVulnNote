<?php
require_once __DIR__.'/../src/lib/db.php';
require_once __DIR__.'/../src/lib/auth.php';
require_once __DIR__.'/../src/lib/util.php';
require_once __DIR__.'/../src/lib/logger.php';
require_once __DIR__.'/../src/lib/config.php';
require_once __DIR__.'/../src/lib/validate.php';
require_once __DIR__.'/../src/lib/errors.php';
require_once __DIR__.'/../src/lib/upload.php';

install_error_handlers();
start_secure_session();

// 보안 헤더(최소 셋)
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: DENY');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// CSP: 우리가 쓰는 리소스만 허용 (Bootstrap CSS CDN 허용)
$bootstrap = "https://cdn.jsdelivr.net";
$csp = "default-src 'self'; style-src 'self' $bootstrap 'unsafe-inline'; img-src 'self' data:; script-src 'self';";
//header("Content-Security-Policy: $csp");

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
  $u = matches('아이디', $_POST['username'] ?? '', '/^[a-zA-Z0-9_\-\.]{1,32}$/');
  $p = $_POST['password'] ?? '';
  $stmt = db()->prepare('SELECT id,username,password,role FROM users WHERE username=?');
  $stmt->execute([$u]);
  $row = $stmt->fetch();
  if ($row && password_verify($p, $row['password'])) {
    $_SESSION['user'] = ['id'=>$row['id'],'username'=>$row['username'],'role'=>$row['role']];
    header('Location: /'); exit;
  }
  $error = '로그인 실패';
  include __DIR__.'/../src/views/login.php'; exit;
}
if ($path === '/logout') { session_destroy(); header('Location: /'); exit; }
// Posts list
if ($path === '/posts' && $_SERVER['REQUEST_METHOD']==='GET') {
  $q = $_GET['q'] ?? '';
  if ($q !== '') {
    $sql = "SELECT p.id, p.title, p.created_at, u.username
            FROM posts p LEFT JOIN users u ON u.id = p.user_id
            WHERE p.title LIKE '%$q%'
            ORDER BY p.id DESC LIMIT 50";
    $posts = db()->query($sql)->fetchAll();
  } else {
    $stmt = db()->query("SELECT p.id, p.title, p.created_at, u.username
                         FROM posts p LEFT JOIN users u ON u.id = p.user_id
                         ORDER BY p.id DESC LIMIT 50");
    $posts = $stmt->fetchAll();
  }
  app_log('DEBUG', 'posts_list_handler', ['q' => $q]); // 디버그용
  render('posts/list', compact('posts')); exit;
}

// New post (GET)
if ($path === '/posts/new' && $_SERVER['REQUEST_METHOD']==='GET') {
  require_login();
  render('posts/new'); exit;
}

// New post (POST)
if ($path === '/posts/new' && $_SERVER['REQUEST_METHOD']==='POST') {
  require_login();
  try {
    $title = str_between('제목', $_POST['title'] ?? '', 1, 200);
    $body  = str_between('본문', $_POST['body'] ?? '', 1, 8000);
  } catch (ValidationError $e) {
    $error = $e->getMessage(); render('posts/new', compact('error')); exit;
  }
  $stmt = db()->prepare("INSERT INTO posts(user_id,title,body) VALUES (?,?,?)");
  $stmt->execute([ current_user()['id'], $title, $body ]);
  $id = db()->lastInsertId();
  app_log('INFO', 'post_created', ['id'=>$id, 'user'=>current_user()['id']]);
  header("Location: /posts/$id"); exit;
}

// Show post
if (preg_match('#^/posts/(\d+)$#', $path, $m) && $_SERVER['REQUEST_METHOD']==='GET') {
  $id = (int)$m[1];
  $stmt = db()->prepare("SELECT p.*, u.username FROM posts p LEFT JOIN users u ON u.id=p.user_id WHERE p.id=?");
  $stmt->execute([$id]); $post = $stmt->fetch();
  if (!$post) { http_response_code(404); echo "Not Found"; exit; }

  $cs = db()->prepare("SELECT c.*, u.username FROM comments c LEFT JOIN users u ON u.id=c.user_id WHERE c.post_id=? ORDER BY c.id ASC");
  $cs->execute([$id]); $comments = $cs->fetchAll();

  app_log('INFO', 'post_show', ['id'=>$id]);
  render('posts/show', compact('post','comments')); exit;
}

// Create comment
if (preg_match('#^/posts/(\d+)/comments$#', $path, $m) && $_SERVER['REQUEST_METHOD']==='POST') {
  require_login();
  try { $content = str_between('댓글', $_POST['content'] ?? '', 1, 2000); }
  catch (ValidationError $e) { header("Location: /posts/{$m[1]}"); exit; }
  $stmt = db()->prepare("INSERT INTO comments(post_id,user_id,content) VALUES (?,?,?)");
  $stmt->execute([(int)$m[1], current_user()['id'], $content]);
  app_log('INFO', 'comment_created', ['post'=>(int)$m[1],'user'=>current_user()['id']]);
  header("Location: /posts/{$m[1]}"); exit;
}

// 업로드 폼
if ($path === '/upload' && $_SERVER['REQUEST_METHOD']==='GET') {
  require_login();
  render('upload/form'); exit;
}

// 업로드 처리
if ($path === '/upload' && $_SERVER['REQUEST_METHOD']==='POST') {
  require_login();
  try {
    [$fname, $mime] = save_uploaded_file($_FILES['file'] ?? []);
    app_log('INFO','file_uploaded',['file'=>$fname,'mime'=>$mime,'user'=>current_user()['id']]);
    header("Location: /download/$fname");
  } catch (Throwable $e) {
    $error = $e->getMessage(); render('upload/form', compact('error')); 
  }
  exit;
}

// 다운로드(웹루트 밖에서 안전하게 서빙)
if (preg_match('#^/download/([\w\.\-]+)$#', $path, $m)) {
  $fname = $m[1];
  $full = uploads_path()."/$fname";
  if (!is_file($full)) { http_response_code(404); echo "Not Found"; exit; }
  $fi = new finfo(FILEINFO_MIME_TYPE); $mime = $fi->file($full) ?: 'application/octet-stream';
  header("Content-Type: $mime");
  header('Content-Disposition: inline; filename="'.basename($fname).'"');
  readfile($full); exit;
}

// /out?url=...
if ($path === '/out' && isset($_GET['url'])) {
  app_log('INFO', 'out_redirect', ['url'=>$_GET['url']]);
  header('Location: ' . $_GET['url']);  
  exit;
}

// Home
if ($path === '/' || $path === '') {
  render('posts/list', ['posts' => db()->query(
    "SELECT p.id, p.title, p.created_at, u.username
     FROM posts p LEFT JOIN users u ON u.id = p.user_id
     ORDER BY p.id DESC LIMIT 10"
  )->fetchAll()]);
  exit;
}

http_response_code(404);
include __DIR__ . '/../src/views/partials/error_404.php';
exit;