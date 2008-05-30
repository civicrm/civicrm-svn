<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2008                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Contribute/DAO/Contribution.php';

require_once 'CRM/Core/BAO/CustomField.php';
require_once 'CRM/Core/BAO/CustomValue.php';

class CRM_Contribute_BAO_Contribution extends CRM_Contribute_DAO_Contribution
{
    /**
     * static field for all the contribution information that we can potentially import
     *
     * @var array
     * @static
     */
    static $_importableFields = null;

    /**
     * static field for all the contribution information that we can potentially export
     *
     * @var array
     * @static
     */
    static $_exportableFields = null;


    function __construct()
    {
        parent::__construct();
    }
    

    /**
     * takes an associative array and creates a contribution object
     *
     * the function extract all the params it needs to initialize the create a
     * contribution object. the params array could contain additional unused name/value
     * pairs
     *
     * @param array  $params (reference ) an assoc array of name/value pairs
     * @param array $ids    the array that holds all the db ids
     *
     * @return object CRM_Contribute_BAO_Contribution object
     * @access public
     * @static
     */
    static function add(&$params, &$ids) 
    {
        if ( empty($params) ) {
            return;
        } 

        $duplicates = array( );
        if ( self::checkDuplicate( $params, $duplicates,
                                   CRM_Utils_Array::value( 'contribution', $ids ) ) ) {
            $error =& CRM_Core_Error::singleton( ); 
            $d = implode( ', ', $duplicates );
            $error->push( CRM_Core_Error::DUPLICATE_CONTRIBUTION, 'Fatal', array( $d ), "Found matching contribution(s): $d" );
            return $error;
        }

        // first clean up all the money fields
        $moneyFields = array( 'total_amount',
                              'net_amount',
                              'fee_amount',
                              'non_deductible_amount' );
        foreach ( $moneyFields as $field ) {
            if ( isset( $params[$field] ) ) {
                $params[$field] = CRM_Utils_Rule::cleanMoney( $params[$field] );
            }
        }

        require_once 'CRM/Utils/Hook.php';
        if ( CRM_Utils_Array::value( 'contribution', $ids ) ) {
            CRM_Utils_Hook::pre( 'edit', 'Contribution', $ids['contribution'], $params );
        } else {
            CRM_Utils_Hook::pre( 'create', 'Contribution', null, $params ); 
        }

        $contribution =& new CRM_Contribute_BAO_Contribution();
        $contribution->copyValues($params);
        
        $contribution->id        = CRM_Utils_Array::value( 'contribution', $ids );

        require_once 'CRM/Utils/Rule.php';
        if (!CRM_Utils_Rule::currencyCode($contribution->currency)) {
            require_once 'CRM/Core/Config.php';
            $config =& CRM_Core_Config::singleton();
            $contribution->currency = $config->defaultCurrency;
        }

        $result = $contribution->save();

        // reset the group contact cache for this group
        require_once 'CRM/Contact/BAO/GroupContactCache.php';
        CRM_Contact_BAO_GroupContactCache::remove( );

        if ( CRM_Utils_Array::value( 'contribution', $ids ) ) {
            CRM_Utils_Hook::post( 'edit', 'Contribution', $contribution->id, $contribution );
        } else {
            CRM_Utils_Hook::post( 'create', 'Contribution', $contribution->id, $contribution );
        }

        return $result;
    }

    /**
     * Given the list of params in the params array, fetch the object
     * and store the values in the values array
     *
     * @param array $params input parameters to find object
     * @param array $values output values of the object
     * @param array $ids    the array that holds all the db ids
     *
     * @return CRM_Contribute_BAO_Contribution|null the found object or null
     * @access public
     * @static
     */
    static function &getValues( &$params, &$values, &$ids ) 
    {
        $contribution =& new CRM_Contribute_BAO_Contribution( );

        $contribution->copyValues( $params );

        if ( $contribution->find(true) ) {
            $ids['contribution'] = $contribution->id;

            CRM_Core_DAO::storeValues( $contribution, $values );

            return $contribution;
        }
        return null;
    }

    /**
     * takes an associative array and creates a contribution object
     *
     * @param array $params (reference ) an assoc array of name/value pairs
     * @param array $ids    the array that holds all the db ids
     *
     * @return object CRM_Contribute_BAO_Contribution object 
     * @access public
     * @static
     */
    static function &create(&$params, &$ids) 
    {
        require_once 'CRM/Utils/Money.php';
        require_once 'CRM/Utils/Date.php';

        // FIXME: a cludgy hack to fix the dates to MySQL format
        $dateFields = array('receive_date', 'cancel_date', 'receipt_date', 'thankyou_date');
        foreach ($dateFields as $df) {
            if (isset($params[$df])) {
                $params[$df] = CRM_Utils_Date::isoToMysql($params[$df]);
            }
        }

        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );

        $contribution = self::add($params, $ids);

        if ( is_a( $contribution, 'CRM_Core_Error') ) {
            $transaction->rollback( );
            return $contribution;
        }

        $params['contribution_id'] = $contribution->id;

        if ( CRM_Utils_Array::value( 'custom', $params ) &&
             is_array( $params['custom'] ) ) {
            require_once 'CRM/Core/BAO/CustomValueTable.php';
            CRM_Core_BAO_CustomValueTable::store( $params['custom'], 'civicrm_contribution', $contribution->id );
        }

        $session = & CRM_Core_Session::singleton();

        if ( CRM_Utils_Array::value('note', $params) ) {
            require_once 'CRM/Core/BAO/Note.php';
           
            $noteParams = array(
                                'entity_table'  => 'civicrm_contribution',
                                'note'          => $params['note'],
                                'entity_id'     => $contribution->id,
                                'contact_id'    => $session->get('userID'),
                                'modified_date' => date('Ymd')
                                );
            if( ! $noteParams['contact_id'] ) {
                $noteParams['contact_id'] =  $params['contact_id'];
            } 
            
            CRM_Core_BAO_Note::add( $noteParams, $ids['note'] );
        }

        // check if activity record exist for this contribution, if
        // not add activity
        require_once "CRM/Activity/DAO/Activity.php";
        $activity =& new CRM_Activity_DAO_Activity( );
        $activity->source_record_id = $contribution->id;
        $activity->activity_type_id = CRM_Core_OptionGroup::getValue( 'activity_type',
                                                                      'Contribution',
                                                                      'name' );
        if ( ! $activity->find( ) ) {
            require_once "CRM/Activity/BAO/Activity.php";
            CRM_Activity_BAO_Activity::addActivity( $contribution, 'Offline' );
        }

        $transaction->commit( );
        
        return $contribution;
    }

    /**
     * Get the values for pseudoconstants for name->value and reverse.
     *
     * @param array   $defaults (reference) the default values, some of which need to be resolved.
     * @param boolean $reverse  true if we want to resolve the values in the reverse direction (value -> name)
     *
     * @return void
     * @access public
     * @static
     */
    static function resolveDefaults(&$defaults, $reverse = false)
    {
        require_once 'CRM/Contribute/PseudoConstant.php';

        self::lookupValue($defaults, 'contribution_type', CRM_Contribute_PseudoConstant::contributionType(), $reverse);
        self::lookupValue($defaults, 'payment_instrument', CRM_Contribute_PseudoConstant::paymentInstrument(), $reverse);
        self::lookupValue($defaults, 'contribution_status', CRM_Contribute_PseudoConstant::contributionStatus(), $reverse);    }

    /**
     * This function is used to convert associative array names to values
     * and vice-versa.
     *
     * This function is used by both the web form layer and the api. Note that
     * the api needs the name => value conversion, also the view layer typically
     * requires value => name conversion
     */
    static function lookupValue(&$defaults, $property, &$lookup, $reverse)
    {
        $id = $property . '_id';

        $src = $reverse ? $property : $id;
        $dst = $reverse ? $id       : $property;

        if (!array_key_exists($src, $defaults)) {
            return false;
        }

        $look = $reverse ? array_flip($lookup) : $lookup;
        
        if(is_array($look)) {
            if (!array_key_exists($defaults[$src], $look)) {
                return false;
            }
        }
        $defaults[$dst] = $look[$defaults[$src]];
        return true;
    }

    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. We'll tweak this function to be more
     * full featured over a period of time. This is the inverse function of
     * create.  It also stores all the retrieved values in the default array
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the name / value pairs
     *                        in a hierarchical manner
     * @param array $ids      (reference) the array that holds all the db ids
     *
     * @return object CRM_Contribute_BAO_Contribution object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults, &$ids ) {
        $contribution = CRM_Contribute_BAO_Contribution::getValues( $params, $defaults, $ids );
        return $contribution;
    }

    /**
     * combine all the importable fields from the lower levels object
     *
     * The ordering is important, since currently we do not have a weight
     * scheme. Adding weight is super important and should be done in the
     * next week or so, before this can be called complete.
     *
     * @return array array of importable Fields
     * @access public
     */
    function &importableFields( $contacType = 'Individual', $status = true ) 
    {
        if ( ! self::$_importableFields ) {
            if ( ! self::$_importableFields ) {
                self::$_importableFields = array();
            }

            if (!$status) {
                $fields = array( '' => array( 'title' => ts('- do not import -') ) );
            } else {
                $fields = array( '' => array( 'title' => ts('- Contribution Fields -') ) );
            }

            require_once 'CRM/Core/DAO/Note.php';
            $note          = CRM_Core_DAO_Note::import( );
            $tmpFields     = CRM_Contribute_DAO_Contribution::import( );
            unset($tmpFields['option_value']);
            require_once 'CRM/Core/OptionValue.php';
            $optionFields = CRM_Core_OptionValue::getFields($mode ='contribute' );
            require_once 'CRM/Contact/BAO/Contact.php';
            $contactFields = CRM_Contact_BAO_Contact::importableFields( $contacType, null );
            
            // Using new Dedupe rule.
            $ruleParams = array(
                                'contact_type' => $contacType,
                                'level' => 'Strict'
                                );
            require_once 'CRM/Dedupe/BAO/Rule.php';
            $fieldsArray = CRM_Dedupe_BAO_Rule::dedupeRuleFields($ruleParams);
            $tmpConatctField = array();
            if( is_array($fieldsArray) ) {
                foreach ( $fieldsArray as $value) {
                    //skip if there is no dupe rule
                    if ( $value == 'none' ) {
                        continue;
                    }
                    
                    $tmpConatctField[trim($value)] = $contactFields[trim($value)];
                    if (!$status) {
                        $title = $tmpConatctField[trim($value)]['title']." (match to contact)" ;
                    } else {
                        $title = $tmpConatctField[trim($value)]['title'];
                    }
                    $tmpConatctField[trim($value)]['title'] = $title;

                }
            }
            $tmpConatctField['external_identifier'] = $contactFields['external_identifier'];
            $tmpConatctField['external_identifier']['title'] = $contactFields['external_identifier']['title'] . " (match to contact)";
            
            $fields = array_merge($fields, $tmpConatctField);
            $fields = array_merge($fields, $tmpFields);
            $fields = array_merge($fields, $note);
            $fields = array_merge($fields, $optionFields);
            $fields = array_merge($fields, CRM_Core_BAO_CustomField::getFieldsForImport('Contribution'));
            self::$_importableFields = $fields;
        }
        return self::$_importableFields;
    }

    function &exportableFields( ) 
    {
        if ( ! self::$_exportableFields ) {
            if ( ! self::$_exportableFields ) {
                self::$_exportableFields = array();
            }
            require_once 'CRM/Core/OptionValue.php';
            require_once 'CRM/Contribute/DAO/Product.php';
            require_once 'CRM/Contribute/DAO/ContributionProduct.php';
            require_once 'CRM/Contribute/DAO/ContributionType.php';
            $impFields = CRM_Contribute_DAO_Contribution::import( );
            $expFieldProduct = CRM_Contribute_DAO_Product::export( );
            $expFieldsContrib = CRM_Contribute_DAO_ContributionProduct::export( );
            $typeField = CRM_Contribute_DAO_ContributionType::export( );
            $optionField = CRM_Core_OptionValue::getFields($mode ='contribute' );
            $fields = array_merge($impFields, $typeField);
            $fields = array_merge($fields, $expFieldProduct );
            $fields = array_merge($fields, $expFieldsContrib );
            $fields = array_merge($fields, $optionField );
            $fields = array_merge($fields, CRM_Core_BAO_CustomField::getFieldsForImport('Contribution'));
            
            self::$_exportableFields = $fields;
        }
        return self::$_exportableFields;
    }

    function getTotalAmountAndCount( $status = null, $startDate = null, $endDate = null ) 
    {
        
        $where = array( );
        switch ( $status ) {
        case 'Valid':
            $where[] = 'contribution_status_id = 1';
            break;

        case 'Cancelled':
            $where[] = 'contribution_status_id = 3';
            break;
        }

        if ( $startDate ) {
            $where[] = "receive_date >= '" . CRM_Utils_Type::escape( $startDate, 'Timestamp' ) . "'";
        }
        if ( $endDate ) {
            $where[] = "receive_date <= '" . CRM_Utils_Type::escape( $endDate, 'Timestamp' ) . "'";
        }

        $whereCond = implode( ' AND ', $where );

        $query = "
SELECT sum( total_amount ) as total_amount, count( id ) as total_count
FROM   civicrm_contribution
WHERE  $whereCond AND is_test=0
";

        $dao = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        if ( $dao->fetch( ) ) {
            return array( 'amount' => $dao->total_amount,
                          'count'  => $dao->total_count );
        }
        return null;
    }

    /**                                                           
     * Delete the indirect records associated with this contribution first
     * 
     * @return $results no of deleted Contribution on success, false otherwise
     * @access public 
     * @static 
     */ 
    static function deleteContribution( $id ) 
    {

        CRM_Utils_Hook::pre( 'delete', 'Contribution', $id, CRM_Core_DAO::$_nullArray );

        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        
        $results = null;
        //delete activity record
        require_once "CRM/Activity/BAO/Activity.php";
        $params = array( 'source_record_id' => $id,
                         'activity_type_id' => 6 );// activity type id for contribution

        CRM_Activity_BAO_Activity::deleteActivity( $params );
        
        $dao     = new CRM_Contribute_DAO_Contribution( );
        $dao->id = $id;
        $results = $dao->delete( );
        
        $transaction->commit( );

        CRM_Utils_Hook::post( 'delete', 'Contribution', $dao->id, $dao );

        return $results;
    }
    
    /**
     * Check if there is a contribution with the same trxn_id or invoice_id
     *
     * @param array  $params (reference ) an assoc array of name/value pairs
     * @param array  $duplicates (reference ) store ids of duplicate contribs
     *
     * @return boolean true if duplicate, false otherwise
     * @access public
     * static
     */
    static function checkDuplicate( $input, &$duplicates, $id = null ) 
    {
        if ( ! $id ) {
            $id         = CRM_Utils_Array::value( 'id'        , $input );
        }
        $trxn_id    = CRM_Utils_Array::value( 'trxn_id'   , $input );
        $invoice_id = CRM_Utils_Array::value( 'invoice_id', $input );

        $clause = array( );
        $input = array( );

        if ( $trxn_id ) {
            $clause[]  = "trxn_id = %1";
            $input[1]  = array( $trxn_id, 'String' );
        }

        if ( $invoice_id ) {
            $clause[]  = "invoice_id = %2";
            $input[2]  = array( $invoice_id, 'String' );
        }

        if ( empty( $clause ) ) {
            return false;
        }

        $clause = implode( ' OR ', $clause );
        if ( $id ) {
            $clause   = "( $clause ) AND id != %3";
            $input[3] = array( $id, 'Integer' );
        }
        
        $query = "SELECT id FROM civicrm_contribution WHERE $clause";
        $dao =& CRM_Core_DAO::executeQuery( $query, $input );
        $result = false;
        while ( $dao->fetch( ) ) {
            $duplicates[] = $dao->id;
            $result = true;
        }
        return $result;
    }
    
    /**
     * takes an associative array and creates a contribution_product object
     *
     * the function extract all the params it needs to initialize the create a
     * contribution_product object. the params array could contain additional unused name/value
     * pairs
     *
     * @param array  $params (reference ) an assoc array of name/value pairs
    
     * @return object CRM_Contribute_BAO_ContributionProduct object
     * @access public
     * @static
     */
    static function addPremium ( &$params ) 
    {

        require_once 'CRM/Contribute/DAO/ContributionProduct.php';
        $contributionProduct = new CRM_Contribute_DAO_ContributionProduct();
        $contributionProduct->copyValues($params);
        return $contributionProduct->save();
    }

    /**
     * Function to get list of contribution fields for profile
     * For now we only allow custom contribution fields to be in
     * profile
     *
     * @return return the list of contribution fields
     * @static
     * @access public
     */
    static function getContributionFields( ) 
    {
        $contributionFields =& CRM_Contribute_DAO_Contribution::export( );

        foreach ($contributionFields as $key => $var) {
            if ($key == 'contribution_contact_id') {
                continue;
            }
            $fields[$key] = $var;
        }

        $fields = array_merge($fields, CRM_Core_BAO_CustomField::getFieldsForImport('Contribution'));
        return $fields;
    }

    static function getCurrentandGoalAmount( $pageID ) 
    {
        $query = "
SELECT p.goal_amount as goal, sum( c.total_amount ) as total
  FROM civicrm_contribution_page p,
       civicrm_contribution      c
 WHERE p.id = c.contribution_page_id
   AND p.id = %1
   AND c.cancel_date is null
GROUP BY p.id
";

        $config =& CRM_Core_Config::singleton( );
        $params = array( 1 => array( $pageID, 'Integer' ) );
        $dao =& CRM_Core_DAO::executeQuery( $query, $params );
        
        if ( $dao->fetch( ) ) {
            return array( $dao->goal, $dao->total );
        } else {
            return array( null, null );
        }
    }

    /**
     * Function to create is honor of
     * 
     * @param array $params  associated array of fields (by reference)
     * @param int   $honorId honor Id
     *
     * @return contact id
     */
    function createHonorContact( &$params, $honorId = null ) 
    {
        $honorParams = array( 'first_name'    => $params["honor_first_name"],
                              'last_name'     => $params["honor_last_name"], 
                              'prefix_id'     => $params["honor_prefix_id"],
                              'email-Primary' => $params["honor_email"] );
        if ( !$honorId ) {
            require_once "CRM/Core/BAO/UFGroup.php";
            $ids = CRM_Core_BAO_UFGroup::findContact( $honorParams, null, 'Individual' );
            $contactsIds = explode( ',', $ids );
            
            if ( is_numeric( $contactsIds[0] ) && count ( $contactsIds ) ==  1 ) {
                $honorId = $contactsIds[0];
            }
        }
        
        $contact =& CRM_Contact_BAO_Contact::createProfileContact( $honorParams, CRM_Core_DAO::$_nullArray, $honorId );
        return $contact;
    }
    
    /**
     * Function to get list of contribution In Honor of contact Ids
     *
     * @param int $honorId In Honor of Contact ID
     *
     * @return return the list of contribution fields
     * 
     * @access public
     * @static
     */
    static function getHonorContacts( $honorId )
    {
        $params=array( );
        require_once 'CRM/Contribute/DAO/Contribution.php';
        $honorDAO =& new CRM_Contribute_DAO_Contribution();
        $honorDAO->honor_contact_id =  $honorId;
        $honorDAO->find( );

        require_once 'CRM/Contribute/PseudoConstant.php';
        $status = CRM_Contribute_Pseudoconstant::contributionStatus($honorDAO->contribution_status_id);
        
        while( $honorDAO->fetch( ) ) {
            $params[$honorDAO->id]['honorId']      = $honorDAO->contact_id;		    
            $params[$honorDAO->id]['display_name'] = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $honorDAO->contact_id, 'display_name' );
            $params[$honorDAO->id]['type']         = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionType', $honorDAO->contribution_type_id, 'name' );
            $params[$honorDAO->id]['amount']       = $honorDAO->total_amount;
            $params[$honorDAO->id]['source']       = $honorDAO->source;
            $params[$honorDAO->id]['receive_date'] = $honorDAO->receive_date;
            $params[$honorDAO->id]['contribution_status']= CRM_Utils_Array::value($honorDAO->contribution_status_id, $status);  
        }

        return $params;
    }

    /**
     * function to get the sort name of a contact for a particular contribution
     *
     * @param  int    $id      id of the contribution
     *
     * @return null|string     sort name of the contact if found
     * @static
     * @access public
     */
    static function sortName( $id ) 
    {
        $id = CRM_Utils_Type::escape( $id, 'Integer' );

        $query = "
SELECT civicrm_contact.sort_name
FROM   civicrm_contribution, civicrm_contact
WHERE  civicrm_contribution.contact_id = civicrm_contact.id
  AND  civicrm_contribution.id = {$id}
";
        return CRM_Core_DAO::singleValueQuery( $query, CRM_Core_DAO::$_nullArray );
    }

    static function annual( $contactID ) {
        
        if ( is_array( $contactID ) ) {
            $contactIDs = implode( ',', $contactID );
        } else {
            $contactIDs = $contactID;
        }

        $config =& CRM_Core_Config::singleton( );
        $startDate = $endDate = null;
        $year     = date( 'Y' );
        $nextYear = $year + 1;
        
        if ( $config->fiscalYearStart ) {
            if ( $config->fiscalYearStart['M'] < 10 ) {
                $config->fiscalYearStart['M'] = '0' . $config->fiscalYearStart['M'];
            }
            if ( $config->fiscalYearStart['d'] < 10 ) {
                $config->fiscalYearStart['d'] = '0' . $config->fiscalYearStart['d'];
            }
            $monthDay = $config->fiscalYearStart['M'] . $config->fiscalYearStart['d'];
        } else {
            $monthDay = '0101';
        }
        $startDate = "$year$monthDay";
        $endDate   = "$nextYear$monthDay";

        $query = "
SELECT count(*) as count,
       sum(total_amount) as amount
  FROM civicrm_contribution b
 WHERE b.contact_id IN ( $contactIDs )
   AND b.contribution_status_id = 1
   AND b.is_test = 0
   AND b.receive_date >= $startDate
   AND b.receive_date <  $endDate
";
        $dao =& CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        if ( $dao->fetch( ) ) {
            if ( $dao->count > 0 && $dao->amount > 0) {
                return array( $dao->count, $dao->amount, (float ) $dao->amount / $dao->count );
            }
        }
        return array( 0, 0, 0 );
    }
    
    /**
     * process payment confirm
     *
     * 
     */
    public function processConfirm( &$form, 
                                    &$paymentParams,
                                    &$premiumParams,
                                    $contactID,
                                    $contributionTypeId,
                                    $component = 'contribution' )
    { 
        require_once 'CRM/Core/Payment/Form.php';
        CRM_Core_Payment_Form::mapParams( $form->_bltID, $form->_params, $paymentParams, true );
        
        $contributionType =& new CRM_Contribute_DAO_ContributionType( );
        if( isset( $paymentParams['contribution_type'] ) ) {
            $contributionType->id = $paymentParams['contribution_type'];
        } else {
            $contributionType->id = $contributionTypeId;
        }
        if ( ! $contributionType->find( true ) ) {
            CRM_Core_Error::fatal( "Could not find a system table" );
        }
        
        // add some contribution type details to the params list
        // if folks need to use it
        $paymentParams['contributionType_name']                = 
            $form->_params['contributionType_name']            = $contributionType->name;
        $paymentParams['contributionType_accounting_code']     = 
            $form->_params['contributionType_accounting_code'] = $contributionType->accounting_code;
        $paymentParams['contributionPageID']                   =
            $form->_params['contributionPageID']               = $form->_values['id'];
        
        
        if ( $form->_values['is_monetary'] && $form->_amount > 0.0 ) {
            require_once 'CRM/Core/Payment.php';
            $payment =& CRM_Core_Payment::singleton( $form->_mode, 'Contribute', $form->_paymentProcessor );
        }
        
        //fix for CRM-2062
        $now = date( 'YmdHis' );

        $result = null;
        if ( $form->_contributeMode == 'notify' ||
             $form->_params['is_pay_later'] ) {
            // this is not going to come back, i.e. we fill in the other details
            // when we get a callback from the payment processor
            // also add the contact ID and contribution ID to the params list
            $paymentParams['contactID'] = $form->_params['contactID'] = $contactID;
            $contribution =
                CRM_Contribute_Form_Contribution_Confirm::processContribution(
                                                                              $form,
                                                                              $paymentParams,
                                                                              null,
                                                                              $contactID,
                                                                              $contributionType, 
                                                                              true, true, true );
            $form->_params['contributionID'    ] = $contribution->id;
            $form->_params['contributionTypeID'] = $contributionType->id;
            $form->_params['item_name'         ] = $form->_params['description'];
            $form->_params['receive_date'      ] = $now;
            if ( $form->_values['is_recur'] &&
                 $contribution->contribution_recur_id ) {
                $form->_params['contributionRecurID'] = $contribution->contribution_recur_id;
            }
            
            $form->set( 'params', $form->_params );
            $form->postProcessPremium( $premiumParams, $contribution );
            
            if ( $form->_values['is_monetary'] && $form->_amount > 0.0 ) {
                // add qfKey so we can send to paypal
                $form->_params['qfKey'] = $form->controller->_key;
                if ( $component == 'membership' ) {
                    $membershipResult = array( 1 => $contribution );
                    return $membershipResult;
                } else {
                    if ( ! $form->_params['is_pay_later'] ) {
                        $result =& $payment->doTransferCheckout( $form->_params );
                    } else {
                        // follow similar flow as IPN
                        return;
                    }
                }
            }
        } elseif ( $form->_contributeMode == 'express' ) {
            if ( $form->_values['is_monetary'] && $form->_amount > 0.0 ) {
                $result =& $payment->doExpressCheckout( $paymentParams );
            }
        } elseif ( $form->_values['is_monetary'] && $form->_amount > 0.0 ) {
            $result =& $payment->doDirectPayment( $paymentParams );
        }
        
        if ( $component == 'membership' ) {
            $membershipResult = array( );
        }       
        
        if ( is_a( $result, 'CRM_Core_Error' ) ) {
            if ( $component !== 'membership' ) {
                CRM_Core_Error::displaySessionError( $result );
                CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/contribute/transact', '_qf_Main_display=true' ) );
            }
            $membershipResult[1] = $result;
        } else {
            if ( $result ) {
                $form->_params = array_merge( $form->_params, $result );
            }
            $form->_params['receive_date'] = $now;
            $form->set( 'params', $form->_params );
            $form->assign( 'trxn_id', $result['trxn_id'] );
            $form->assign( 'receive_date',
                           CRM_Utils_Date::mysqlToIso( $form->_params['receive_date']) );
        
            // result has all the stuff we need
            // lets archive it to a financial transaction
            if ( $contributionType->is_deductible ) {
                $form->assign('is_deductible',  true );
                $form->set('is_deductible',  true);
            }
            if( isset( $paymentParams['contribution_source'] ) ) {
                $form->_params['source'] = $paymentParams['contribution_source'];
            }
            
            $contribution = CRM_Contribute_Form_Contribution_Confirm::processContribution( $form,
                                                                      $form->_params, $result, $contactID, 
                                                                      $contributionType, true, false, true );
            
            $form->postProcessPremium( $premiumParams, $contribution );
            
            $membershipResult[1] = $contribution;
        }
        
        if ( $component == 'membership' ) {
            return $membershipResult;
        }       
        
        // finally send an email receipt
        require_once "CRM/Contribute/BAO/ContributionPage.php";
        $form->_values['contribution_id'] = $contribution->id;
        CRM_Contribute_BAO_ContributionPage::sendMail( $contactID, $form->_values, $contribution->is_test );
    }


     /**
     * Check if there is a contribution with the params passed in.
     * Used for trxn_id,invoice_id and contribution_id
     *
     * @param array  $params (reference ) an assoc array of name/value pairs
     *
     * @return array contribution id if success else NULL
     * @access public
     * static
     */
    static function checkDuplicateIds( $params ) 
    {
        $dao = new CRM_Contribute_DAO_Contribution();
        
        $clause = array( );
        $input = array( );
        foreach ( $params as $k=>$v ) {
            if( $v ) {
                $clause[]  = "$k = '$v'";                
            } 
        }
        $clause = implode( ' AND ', $clause );
        $query = "SELECT id FROM civicrm_contribution WHERE $clause";
        $dao =& CRM_Core_DAO::executeQuery( $query, $input );
       
        while ( $dao->fetch( ) ) {
            $result = $dao->id;
            return $result;            
        }
        return NULL;        
    }

}


