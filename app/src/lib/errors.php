<?php
function install_error_handlers() {
  set_exception_handler(function(Throwable $e) {
    app_log('ERROR', 'uncaught_exception', ['msg'=>$e->getMessage(), 'file'=>$e->getFile(), 'line'=>$e->getLine()]);
    if (is_debug()) {
      http_response_code(500);
      echo "<pre>".htmlspecialchars($e, ENT_QUOTES)."</pre>";
    } else {
      http_response_code(500);
      include __DIR__ . '/../views/partials/error_500.php';
    }
  });

  set_error_handler(function($severity, $message, $file, $line) {
    app_log('ERROR', 'php_error', compact('severity','message','file','line'));
    if (is_debug()) return false; // 기본 에러 핸들링도 표시
    return true; // 프로덕션에서는 숨김
  });

  register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
      app_log('ERROR', 'fatal_shutdown', $err);
    }
  });
}