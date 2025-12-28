<?php

function getPrefectureNameQuery() {

    return "
        SELECT 
            name AS prefecture_name
        FROM prefecture
        WHERE prefecture_id = :prefecture_id
    ";
}

?>