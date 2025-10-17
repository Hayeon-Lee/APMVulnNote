<?php
function uploads_path(){ return '/var/www/html/public/uploads'; }

// 허용 확장자 / MIME
function allow_exts(){ return ['png','jpg','jpeg','gif','webp','txt','pdf','php']; } 
function allow_mimes(){
  return ['image/png','image/jpeg','image/gif','image/webp','text/plain','application/pdf','application/x-php','text/x-php'];
}

function normalize_mime(string $m): string {
  $m = strtolower(trim($m));
  // 흔한 별칭 정규화
  if ($m === 'image/x-png')   return 'image/png';
  if ($m === 'image/pjpeg')   return 'image/jpeg';
  if (strpos($m, ';') !== false) {     // 'text/plain; charset=utf-8' → 'text/plain'
    $m = explode(';', $m, 2)[0];
  }
  return $m;
}

function safe_basename($name){
  $name = preg_replace('/[^\w\.\-]+/u','_', $name);
  return substr($name, 0, 120);
}

function guess_mime_by_ext(string $ext): string {
  return [
    'png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg',
    'gif'=>'image/gif','webp'=>'image/webp','txt'=>'text/plain','pdf'=>'application/pdf'
  ][$ext] ?? 'application/octet-stream';
}

function save_uploaded_file(array $f){
  if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new RuntimeException('업로드 오류');

  $ext  = strtolower(pathinfo($f['name'] ?? '', PATHINFO_EXTENSION));
  
  // finfo로 실제 MIME 확인
  $fi   = new finfo(FILEINFO_MIME_TYPE);
  $mime = $fi->file($f['tmp_name'] ?? '') ?: 'application/octet-stream';
  $mime = normalize_mime($mime);

  // 만약 octet-stream이면 (일부 환경) 확장자 기반으로 보수적 폴백
  if ($mime === 'application/octet-stream') {
    $mime = guess_mime_by_ext($ext);
  }

  if (!in_array($mime, allow_mimes(), true)) {
    throw new RuntimeException('MIME 불일치');
  }

  $id = bin2hex(random_bytes(8));
  $base = safe_basename(pathinfo($f['name'], PATHINFO_FILENAME));
  $fname = "{$id}_{$base}.{$ext}";
  $dest = uploads_path()."/$fname";
  if (!@move_uploaded_file($f['tmp_name'], $dest)) throw new RuntimeException('저장 실패');

  return [$fname, $mime];
}