<?php

require "connectDB.php";
require "query/getPrefectureQuery.php";

// --- DB接続 ---
$pdo = connectDB();
if ($pdo === null) {
    exit();
}

$query = getPrefectureQuery();
$stmt = $pdo->prepare($query);
if ($stmt === false) {
    throw new RuntimeException('getPrefecture.php SQL prepare failed');
}
$stmt->execute();
if ($stmt === false) {
    throw new  RuntimeException('getPrefecture.php SQL execute failed');
}

echo json_encode($stmt->fetchAll());

?>