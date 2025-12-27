<?php

function getPrefectureNameQuery($prefecture_id) {

    $query = "SELECT name FROM prefecture
                     WHERE prefecture_id = $prefecture_id";

    return $query;
}

?>