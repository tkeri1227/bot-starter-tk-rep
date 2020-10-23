<?php

// Composerでインストールしたライブラリを一括読み込み
require_once __DIR__ . '/vendor/autoload.php';
$inputString = file_get_contents('php://input');
error_log($inputString);

?>
