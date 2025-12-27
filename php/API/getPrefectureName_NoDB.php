<?php

require "returnJson.php";

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
        throw new Exception("都道府県番号は1~47の範囲で指定してください");
    }

    switch ($prefecture_no) {
    case '1':
      $prefecture_name = "北海道";
      break;
    case '2':
      $prefecture_name = "青森県";
      break;
    case '3':
      $prefecture_name = "岩手県";
      break;
    case '4':
      $prefecture_name = "宮城県";
      break;
    case '5':
      $prefecture_name = "秋田県";
      break;
    case '6':
      $prefecture_name = "山形県";
      break;
    case '7':
      $prefecture_name = "福島県";
      break;
    case '8':
      $prefecture_name = "茨城県";
      break;
    case '9':
      $prefecture_name = "栃木県";
      break;
    case '10':
      $prefecture_name = "群馬県";
      break;
    case '11':
      $prefecture_name = "埼玉県";
      break;
    case '12':
      $prefecture_name = "千葉県";
      break;
    case '13':
      $prefecture_name = "東京都";
      break;
    case '14':
      $prefecture_name = "神奈川県";
      break;
    case '15':
      $prefecture_name = "新潟県";
      break;
    case '16':
      $prefecture_name = "富山県";
      break;
    case '17':
      $prefecture_name = "石川県";
      break;
    case '18':
      $prefecture_name = "福井県";
      break;
    case '19':
      $prefecture_name = "山梨県";
      break;
    case '20':
      $prefecture_name = "長野県";
      break;
    case '21':
      $prefecture_name = "岐阜県";
      break;
    case '22':
      $prefecture_name = "静岡県";
      break;
    case '23':
      $prefecture_name = "愛知県";
      break;
    case '24':
      $prefecture_name = "三重県";
      break;
    case '25':
      $prefecture_name = "滋賀県";
      break;
    case '26':
      $prefecture_name = "京都府";
      break;
    case '27':
      $prefecture_name = "大阪府";
      break;
    case '28':
      $prefecture_name = "兵庫県";
      break;
    case '29':
      $prefecture_name = "奈良県";
      break;
    case '30':
      $prefecture_name = "和歌山県";
      break;
    case '31':
      $prefecture_name = "鳥取県";
      break;
    case '32':
      $prefecture_name = "島根県";
      break;
    case '33':
      $prefecture_name = "岡山県";
      break;
    case '34':
      $prefecture_name = "広島県";
      break;
    case '35':
      $prefecture_name = "山口県";
      break;
    case '36':
      $prefecture_name = "徳島県";
      break;
    case '37':
      $prefecture_name = "香川県";
      break;
    case '38':
      $prefecture_name = "愛媛県";
      break;
    case '39':
      $prefecture_name = "高知県";
      break;
    case '40':
      $prefecture_name = "福岡県";
      break;
    case '41':
      $prefecture_name = "佐賀県";
      break;
    case '42':
      $prefecture_name = "長崎県";
      break;
    case '43':
      $prefecture_name = "熊本県";
      break;
    case '44':
      $prefecture_name = "大分県";
      break;
    case '45':
      $prefecture_name = "宮崎県";
      break;
    case '46':
      $prefecture_name = "鹿児島県";
      break;
    case '47':
      $prefecture_name = "沖縄県";
      break;
    default:
      throw new Exception("都道府県番号は1~47の範囲で指定してください");
      break;
  }

    $result = [
        'result' => 'success',
        'prefectureNo' => $prefecture_no,
        'prefectureName' => $prefecture_name
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