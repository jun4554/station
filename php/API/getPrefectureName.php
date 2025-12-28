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

try {

    // パラメータ検証
    if (empty($prefecture_no)) {
        throw new Exception("都道府県番号が指定されていません");
    } elseif (!preg_match('/^[1-9]$|^1[0-9]$|^2[0-9]$|^3[0-9]$|^4[0-7]$/', $prefecture_no)) {
        throw new Exception("不正な値が渡されました。都道府県番号は1~47の範囲で指定してください");
    }

    // --- DB接続 ---
    $pdo = connectDB();
    if ($pdo === null) {
        exit();
    }

    $stmt = $pdo->prepare(getPrefectureNameQuery());
    if ($stmt === false) {
        throw new RuntimeException('getPrefectureName.php SQL prepare failed');
    }

    $stmt->bindValue(':prefecture_id', $prefecture_no , PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt === false) {
        throw new  RuntimeException('getPrefectureName.php SQL execute failed');
    }

    echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    $result = [
        'result' => 'failure',
        'message' => $e->getMessage()
    ];
    returnJson($result);
}

?>