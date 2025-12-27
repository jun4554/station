<?php

require "connectDB.php";
require "query/getLineQuery.php";

// --- DB接続 ---
$pdo = connectDB();
if ($pdo === null) {
    exit();
}

$query = getLineQuery();
$stmt = $pdo->prepare($query);
if ($stmt === false) {
    throw new RuntimeException('getLine.php SQL prepare failed');
}
$stmt->execute();
if ($stmt === false) {
    throw new  RuntimeException('getLine.php SQL execute failed');
}

echo json_encode($stmt->fetchAll());

?>