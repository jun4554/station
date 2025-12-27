<?php

function getLatLngQuery() {

    return "
        SELECT 
            station.name,
            station.kana_name,
            (
                SELECT p.name
                FROM prefecture p
                WHERE p.prefecture_id = station.prefecture_id
            ) AS prefectureName,
            (
                SELECT ln.name
                FROM lineName ln
                WHERE station.line_id = ln.id
            ) AS lineName,
            (
                SELECT ln.name
                FROM lineName ln
                WHERE station.line_id2 = ln.id
            ) AS lineName2,
            station.lat,
            station.lng
        FROM station
        WHERE (
                station.name LIKE :search
                OR station.kana_name LIKE :search
              )
          AND station.showFlg = 1
        ORDER BY station.kana_name, station.prefecture_id
    ";
}

?>