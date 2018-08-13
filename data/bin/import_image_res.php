<?php

$files = glob(__DIR__ . '/../../../web/files/images/small/*.*');
$pdo = new PDO('pgsql:dbname=beta_kirkanta');
$smt = $pdo->prepare('UPDATE pictures SET meta = coalesce(meta, \'{}\') || :sizes WHERE filename = :filename');
$dirs = ['small', 'medium', 'large', 'huge'];

foreach ($files as $path) {
  $res = [];

  foreach ($dirs as $dir) {
    $file = implode('/', [dirname(dirname($path)), $dir, basename($path)]);

    if (is_file($file) && ($size = getimagesize($file))) {
      $res[$dir] = sprintf('%dx%d', $size[0], $size[1]);
    }
  }

  if (!empty($res)) {
    printf("%s\n", $path);
    $json = json_encode(['resolution' => $res]);
    $smt->execute(['sizes' => $json, 'filename' => basename($path)]);
  }
}
