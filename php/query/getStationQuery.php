<?php

function getStationQuery($prefectureIdArray, $lineIdArray) {

    /*
    $query = "SELECT 
                line_id,
                (SELECT lineName.name FROM line LEFT JOIN lineName ON line.line_id = lineName.id WHERE station.prefecture_id = line.prefecture_id AND station.line_id = line.line_id) AS lineName,
                (SELECT lineName.name FROM line LEFT JOIN lineName ON line.line_id = lineName.id WHERE station.prefecture_id = line.prefecture_id AND station.line_id2 = line.line_id) AS lineName2,
                (SELECT lineName.name FROM line LEFT JOIN lineName ON line.line_id = lineName.id WHERE station.prefecture_id = line.prefecture_id AND station.line_id3 = line.line_id) AS lineName3,
                (SELECT lineName.name FROM line LEFT JOIN lineName ON line.line_id = lineName.id WHERE station.prefecture_id = line.prefecture_id AND station.line_id4 = line.line_id) AS lineName4,
                (SELECT lineName.name FROM line LEFT JOIN lineName ON line.line_id = lineName.id WHERE station.prefecture_id = line.prefecture_id AND station.line_id5 = line.line_id) AS lineName5,
                (SELECT lineName.name FROM line LEFT JOIN lineName ON line.line_id = lineName.id WHERE station.prefecture_id = line.prefecture_id AND station.line_id6 = line.line_id) AS lineName6,
                (SELECT lineName.name FROM line LEFT JOIN lineName ON line.line_id = lineName.id WHERE station.prefecture_id = line.prefecture_id AND station.line_id7 = line.line_id) AS lineName7,
                (SELECT lineName.name FROM line LEFT JOIN lineName ON line.line_id = lineName.id WHERE station.prefecture_id = line.prefecture_id AND station.line_id8 = line.line_id) AS lineName8,
                (SELECT lineName.name FROM line LEFT JOIN lineName ON line.line_id = lineName.id WHERE station.prefecture_id = line.prefecture_id AND station.line_id9 = line.line_id) AS lineName9,
                station.name AS stationName,
                kana_name AS stationKanaName,
                passenger,
                year,
                remarks,
                lat,
                lng,
                sourceUrl,
                showFlg
                FROM station
                WHERE (lat BETWEEN $south AND $north)
                AND (lng BETWEEN  $west AND $east)
                AND ((passenger BETWEEN $passengerFrom AND $passengerTo)";

                if ($showNonPublic == "true") {
                    $query .= "OR passenger = -1)";
                } else {
                    $query .= ")";
                }
                // AND year = '2019'";
    */
    $query = "
        SELECT
            station.line_id,
            (SELECT lineName.name FROM line LEFT JOIN lineName ON line.line_id = lineName.id WHERE station.prefecture_id = line.prefecture_id AND station.line_id = line.line_id) AS lineName,
            (SELECT lineName.name FROM line LEFT JOIN lineName ON line.line_id = lineName.id WHERE station.prefecture_id = line.prefecture_id AND station.line_id2 = line.line_id) AS lineName2,
            (SELECT lineName.name FROM line LEFT JOIN lineName ON line.line_id = lineName.id WHERE station.prefecture_id = line.prefecture_id AND station.line_id3 = line.line_id) AS lineName3,
            (SELECT lineName.name FROM line LEFT JOIN lineName ON line.line_id = lineName.id WHERE station.prefecture_id = line.prefecture_id AND station.line_id4 = line.line_id) AS lineName4,
            (SELECT lineName.name FROM line LEFT JOIN lineName ON line.line_id = lineName.id WHERE station.prefecture_id = line.prefecture_id AND station.line_id5 = line.line_id) AS lineName5,
            (SELECT lineName.name FROM line LEFT JOIN lineName ON line.line_id = lineName.id WHERE station.prefecture_id = line.prefecture_id AND station.line_id6 = line.line_id) AS lineName6,
            (SELECT lineName.name FROM line LEFT JOIN lineName ON line.line_id = lineName.id WHERE station.prefecture_id = line.prefecture_id AND station.line_id7 = line.line_id) AS lineName7,
            (SELECT lineName.name FROM line LEFT JOIN lineName ON line.line_id = lineName.id WHERE station.prefecture_id = line.prefecture_id AND station.line_id8 = line.line_id) AS lineName8,
            (SELECT lineName.name FROM line LEFT JOIN lineName ON line.line_id = lineName.id WHERE station.prefecture_id = line.prefecture_id AND station.line_id9 = line.line_id) AS lineName9,
            station.name AS stationName,
            station.kana_name AS stationKanaName,
            station.passenger,
            station.year,
            station.remarks,
            station.lat,
            station.lng,
            station.sourceUrl,
            station.showFlg
        FROM station
        WHERE lat BETWEEN :south AND :north
        AND lng BETWEEN :west AND :east
        AND (passenger BETWEEN :passengerFrom AND :passengerTo OR (:showNonPublic = 1 AND passenger = -1))
        ";
    /*
    if (!empty($prefectureIdArray)) {
         $query .= "AND prefecture_id IN ($prefectureIdArray)";
    }
    
    if ($prefectureIdArray) {
        $query .= " AND prefecture_id IN (" .
            implode(',', array_fill(0, count($prefectureIdArray), '?')) . ")";
    }
    */
    if (!empty(array_filter($prefectureIdArray))) { // $prefectureIdArrayに指定がない場合、0が入っているので、その場合は除外
        $placeholders = [];
        foreach ($prefectureIdArray as $i => $id) {
            $placeholders[] = ":pref{$i}";
        }
        $query .= " AND prefecture_id IN (" . implode(',', $placeholders) . ")";
    }

    /*
    if (!empty($lineIdArray)) {
        $query .= "AND (line_id IN ($lineIdArray)
                         OR line_id2 IN ($lineIdArray) 
                         OR line_id3 IN ($lineIdArray) 
                         OR line_id4 IN ($lineIdArray) 
                         OR line_id5 IN ($lineIdArray) 
                         OR line_id6 IN ($lineIdArray)
                         OR line_id7 IN ($lineIdArray) 
                         OR line_id8 IN ($lineIdArray)
                         OR line_id9 IN ($lineIdArray)) ";
    }
    */

    if (!empty(array_filter($lineIdArray))) { // $lineIdArrayに指定がない場合、0が入っているので、その場合は除外
        $placeholders = [];
        foreach ($lineIdArray as $i => $id) {
            $placeholders[] = ":line{$i}";
        }
        $placeholders = implode(',', $placeholders);

        $query .= "
        AND (
            line_id IN ($placeholders)
            OR line_id2 IN ($placeholders)
            OR line_id3 IN ($placeholders)
            OR line_id4 IN ($placeholders)
            OR line_id5 IN ($placeholders)
            OR line_id6 IN ($placeholders)
            OR line_id7 IN ($placeholders)
            OR line_id8 IN ($placeholders)
            OR line_id9 IN ($placeholders)
        )";
    }

    $query .= " ORDER BY line_id, station_id";

error_log(print_r($query, true));

    return $query;
}

?>