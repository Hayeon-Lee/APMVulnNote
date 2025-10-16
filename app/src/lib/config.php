<?php
function cfg(string $k, $default=null) {
  $v = getenv($k);
  return $v === false ? $default : $v;
}
function is_prod(): bool { return cfg('APP_ENV','local') === 'production'; }
function is_debug(): bool { return (int)cfg('APP_DEBUG',0) === 1; }