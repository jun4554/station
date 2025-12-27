<?php

/*
 * 結果をjsonで返却する
 *
 * @param  string result 返却値
 * @return   string jsonにエンコードされた返却値
 */
function returnJson($result){
    header('Content-Type: application/json; charset=UTF-8');
    echo  json_encode($result, JSON_UNESCAPED_UNICODE);
    exit(0);
}

?>