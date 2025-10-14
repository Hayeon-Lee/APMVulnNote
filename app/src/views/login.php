<!doctype html><html><head><meta charset="utf-8">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<title>Login</title></head><body class="container py-5">
  <h1 class="mb-4">로그인</h1>
  <?php if (!empty($error)): ?><div class="alert alert-danger"><?=$error?></div><?php endif; ?>
  <form method="post" action="/login" class="vstack gap-3" style="max-width:360px">
    <input class="form-control" name="username" placeholder="username" required>
    <input class="form-control" type="password" name="password" placeholder="password" required>
    <button class="btn btn-primary" type="submit">Sign in</button>
  </form>
</body></html>
