<?php
/*
 +----------------------------------------------------------------------+
 | CiviCRM version 1.0                                                  |
 +----------------------------------------------------------------------+
 | Copyright (c) 2005 Donald A. Lobo                                    |
 +----------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                      |
 |                                                                      |
 | CiviCRM is free software; you can redistribute it and/or modify it   |
 | under the terms of the Affero General Public License Version 1,      |
 | March 2002.                                                          |
 |                                                                      |
 | CiviCRM is distributed in the hope that it will be useful, but       |
 | WITHOUT ANY WARRANTY; without even the implied warranty of           |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                 |
 | See the Affero General Public License for more details at            |
 | http://www.affero.org/oagpl.html                                     |
 |                                                                      |
 | A copy of the Affero General Public License has been been            |
 | distributed along with this program (affero_gpl.txt)                 |
 +----------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo 01/15/2005
 * $Id$
 *
 */

//require_once 'CRM/Core/Error.php'; 
//require_once 'CRM/Core/DAO.php'; 
require_once 'CRM/Core/PseudoConstant.php'; 

class CRM_Contact_Server_StateCountryServer  
{
    
    function getState($fragment='', $countryId = 0) 
    {
        $fraglen = strlen($fragment);
        
        if (!$countryId) { 
            $states = CRM_Core_PseudoConstant::stateProvince();
        } else {
          
            $queryString = "SELECT civicrm_state_province.id as civicrm_state_province_id  
                            FROM civicrm_country , civicrm_state_province 
                            WHERE civicrm_state_province.country_id = civicrm_country.id
                              AND civicrm_country.id = ".$countryId."
                              AND civicrm_state_province.name ='".$fragment."'";  

            $DAOobj =& new CRM_Core_DAO();
            
            $DAOobj->query($queryString); 
            
            while ($DAOobj->fetch()) {
              return $DAOobj->civicrm_state_province_id;
            }
        }

        for ( $i = $fraglen; $i > 0; $i-- ) {
            $matches = preg_grep('/^'.substr($fragment,0,$i).'/i', $states);
            
            if ( count($matches) > 0 ) {
                $id = key($matches);
                $value = current($matches);
                $showState[$id] = $value;
                return $showState;
            }
        }
        
        return '';
    }

    function getCountry($stateProvince) {
        unset($matches);
        $queryString = "SELECT civicrm_country.id as civicrm_country_id, civicrm_country.name as civicrm_country_name 
                        FROM civicrm_country , civicrm_state_province 
                        WHERE civicrm_state_province.country_id = civicrm_country.id
                          AND civicrm_state_province.name ='".$stateProvince."'";  

        $DAOobj =& new CRM_Core_DAO();
        
        $DAOobj->query($queryString); 

        while ($DAOobj->fetch()) { 
            $matches[$DAOobj->civicrm_country_id] = $DAOobj->civicrm_country_name;
        }
        return $matches;
    }

}
?>
