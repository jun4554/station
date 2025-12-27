<?php

function getLinePrefixQuery() {

    return "
        SELECT 
            DISTINCT prefix 
        FROM genericTerm 
        WHERE prefix != '' ORDER BY id";
}

?>