<?php

require "returnJson.php";
require "../connectDB.php";
require "query/getStationQuery.php";

/*
 * 駅データをjsonで返す
 *
 * @param string station_name 駅名（漢字、かなどちらも可能。曖昧可能）
 * @param string pattern_match 検索条件（1:完全一致、2:曖昧検索）
 * @return array
 *          string result   success, failure
 *          成功時
 *              array stationData
 *          失敗時
 *              string message  エラーメッセージ
 */

// パラメータの取得
$station_name = $_REQUEST['station_name'];
$pattern_match = $_REQUEST['pattern_match'];

try {

    // パラメータ検証
    if (empty($station_name)) {
        throw new Exception("パラメータ:station_nameが指定されていません");
    }
    if (empty($pattern_match)) {
        throw new Exception("パラメータ:pattern_matchが指定されていません");
    } else if (!($pattern_match == 1 or $pattern_match == 2)) {
        throw new Exception("パラメータ:pattern_matchは1または2を指定してください");
    }

    // --- DB接続 ---
    $pdo = connectDB();
    if ($pdo === null) {
        exit();
    }

    // クエリ生成
    $stmt = $pdo->prepare(getStationQuery($pattern_match));
    if ($stmt === false) {
        throw new RuntimeException('getStationName.php SQL prepare failed');
    }
    if ($pattern_match == 2) {
        $station_name = '%' . $station_name . '%';
    }
    $stmt->bindValue(':station_name', $station_name, PDO::PARAM_STR);
    
    // クエリ実行
    $stmt->execute();
    if ($stmt === false) {
        throw new  RuntimeException('getStationName.php SQL execute failed');
    }

    echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    $result = [
        'result' => 'failure',
        'message' => $e->getMessage()
    ];
    // JSONで結果を返す
    returnJson($result);
}

?>