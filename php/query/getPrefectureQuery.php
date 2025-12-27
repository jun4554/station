<?php

function getPrefectureQuery() {

    $query = 
        "SELECT
            prefecture_id, 
            prefecture.name,
            region.name AS region_name
        FROM prefecture
        LEFT JOIN region ON prefecture.region_id =  region.id
        ORDER BY prefecture_id";

    return $query;
}

?>