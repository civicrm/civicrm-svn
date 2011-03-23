<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
 +--------------------------------------------------------------------+
 | Copyright Tech To The People (c) 2010                              |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 * File for the CiviCRM APIv3 Petition Signatures functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Contribute
 */

/**
 * Include utility functions
 */
require_once 'api/v3/utils.php';
require_once 'CRM/Utils/Rule.php';
require_once 'CRM/Campaign/BAO/Petition.php';

/**
 * Get the list of signatories
 *
 * @param  array   $params           (reference ) input parameters
 *
 * @return array (reference )        contribution_id of created or updated record
 * @static void
 * @access public
 */
function &civicrm_api3_survey_respondant_get( $params ) {
  _civicrm_api3_initialize(true );
  try{
    civicrm_api3_verify_mandatory($params,'CRM_Campaign_BAO_Petition',array('survey_id'));

    if (array_key_exists ( 'status_id', $params ) ) {
      $status_id=$params['status_id'];
    } else {
      $status_id=null;
    }

    $petition = new CRM_Campaign_BAO_Petition();
    $signatures = $petition->getPetitionSignature($params['survey_id'],$status_id);
    return ($signatures);

  } catch (PEAR_Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  }
}

function &civicrm_api3_survey_respondant_count( $params ) {
  _civicrm_api3_initialize(true );
  try{

    $petition = new CRM_Campaign_BAO_Petition();
    if (array_key_exists ('groupby',$params) &&
        $params['groupby'] == 'country' ) {
      $signaturesCount = $petition->getPetitionSignatureTotalbyCountry($params['survey_id']);
    } else {
      $signaturesCount = $petition->getPetitionSignatureTotal($params['survey_id']);
    }
    return ($signaturesCount);

  } catch (PEAR_Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  }
}
