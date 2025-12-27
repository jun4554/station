<?php

function getLineQuery() {

    return "
        SELECT 
            lineName.id AS id, 
            lineName.name AS name,
            genericTerm.id AS genericTermId,
            genericTerm.name AS genericTermName,
            genericTerm.prefix AS prefix
        FROM lineName
        LEFT JOIN genericTerm ON lineName.genericTerm_id =  genericTerm.id
        ORDER BY lineName.id";
}

?>