<?php

require_once 'CRM/Dedupe/BAO/QueryBuilder.php';

// TODO: How to handle NULL values/records?
class CRM_Dedupe_BAO_QueryBuilder_IndividualStrict extends CRM_Dedupe_BAO_QueryBuilder {

    static function record($rg) {
        require_once 'CRM/Core/DAO.php';
        require_once 'CRM/Utils/Array.php';
        var_dump($rg); exit();
        $civicrm_email = CRM_Utils_Array::value('civicrm_email', $rg->params, array());

        $params = array(
              1 => array(CRM_Utils_Array::value('email',$civicrm_email,''), 'String')
          );

        return array(
            "civicrm_contact.{$rg->name}.{$rg->threshold}" => CRM_Core_DAO::composeQuery("
                SELECT contact.id as id1, {$rg->threshold} as weight
                FROM civicrm_contact as contact
                  JOIN civicrm_email as email ON email.contact_id=contact.id
                WHERE contact_type = 'Individual'
                  AND email = %1", $params, true)
        );
    }

    static function internal($rg) {
        $query = "
            SELECT contact1.id as id1, contact2.id as id2, {$rg->threshold} as weight
            FROM civicrm_contact as contact1
              JOIN civicrm_email as email1 ON email1.contact_id=contact1.id
              JOIN civicrm_contact as contact2 ON
                contact1.id = contact2.id
              JOIN civicrm_email as email2 ON
                email2.contact_id=contact2.id AND
                email1.email=email2.email
            WHERE contact1.contact_type = 'Individual'
              AND ".self::internalFilters($rg);
        return array("civicrm_contact.{$rg->name}.{$rg->threshold}" => $query);
    }
};

?>
