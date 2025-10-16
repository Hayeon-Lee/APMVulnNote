<h1 class="mb-3">파일 업로드</h1>
<?php if(!empty($error)): ?><div class="alert alert-danger"><?=esc($error)?></div><?php endif; ?>
<form method="post" action="/upload" enctype="multipart/form-data" class="vstack gap-3" style="max-width:480px">
  <input type="file" name="file" class="form-control" required>
  <p class="text-muted small">허용: png, jpg, jpeg, gif, webp, txt, pdf</p>
  <button class="btn btn-primary" type="submit">Upload</button>
</form>