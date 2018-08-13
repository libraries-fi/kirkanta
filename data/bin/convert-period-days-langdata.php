<?php
/*
 * Convert string value to array of translations.
 */

$pdo = new PDO('pgsql:dbname=beta_kirkanta');
$periods = $pdo->query('SELECT a.id, a.days, a.translations, array_agg(b.langcode) langcodes FROM periods a INNER JOIN periods_data b ON a.id = b.entity_id GROUP BY a.id')->fetchAll();

$pdo->beginTransaction();
$smt = $pdo->prepare('UPDATE periods SET days = ? WHERE id = ?');

foreach ($periods as $period) {
  $days = json_decode($period['days']);
  $langcodes = explode(',', substr($period['langcodes'], 1, -1));

  foreach ($days as $i => $row) {
    if (!isset($row->translations)) {
      // Row already converted.
      continue;
    }

    if (!is_object($row->info)) {
      $row->info = (object)['fi' => $row->info];
    }

    foreach ($langcodes as $langcode) {
      if ($langcode != 'fi') {
        $row->info->{$langcode} = $row->translations->{$langcode}->info ?? null;

        if (!strlen($row->info->{$langcode})) {
          $row->info->{$langcode} = null;
        }
      }
    }

    unset($row->translations);
  }

  $smt->execute([json_encode($days), $period['id']]);
}

$pdo->commit();
