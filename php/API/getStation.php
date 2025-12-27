<?php

require "returnJson.php";
require "../validation.php";
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

// 結果格納変数
$result = [];

try {

    // パラメータ検証
    if (empty($station_name)) {
        throw new Exception("パラメータ:station_nameが指定されていません");
    } else if (validation($station_name)) {
        throw new Exception("不正なリクエストです");
    }
    if (empty($pattern_match)) {
        throw new Exception("パラメータ:pattern_matchが指定されていません");
    } else if (!($pattern_match == 1 or $pattern_match == 2)) {
        throw new Exception("パラメータ:pattern_matchは1または2を指定してください");
    }

    // データベース接続
    $mysqli = connectDB();
    if ($mysqli == null) {
        throw new Exception("データベースの接続に失敗しました");
    }

    // クエリ生成
    $query = getStationQuery($station_name, $pattern_match);

    $select = $mysqli -> query($query);
    //クエリ失敗
    if(!$select) {
        throw new Exception($mysqli->error);
    }

    //レコード件数
    $row_count = $select->num_rows;
    if ($row_count == 0) {
        throw new Exception("該当する駅はありません");
    }

    //連想配列で取得
    $stack = array();
    while($row = $select->fetch_array(MYSQLI_ASSOC)){

        $sourceUrl = $row["sourceUrl"];
        if ($sourceUrl == "" && $row["passenger"] != "") {
             $sourceUrl = "https://ja.wikipedia.org/wiki/".$row["name"]."駅";
        }
        array_push($stack, 
            ['name' => $row["name"],
             'kanaName' => $row["kana_name"],
             'prefectureName' => $row["prefectureName"], 
             'lineName1' => $row["lineName"], 
             'lineName2' => $row["lineName2"], 
             'lineName3' => $row["lineName3"],
             'lineName4' => $row["lineName4"],
             'lineName5' => $row["lineName5"], 
             'lineName6' => $row["lineName6"], 
             'lineName7' => $row["lineName7"], 
             'lineName8' => $row["lineName8"], 
             'lineName9' => $row["lineName9"],
             'lat' => $row["lat"], 
             'lng' => $row["lng"],
             'passenger' => $row["passenger"],
             'passengerRemarks' => $row["remarks"], 
             'passengerYear' => $row["year"],
             'sourceUrl' => $sourceUrl 
            ]
        );
    }

    //結果セットを解放
    $select->free();

    // データベース切断
    $mysqli->close();

    $result = [
        'result' => 'success',
        'stationData' => $stack
    ];

} catch (Exception $e) {
    $result = [
        'result' => 'failure',
        'message' => $e->getMessage()
    ];
}

// JSONで結果を返す
returnJson($result);

?>