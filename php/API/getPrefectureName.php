<?php

require "returnJson.php";
require "../connectDB.php";
require "query/getPrefectureNameQuery.php";

/*
 * 都道府県名をjsonで返す
 *
 * @param string prefecture_no  1~47のいずれか
 * @return array
 *          string result   success, failure
 *          成功時
 *              string prefectureNo
 *              string prefectureName
 *          失敗時
 *              string message  エラーメッセージ
 */

// パラメータの取得
$prefecture_no = $_REQUEST['prefecture_no'];

// 結果格納変数
$result = [];

try {

    // パラメータ検証
    if (empty($prefecture_no)) {
        throw new Exception("都道府県番号が指定されていません");
    } elseif (!preg_match('/^[1-9]$|^1[0-9]$|^2[0-9]$|^3[0-9]$|^4[0-7]$/', $prefecture_no)) {
        throw new Exception("不正な値が渡されました。都道府県番号は1~47の範囲で指定してください");
    }

    // データベース接続
    $mysqli = connectDB();
    if ($mysqli == null) {
        throw new Exception("データベースの接続に失敗しました");
    }

    // クエリ生成
    $query = getPrefectureNameQuery($prefecture_no);

    $select = $mysqli -> query($query);
    //クエリ失敗
    if(!$select) {
        throw new Exception($mysqli->error);
    }

    $row = $select->fetch_array(MYSQLI_ASSOC);

    $result = [
        'result' => 'success',
        'prefectureNo' => $prefecture_no,
        'prefectureName' => $row["name"]
    ];

    // データベース切断
    $mysqli->close();

} catch (Exception $e) {
    $result = [
        'result' => 'failure',
        'message' => $e->getMessage()
    ];
}

// JSONで結果を返す
returnJson($result);

?>