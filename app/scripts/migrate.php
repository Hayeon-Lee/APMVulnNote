<?php
require_once __DIR__.'/../src/lib/db.php';
$sql = file_get_contents(__DIR__.'/../migrations/001_init.sql');
db()->exec($sql);
echo "Migrated\n";
