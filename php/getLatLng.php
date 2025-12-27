<?php

require "connectDB.php";
require "query/getLatLngQuery.php";

// --- DB接続 ---
$pdo = connectDB();
if ($pdo === null) {
    exit();
}

$searchText = $_GET['searchText'] ?? '';

$stmt = $pdo->prepare(getLatLngQuery());
if ($stmt === false) {
    throw new RuntimeException('getLatLng.php SQL prepare failed');
}

$stmt->bindValue(':search', '%' . $searchText . '%', PDO::PARAM_STR);
$stmt->execute();
if ($stmt === false) {
    throw new RuntimeException('getLatLng.php SQL execute failed');
}

echo json_encode($stmt->fetchAll());

?>