<!doctype html><html><head><meta charset="utf-8">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<title>APMVulnNote</title></head>
<body class="container py-4">
<nav class="mb-3 d-flex gap-3">
  <a href="/">Home</a>
  <a href="/posts">Posts</a>
  <a href="/health">Health</a>
  <?php $u = current_user(); if ($u): ?>
    <span class="text-muted">Hi, <?=esc($u['username'])?></span>
    <a href="/logout">Logout</a>
  <?php else: ?>
    <a href="/login">Login</a>
  <?php endif; ?>
</nav>
<div>