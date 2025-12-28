<?php

function getStationQuery($pattern_match) {

    $query = "SELECT 
                name,
                kana_name,
                (SELECT name FROM prefecture WHERE prefecture.prefecture_id = station.prefecture_id) AS prefectureName,
                (SELECT name FROM lineName WHERE station.line_id = lineName.id) AS lineName,
                (SELECT name FROM lineName WHERE station.line_id2 = lineName.id) AS lineName2,
                (SELECT name FROM lineName WHERE station.line_id3 = lineName.id) AS lineName3,
                (SELECT name FROM lineName WHERE station.line_id4 = lineName.id) AS lineName4,
                (SELECT name FROM lineName WHERE station.line_id5 = lineName.id) AS lineName5,
                (SELECT name FROM lineName WHERE station.line_id6 = lineName.id) AS lineName6,
                (SELECT name FROM lineName WHERE station.line_id7 = lineName.id) AS lineName7,
                (SELECT name FROM lineName WHERE station.line_id8 = lineName.id) AS lineName8,
                (SELECT name FROM lineName WHERE station.line_id9 = lineName.id) AS lineName9,
                lat,
                lng,
                CASE
                    WHEN passenger = -1 then ''
                    ELSE passenger
                END AS passenger,
                remarks,
                year,
                sourceUrl
                FROM station";

    if($pattern_match == 1){
        $query .= " WHERE (name = :station_name OR kana_name = :station_name)";
    }elseif($pattern_match == 2){
        $query .= " WHERE (name LIKE :station_name OR kana_name LIKE :station_name)";
    }
    $query .= " AND showFlg = 1 ORDER BY kana_name, prefecture_id";

    return $query;
}

?>