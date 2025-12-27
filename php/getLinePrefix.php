<?php

require "connectDB.php";
require "query/getLinePrefixQuery.php";

// --- DB接続 ---
$pdo = connectDB();
if ($pdo === null) {
    exit();
}

$query = getLinePrefixQuery();
$stmt = $pdo->prepare($query);
if ($stmt === false) {
    throw new RuntimeException('getLinePrefix.php SQL prepare failed');
}
$stmt->execute();
if ($stmt === false) {
    throw new  RuntimeException('getLinePrefix.php SQL execute failed');
}

echo json_encode($stmt->fetchAll());

?>