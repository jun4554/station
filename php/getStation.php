<?php

require "connectDB.php";
require "query/getStationQuery.php";

header('Content-Type: application/json; charset=utf-8');

// --- 入力取得 ---
$east  = (float)($_GET['east'] ?? 0);
$west  = (float)($_GET['west'] ?? 0);
$south = (float)($_GET['south'] ?? 0);
$north = (float)($_GET['north'] ?? 0);

$passengerFrom = (int)($_GET['passengerFrom'] ?? 0);
$passengerTo = (int)($_GET['passengerTo'] ?? 0);

$showNonPublic = ($_GET['showNonPublic'] ?? 'false') === 'true' ? 1 : 0;

$prefectureIdArray = $_GET['prefectureIdArray'] ?? "";
$prefectureIdArray = array_map(
    // 文字列で来るので配列に変換
    'intval',
    explode(',', $prefectureIdArray)
);
error_log(print_r(implode(',', $prefectureIdArray), true));

$lineIdArray = $_GET['lineIdArray'] ?? "";
$lineIdArray = array_map(
    // 文字列で来るので配列に変換
    'intval',
    explode(',', $lineIdArray)
);
error_log(print_r(implode(',', $lineIdArray), true));
error_log(print_r($prefectureIdArray, true));

// --- DB接続 ---
$pdo = connectDB();
if ($pdo === null) {
    exit();
}

$query = getStationQuery($prefectureIdArray, $lineIdArray);
$stmt = $pdo->prepare($query);
if ($stmt === false) {
    throw new RuntimeException('getStation.php SQL prepare failed');
}

// --- バインド ---
$stmt->bindValue(':south', $south);
$stmt->bindValue(':north', $north);
$stmt->bindValue(':west',  $west);
$stmt->bindValue(':east',  $east);
$stmt->bindValue(':passengerFrom', $passengerFrom, PDO::PARAM_INT);
$stmt->bindValue(':passengerTo',   $passengerTo,   PDO::PARAM_INT);
$stmt->bindValue(':showNonPublic', $showNonPublic, PDO::PARAM_INT);

if (!empty(array_filter($prefectureIdArray))) { // $prefectureIdArrayに指定がない場合、0が入っているので、その場合は除外
    foreach ($prefectureIdArray as $i => $id) {
        $stmt->bindValue(":pref{$i}", (int)$id, PDO::PARAM_INT);
    }
}

if (!empty(array_filter($lineIdArray))) { // $lineIdArrayに指定がない場合、0が入っているので、その場合は除外
    foreach ($lineIdArray as $i => $id) {
        $stmt->bindValue(":line{$i}", (int)$id, PDO::PARAM_INT);
    }
}

$stmt->execute();
if ($stmt === false) {
    throw new  RuntimeException('getStation.php SQL execute failed');
}
echo json_encode($stmt->fetchAll());

?>