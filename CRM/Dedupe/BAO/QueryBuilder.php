<?php

class CRM_Dedupe_BAO_QueryBuilder {

    static function record($rg) {
        $queries = array('SELECT 0 id1, 0 weight LIMIT 0');
        $rg->noRules = true;
    }

    static function internal($rg) {
        $queries = array('SELECT 0 id1, 0 id2, 0 weight LIMIT 0');
        $rg->noRules = true;
    }

    static function internalFilters($rg) {
        // Add a contact id filter for dedupe by group requests and add logic
        // to remove duplicate results with opposing orders, i.e. 1,2 and 2,1
        if( !empty($o->contactIds) ) {
            $cids = implode(',',$o->contactIds);
            return "(contact1.id IN($cids) AND ( contact2.id NOT IN($cids) OR (contact2.id IN($cids) AND contact1.id < contact2.id) ))";
        } else {
            return "(contact1.id < contact2.id)";
        }
    }
};

?>
