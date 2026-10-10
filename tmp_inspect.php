<?php

$pdo = new PDO('sqlite:'.__DIR__.'/database/database.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

foreach (['users', 'tournaments', 'rounds', 'groups', 'teams', 'matches'] as $t) {
    echo "== $t ==\n";
    foreach ($pdo->query("PRAGMA table_info($t)") as $c) {
        echo $c['name'].' ('.$c['type'].")\n";
    }
    echo "\n";
}

echo "== rounds t1 (assoc) ==\n";
foreach ($pdo->query('SELECT * FROM rounds WHERE tournament_id=1') as $r) {
    echo json_encode($r)."\n";
}
