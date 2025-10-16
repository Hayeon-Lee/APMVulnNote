<?php
require_once __DIR__.'/../src/lib/db.php';
require_once __DIR__.'/../src/lib/auth.php';
require_once __DIR__.'/../src/lib/util.php';
require_once __DIR__.'/../src/lib/logger.php';
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
// Posts list
if ($path === '/posts' && $_SERVER['REQUEST_METHOD']==='GET') {
  $stmt = db()->query("SELECT p.id, p.title, p.created_at, u.username
                       FROM posts p LEFT JOIN users u ON u.id = p.user_id
                       ORDER BY p.id DESC LIMIT 50");
  $posts = $stmt->fetchAll();
  app_log('INFO', 'posts_list');
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
  $title = trim($_POST['title'] ?? '');
  $body  = trim($_POST['body'] ?? '');
  if ($title === '' || strlen($title) > 200 || $body === '') {
    $error = '제목/본문을 확인하세요.'; render('posts/new', compact('error')); exit;
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
  $postId = (int)$m[1];
  $content = trim($_POST['content'] ?? '');
  if ($content === '' || strlen($content) > 2000) {
    header("Location: /posts/$postId"); exit;
  }
  $stmt = db()->prepare("INSERT INTO comments(post_id,user_id,content) VALUES (?,?,?)");
  $stmt->execute([$postId, current_user()['id'], $content]);
  app_log('INFO', 'comment_created', ['post'=>$postId,'user'=>current_user()['id']]);
  header("Location: /posts/$postId"); exit;
}

// Home
render('posts/list', ['posts' => db()->query(
  "SELECT p.id, p.title, p.created_at, u.username
   FROM posts p LEFT JOIN users u ON u.id = p.user_id
   ORDER BY p.id DESC LIMIT 10")->fetchAll()
]);

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