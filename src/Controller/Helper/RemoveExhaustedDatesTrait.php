<?php

namespace SFW2\Routing\Controller\Helper;

trait RemoveExhaustedDatesTrait {
    protected function removeExhaustedDates() {
        $stmt =
            "DELETE FROM `sfw2_calendar` " .
            "WHERE `Day` IS NULL " .
            "AND ((`EndDate` IS NULL AND `StartDate` < NOW()) " .
            "OR (`EndDate` < NOW()))";

        $this->database->delete($stmt);
    }
}


