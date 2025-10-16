<?php
function esc($s) { return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function render($view, $vars = []) {
  extract($vars, EXTR_OVERWRITE);
  include __DIR__ . "/../views/partials/layout_top.php";
  include __DIR__ . "/../views/$view.php";
  include __DIR__ . "/../views/partials/layout_bottom.php";
}
