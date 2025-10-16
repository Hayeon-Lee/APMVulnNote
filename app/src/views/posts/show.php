<article class="mb-4">
  <h1><?= esc($post['title']) ?></h1>
  <div class="text-muted small mb-2">
    by <?= esc($post['username']) ?> • <?= esc($post['created_at']) ?>
  </div>
  <pre class="p-3 bg-light border rounded"><?= esc($post['body']) ?></pre>
</article>

<section class="mt-4">
  <h2 class="h5">Comments (<?= count($comments) ?>)</h2>
  <ul class="list-group mb-3">
    <?php foreach ($comments as $c): ?>
      <li class="list-group-item">
        <div class="small text-muted mb-1">
          <?= esc($c['username']) ?> • <?= esc($c['created_at']) ?>
        </div>
        <div><?= nl2br(esc($c['content'])) ?></div>
      </li>
    <?php endforeach; ?>
  </ul>

  <?php if(current_user()): ?>
    <form method="post" action="/posts/<?= (int)$post['id'] ?>/comments" class="vstack gap-2">
      <textarea class="form-control" name="content" rows="3" maxlength="2000" placeholder="Write a comment..." required></textarea>
      <button class="btn btn-secondary" type="submit">Add Comment</button>
    </form>
  <?php else: ?>
    <div class="alert alert-info">로그인 후 댓글을 작성할 수 있습니다.</div>
  <?php endif; ?>
</section>