<?php

namespace SFW2\Routing\Controller\Helper;

trait GetDivisionTrait {
    function getDivisions() {
        $stmt =
            'SELECT `Id`, `Name` ' .
            'FROM `sfw2_division` ' .
            'ORDER BY `Position`';

        return $this->database->select($stmt);
    }
}
