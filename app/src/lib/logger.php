<?php
function app_log($level, $msg, array $ctx = []) {
  $dir = '/var/log/app';
  if (!is_dir($dir)) @mkdir($dir, 0775, true);
  $line = sprintf(
    "[%s] %s %s %s %s %s\n",
    date('c'), $level, $_SERVER['REQUEST_METHOD'] ?? '-',
    $_SERVER['REQUEST_URI'] ?? '-', $msg, json_encode($ctx, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)
  );
  error_log($line, 3, "$dir/app.log");
}
