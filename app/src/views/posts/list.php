<h1 class="mb-3">Posts</h1>
<div class="mb-3">
  <?php if (current_user()): ?>
    <a class="btn btn-primary" href="/posts/new">New Post</a>
  <?php endif; ?>
</div>
<table class="table table-striped">
  <thead><tr><th>ID</th><th>Title</th><th>Author</th><th>Created</th></tr></thead>
  <tbody>
  <?php foreach ($posts as $p): ?>
    <tr>
      <td><?= (int)$p['id'] ?></td>
      <td><a href="/posts/<?= (int)$p['id'] ?>"><?= esc($p['title']) ?></a></td>
      <td><?= esc($p['username']) ?></td>
      <td><?= esc($p['created_at']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>