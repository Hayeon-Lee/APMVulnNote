<h1 class="mb-3">New Post</h1>
<?php if(!empty($error)): ?><div class="alert alert-danger"><?=esc($error)?></div><?php endif; ?>
<form method="post" action="/posts/new" class="vstack gap-3" style="max-width:640px">
  <input class="form-control" name="title" maxlength="200" placeholder="Title" required>
  <textarea class="form-control" name="body" rows="8" placeholder="Body" required></textarea>
  <button class="btn btn-primary" type="submit">Create</button>
</form>