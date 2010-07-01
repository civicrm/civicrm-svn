<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/Selector/Base.php';
require_once 'CRM/Core/Selector/API.php';

require_once 'CRM/Utils/Pager.php';
require_once 'CRM/Utils/Sort.php';

require_once 'CRM/Contact/BAO/Query.php';

/**
 * This class is used to retrieve and display a range of
 * contacts that match the given criteria (specifically for
 * results of advanced search options.
 *
 */
class CRM_Campaign_Selector_Search extends CRM_Core_Selector_Base implements CRM_Core_Selector_API 
{
    /**
     * This defines two actions- View and Edit.
     *
     * @var array
     * @static
     */
    static $_links = null;

    /**
     * we use desc to remind us what that column is, name is used in the tpl
     *
     * @var array
     * @static
     */
    static $_columnHeaders;

    /**
     * Properties of contact we're interested in displaying
     * @var array
     * @static
     */
    static $_properties = array( 'contact_id', 
                                 'sort_name', 
                                 'street_number',
                                 'street_address',
                                 'city',
                                 'postal_code',
                                 'state_province',
                                 'country',
                                 'email',
                                 'phone' 
                                 );
    
    /** 
     * are we restricting ourselves to a single contact 
     * 
     * @access protected   
     * @var boolean   
     */   
    protected $_single = false;

    /**  
     * are we restricting ourselves to a single contact  
     *  
     * @access protected    
     * @var boolean    
     */    
    protected $_limit = null;

    /**
     * what context are we being invoked from
     *   
     * @access protected     
     * @var string
     */     
    protected $_context = null;

    /**
     * queryParams is the array returned by exportValues called on
     * the HTML_QuickForm_Controller for that page.
     *
     * @var array
     * @access protected
     */
    public $_queryParams;

    /**
     * represent the type of selector
     *
     * @var int
     * @access protected
     */
    protected $_action;

    /** 
     * The additional clause that we restrict the search with 
     * 
     * @var string 
     */ 
    protected $_surveyClause = null;
    
    /** 
     * The query object
     * 
     * @var string 
     */ 
    protected $_query;
    
    /*
     * activity detail table
     */
    const
        ACTIVITY_SURVEY_DETAIL_TABLE = 'civicrm_value_survey_activity_details';

    /**
     * Class constructor
     *
     * @param array    $queryParams array of parameters for query
     * @param int      $action - action of search basic or advanced.
     * @param string   $surveyClause if the caller wants to further restrict the search.
     * @param boolean  $single are we dealing only with one contact?
     * @param int      $limit  how many voters do we want returned
     *
     * @return CRM_Contact_Selector
     * @access public
     */
    function __construct( &$queryParams,
                          $action = CRM_Core_Action::NONE,
                          $surveyClause = null,
                          $single = false,
                          $limit = null,
                          $context = 'search' ) 
    {
        // submitted form values
        $this->_queryParams =& $queryParams;
        
        $this->_single  = $single;
        $this->_limit   = $limit;
        $this->_context = $context;
        
        $this->_surveyClause = $surveyClause;
        
        // type of selector
        $this->_action = $action;
        
        $this->_query = new CRM_Contact_BAO_Query( $this->_queryParams, null, null, false, false,
                                                   CRM_Contact_BAO_Query::MODE_CAMPAIGN );
    }//end of constructor
    
    /**
     * This method returns the links that are given for each search row.
     * currently the links added for each row are 
     * 
     * - View
     * - Edit
     *
     * @return array
     * @access public
     *
     */
    static function &links( )
    {
        return self::$_links = array( );
    }
    
    /**
     * getter for array of the parameters required for creating pager.
     *
     * @param 
     * @access public
     */
    function getPagerParams( $action, &$params ) 
    {
        $params['csvString']    = null;
        $params['status']       = ts('Voters') . ' %%StatusMessage%%';
        $params['rowCount']     = ( $this->_limit ) ? $this->_limit:CRM_Utils_Pager::ROWCOUNT;
        $params['buttonTop']    = 'PagerTopButton';
        $params['buttonBottom'] = 'PagerBottomButton';
    }
    
    
    /**
     * Returns total number of rows for the query.
     *
     * @param 
     * @return int Total number of rows 
     * @access public
     */
    function getTotalCount( $action )
    {
        return $this->_query->searchQuery( 0, 0, null,
                                           true, false, 
                                           false, false, 
                                           false, 
                                           $this->_campaignClause );
    }
    
    
    /**
     * returns all the rows in the given offset and rowCount
     *
     * @param enum   $action   the action being performed
     * @param int    $offset   the row number to start from
     * @param int    $rowCount the number of rows to return
     * @param string $sort     the sql string that describes the sort order
     * @param enum   $output   what should the result set include (web/email/csv)
     *
     * @return int   the total number of rows for this action
     */
    function &getRows( $action, $offset, $rowCount, $sort, $output = null ) 
    {
        
        /*
        require_once 'CRM/Contact/BAO/Contact/Utils.php';
        $result = $this->_query->searchQuery( $offset, $rowCount, $sort,
                                              false, false, 
                                              false, false, 
                                              false, 
                                              $this->_campaignClause
        );

        */
        require_once 'CRM/Contact/BAO/Contact/Utils.php';
        $result = $this->_buildQuery( );
        // process the result of the query
        $rows = array( );
        
        While ( $result->fetch( ) ) {
            $row = array( );
            // the columns we are interested in
            foreach (self::$_properties as $property) {
                if ( property_exists( $result, $property ) ) {
                    $row[$property] = $result->$property;   
                }      
            }
            $row['checkbox'] = CRM_Core_Form::CB_PREFIX . $result->contact_id;
            $row['contact_type'] = CRM_Contact_BAO_Contact_Utils::getImage( 'Individual' );
            
            $rows[] = $row;
        }
        
        return $rows;
    }
    
    function _buildQuery( ) {
         $session = CRM_Core_Session::singleton( );
 
        $select = "
DISTINCT(contact_a.id) as contact_id, contact_a.sort_name as sort_name, civicrm_address.id as address_id, civicrm_address.street_address as street_address, civicrm_address.street_number as street_number, civicrm_address.city as city, civicrm_address.postal_code as postal_code, civicrm_state_province.id as state_province_id, civicrm_state_province.abbreviation as state_province, civicrm_state_province.name as state_province_name, civicrm_country.id as country_id, civicrm_country.name as country, civicrm_phone.id as phone_id, civicrm_phone.phone_type_id as phone_type_id, civicrm_phone.phone as phone, civicrm_email.id as email_id, civicrm_email.email as email";

        $from =  "civicrm_contact contact_a LEFT JOIN civicrm_address ON ( contact_a.id = civicrm_address.contact_id AND civicrm_address.is_primary = 1 ) LEFT JOIN civicrm_state_province ON civicrm_address.state_province_id = civicrm_state_province.id  LEFT JOIN civicrm_country ON civicrm_address.country_id = civicrm_country.id  LEFT JOIN civicrm_email ON (contact_a.id = civicrm_email.contact_id AND civicrm_email.is_primary = 1) LEFT JOIN civicrm_phone ON (contact_a.id = civicrm_phone.contact_id AND civicrm_phone.is_primary = 1) LEFT JOIN civicrm_activity_target activity_target ON ( activity_target.target_contact_id = contact_a.id ) LEFT JOIN ". self::ACTIVITY_SURVEY_DETAIL_TABLE ." survery_details ON ( activity_target.activity_id = survery_details.entity_id )";
        
        $where   = "(contact_a.is_deleted = 0 AND contact_a.contact_type = 'Individual') "; 
        $columns = array( 'sort_name', 'street_number', 'street_address', 'city', 'status_id', 'survey_id' );

        $params = $clause = array( );
        $count  = 1;
        if ( !empty($this->_queryParams) ) {
            foreach( $this->_queryParams  as $queryParam ) {
                if ( in_array( $queryParam[0], $columns ) ) {
                    if ( !CRM_Utils_Array::value('2', $queryParam) ) {
                        continue;
                    }
                    $column = $queryParam[0];
                    $value  = $queryParam[2];
                    
                    if ( $column == 'sort_name' ) {
                        $clause[ ] = "{$column} LIKE %{$count}";
                        $params[$count] = array( '%'.$value.'%', 'String' );
                    } else if ( $column == 'status_id' ) { 
                        $clause[ ] = "survery_details.status_id = %{$count}";
                        $params[$count] = array( 'H', 'String' );
                        
                        // show voters contacts held by current interviewer
                        $count++;
                        $clause[ ] = "survery_details.interviewer_id = %{$count}";
                        $params[$count] = array( $session->get('userID'), 'Integer' );
                        
                    } else if ($column == 'survey_id' ) {
                        $clause[ ] = "survery_details.survey_id = %{$count}";
                        $params[$count] = array( $value, 'Integer' );
                    } else {
                        $clause[ ] = "{$column} = %{$count}";
                        $params[$count] = array( $value, 'String' );
                    }
                    $count++;
                }
            }

            if ( !empty($clause) ) {  
                $where .=  ' AND '. implode( ' AND ', $clause );
            }
        }
        $whereClause = CRM_Core_DAO::composeQuery( $where, $params, true );
        $query    =  "SELECT {$select} FROM {$from} WHERE {$whereClause} LIMIT 0, 50";
        $result = CRM_Core_DAO::executeQuery($query);
        return $result;
    }

    /**
     * @return array              $qill         which contains an array of strings
     * @access public
     x*/
    
    // the current internationalisation is bad, but should more or less work
    // for most of "European" languages
    public function getQILL( )
    {
        return $this->_query->qill( );
    }
    
    /** 
     * returns the column headers as an array of tuples: 
     * (name, sortName (key to the sort array)) 
     * 
     * @param string $action the action being performed 
     * @param enum   $output what should the result set include (web/email/csv) 
     * 
     * @return array the column headers that need to be displayed 
     * @access public 
     */ 
    public function &getColumnHeaders( $action = null, $output = null ) 
    {
        self::$_columnHeaders = array( );
        
        if ( ! $this->_single ) {
            $contactDetails = array(
                                    array( 'name'      => ts('Contact Name'), 
                                           'sort'      => 'sort_name', 
                                           'direction' => CRM_Utils_Sort::DONTCARE ),
                                    array( 'name' => ts('Street Number'),),
                                    array( 'name' => ts('Street Address') ),
                                    array( 'name' => ts('City') ),  
                                    array( 'name' => ts('Postal Code') ),    
                                    array( 'name' => ts('State') ),       
                                    array( 'name' => ts('Country') ),    
                                    array( 'name' => ts('Email') ),
                                    array( 'name' => ts('Phone') )   
                                    );
            self::$_columnHeaders = array_merge( $contactDetails, self::$_columnHeaders );
        }
        
        return self::$_columnHeaders;
    }
    
    function &getQuery( ) {
        return $this->_query;
    }
    
    /** 
     * name of export file. 
     * 
     * @param string $output type of output 
     * @return string name of the file 
     */ 
    function getExportFileName( $output = 'csv') { 
        return ts('CiviCRM Voter Search'); 
    }
    
}//end of class


