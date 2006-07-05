<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.5                                                |
 +--------------------------------------------------------------------+
 | Copyright (c) 2005 Donald A. Lobo                                  |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the Affero General Public License Version 1,    |
 | March 2002.                                                        |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the Affero General Public License for more details.            |
 |                                                                    |
 | You should have received a copy of the Affero General Public       |
 | License along with this program; if not, contact the Social Source |
 | Foundation at info[AT]socialsourcefoundation[DOT]org.  If you have |
 | questions about the Affero General Public License or the licensing |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | at http://www.openngo.org/faqs/licensing.html                      |
 +--------------------------------------------------------------------+
*/

/**
 *
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo (c) 2005
 * $Id$
 *
 */

require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/Selector/Base.php';
require_once 'CRM/Core/Selector/API.php';

require_once 'CRM/Utils/Pager.php';
require_once 'CRM/Utils/Sort.php';

require_once 'CRM/Contact/BAO/Contact.php';
require_once 'CRM/Contact/BAO/Query.php';

/**
 * This class is used to retrieve and display a range of
 * contacts that match the given criteria (specifically for
 * results of advanced search options.
 *
 */
class CRM_Contact_Selector extends CRM_Core_Selector_Base implements CRM_Core_Selector_API 
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
    static $_properties = array('contact_id', 'contact_type', 'contact_sub_type', 
                                'sort_name', 'street_address',
                                'city', 'state_province', 'postal_code', 'country',
                                'geo_code_1', 'geo_code_2',
                                'email', 'phone', 'status' );

    /**
     * This caches the content for the display system.
     *
     * @var string
     * @access protected
     */
    protected $_contact;

    /**
     * formValues is the array returned by exportValues called on
     * the HTML_QuickForm_Controller for that page.
     *
     * @var array
     * @access protected
     */
    public $_formValues;

    /**
     * params is the array in a value used by the search query creator
     *
     * @var array
     * @access protected
     */
    public $_params;

    /**
     * The return properties used for search
     *
     * @var array
     * @access protected
     */
    protected $_returnProperties;

    /**
     * represent the type of selector
     *
     * @var int
     * @access protected
     */
    protected $_action;

    protected $_query;

    /** 
     * group id 
     * 
     * @var int 
     */ 
    protected $_ufGroupID; 
    
    /**
     * the public visible fields to be shown to the user
     *
     * @var array
     * @access protected
     */
    protected $_fields;

    /**
     * Class constructor
     *
     * @param array $formValues array of form values imported
     * @param array $params     array of parameters for query
     * @param int   $action - action of search basic or advanced.
     *
     * @return CRM_Contact_Selector
     * @access public
     */
    function __construct(&$formValues, &$params, &$returnProperties, $action = CRM_Core_Action::NONE) 
    {
        //object of BAO_Contact_Individual for fetching the records from db
        $this->_contact =& new CRM_Contact_BAO_Contact();

        // submitted form values
        $this->_formValues       =& $formValues;
        $this->_params           =& $params;
        $this->_returnProperties =& $returnProperties;

        // type of selector
        $this->_action = $action;
        
        $this->_ufGroupID = $this->_formValues['uf_group_id'];

        if ( $this->_ufGroupID ) {
            $this->_fields = CRM_Core_BAO_UFGroup::getListingFields( CRM_Core_Action::VIEW,
                                                                     CRM_Core_BAO_UFGroup::PUBLIC_VISIBILITY |
                                                                     CRM_Core_BAO_UFGroup::LISTINGS_VISIBILITY,
                                                                     false, $this->_ufGroupID );
            self::$_columnHeaders = null;

            //CRM_Core_Error::debug( 'f', $this->_fields );
            
            $this->_customFields =& CRM_Core_BAO_CustomField::getFieldsForImport( 'Individual' );

            $this->_returnProperties =& CRM_Contact_BAO_Contact::makeHierReturnProperties( $this->_fields );
            $this->_returnProperties['contact_type'] = 1;
            $this->_returnProperties['contact_sub_type'] = 1;
            $this->_returnProperties['sort_name'   ] = 1;
        }

        $this->_query   =& new CRM_Contact_BAO_Query( $this->_params, $this->_returnProperties );
        $this->_options =& $this->_query->_options;
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
    static function &links()
    {

        if (!(self::$_links)) {
            self::$_links = array(
                                  CRM_Core_Action::VIEW   => array(
                                                                   'name'     => ts('View'),
                                                                   'url'      => 'civicrm/contact/view',
                                                                   'qs'       => 'reset=1&cid=%%id%%',
                                                                   'title'    => ts('View Contact Details'),
                                                                  ),
                                  CRM_Core_Action::UPDATE => array(
                                                                   'name'     => ts('Edit'),
                                                                   'url'      => 'civicrm/contact/add',
                                                                   'qs'       => 'reset=1&action=update&cid=%%id%%',
                                                                   'title'    => ts('Edit Contact Details'),
                                                                  ),
                                  );

            $config = CRM_Core_Config::singleton( );
            if ( $config->mapAPIKey && $config->mapProvider) {
                self::$_links[CRM_Core_Action::MAP] = array(
                                                            'name'     => ts('Map'),
                                                            'url'      => 'civicrm/contact/search/map',
                                                            'qs'       => 'reset=1&cid=%%id%%',
                                                            'title'    => ts('Map Contact'),
                                                            );
            }
        }
        return self::$_links;
    } //end of function


    /**
     * getter for array of the parameters required for creating pager.
     *
     * @param 
     * @access public
     */
    function getPagerParams($action, &$params) 
    {
        $params['status']       = ts('Contact %%StatusMessage%%');
        $params['csvString']    = null;
        $params['rowCount']     = CRM_Utils_Pager::ROWCOUNT;

        $params['buttonTop']    = 'PagerTopButton';
        $params['buttonBottom'] = 'PagerBottomButton';
    }//end of function


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
    function &getColumnHeaders($action = null, $output = null) 
    {

        if ( $output == CRM_Core_Selector_Controller::EXPORT || $output == CRM_Core_Selector_Controller::SCREEN ) {
            $csvHeaders = array( ts('Contact Id'), ts('Contact Type') );
            foreach ( self::_getColumnHeaders() as $column ) {
                if ( array_key_exists( 'name', $column ) ) {
                    $csvHeaders[] = $column['name'];
                }
            }
            return $csvHeaders;
        } else if ( $this->_ufGroupID ) {
            // we dont use the cached value of column headers
            // since it potentially changed because of the profile selected
            static $skipFields = array( 'group', 'tag' );
            $direction = CRM_Utils_Sort::ASCENDING;
            $empty = true;
            if ( ! self::$_columnHeaders ) {
                self::$_columnHeaders = array( array( 'name' => '' ),
                                               array(
                                                     'name'      => ts('Name'),
                                                     'sort'      => 'sort_name',
                                                     'direction' => CRM_Utils_Sort::ASCENDING,
                                                     )
                                               );
                foreach ( $this->_fields as $name => $field ) { 
                    if ( $field['in_selector'] &&
                         ! in_array( $name, $skipFields ) ) {
                        self::$_columnHeaders[] = array( 'name'      => $field['title'],
                                                         'sort'      => $name,
                                                         'direction' => $direction );
                        $direction = CRM_Utils_Sort::DONTCARE;
                        $empty = false;
                    }
                }
                    
                // if we dont have any valid columns, dont add the implicit ones
                // this allows the template to check on emptiness of column headers
                if ( $empty ) {
                    self::$_columnHeaders = array( );
                } else {
                    self::$_columnHeaders[] = array('desc' => ts('Actions'));
                }
            }
            return self::$_columnHeaders;
        } else if ( ! empty( $this->_returnProperties ) ) { 

            self::$_columnHeaders = array( array( 'name' => '' ),
                                           array(
                                                 'name'      => ts('Name'),
                                                 'sort'      => 'sort_name',
                                                 'direction' => CRM_Utils_Sort::ASCENDING,
                                                 )
                                           );
            $properties =& self::makeProperties( $this->_returnProperties );

            foreach ( $properties as $prop ) {
                if ( $prop == 'contact_type' || $prop == 'contact_sub_type' || $prop == 'sort_name' ) {
                    continue;
                }

                if ( strpos($prop, '-') ) {
                    list ($loc, $fld, $phoneType) = explode('-', $prop);
                    $title = $this->_query->_fields[$fld]['title'];
                    if (trim($phoneType) && !is_numeric($phoneType) && strtolower($phoneType) != $fld) {
                        $title .= "-{$phoneType}";
                    }
                    $title .= " ($loc)";
                } else {
                    $title = $this->_query->_fields[$prop]['title'];
                }

                self::$_columnHeaders[] = array( 'name' => $title, 'desc' => $prop );
            }
            self::$_columnHeaders[] = array('desc' => ts('Actions'));
            return self::$_columnHeaders;
        } else {
            return self::_getColumnHeaders();
        }
    }


    /**
     * Returns total number of rows for the query.
     *
     * @param 
     * @return int Total number of rows 
     * @access public
     */
    function getTotalCount($action)
    {
        return $this->_query->searchQuery( 0, 0, null, true );
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

    function &getRows($action, $offset, $rowCount, $sort, $output = null) {
        $config =& CRM_Core_Config::singleton( );

        if ( ( $output == CRM_Core_Selector_Controller::EXPORT || $output == CRM_Core_Selector_Controller::SCREEN ) &&
             $this->_formValues['radio_ts'] == 'ts_sel' ) {
            $includeContactIds = true;
        } else {
            $includeContactIds = false;
        }

        // note the formvalues were given by CRM_Contact_Form_Search to us 
        // and contain the search criteria (parameters)
        // note that the default action is basic
        $result = $this->_query->searchQuery($offset, $rowCount, $sort,
                                             false, $includeContactIds );

        // process the result of the query
        $rows = array( );

        $mask = CRM_Core_Action::mask( CRM_Core_Permission::getPermission( ) );

        $mapMask = $mask & 4095; // mask value to hide map link if there are not lat/long
        
        $gc = CRM_Core_SelectValues::groupContactStatus();

        // Dirty session hack to get at the context 
        $session =& CRM_Core_Session::singleton();
        $context = $session->get('context', 'CRM_Contact_Controller_Search');

        if ($this->_ufGroupID ) {
            
            // CRM_Core_Error::debug( 'p', self::$_properties );
            require_once 'CRM/Core/PseudoConstant.php';
            $locationTypes = CRM_Core_PseudoConstant::locationType( );
            
            $names = array( );
            static $skipFields = array( 'group', 'tag' ); 
            foreach ( $this->_fields as $key => $field ) {
                if ( $field['in_selector'] && 
                     ! in_array( $key, $skipFields ) ) { 
                    if ( strpos( $key, '-' ) !== false ) {
                        list( $fieldName, $id, $type ) = explode( '-', $key );

                        if ($id == 'Primary') { //fix to display default primary location
                            require_once "CRM/Core/BAO/LocationType.php";
                            $defaultLocation =& CRM_Core_BAO_LocationType::getDefault();
                            $id = $defaultLocation->id;
                        }

                        $locationTypeName = CRM_Utils_Array::value( $id, $locationTypes );
                        if ( ! $locationTypeName ) {
                            continue;
                        }
                        
                        if ( in_array( $fieldName, array( 'phone', 'im', 'email' ) ) ) { 
                            if ( $type ) {
                                $names[] = "{$locationTypeName}-{$fieldName}-{$type}";
                            } else {
                                $names[] = "{$locationTypeName}-{$fieldName}-1";
                            }
                        } else {
                            $names[] = "{$locationTypeName}-{$fieldName}";
                        }
                    } else {
                        $names[] = $field['name'];
                    }
                }
            }
            
            $names[] =  "status";
            
        } else if ( ! empty( $this->_returnProperties ) ) {
            $names =& self::makeProperties( $this->_returnProperties );
        } else {
            $names = self::$_properties;
        }

        //hack for student data (checkboxs)
        $multipleSelectFields = null;
        if ( CRM_Core_Permission::access( 'Quest' ) ) {
            require_once 'CRM/Quest/BAO/Student.php';
            $multipleSelectFields = CRM_Quest_BAO_Student::$multipleSelectFields;
        }

        require_once 'CRM/Core/OptionGroup.php';
        $links =& self::links( );

        while ($result->fetch()) {
            $row = array();

            // the columns we are interested in
            foreach ($names as $property) {
                if ( $property == 'status' ) {
                    continue;
                }
                
                if ( $cfID = CRM_Core_BAO_CustomField::getKeyID($property)) {
                    $row[$property] = CRM_Core_BAO_CustomField::getDisplayValue( $result->$property, $cfID, $this->_options );
                }  else if ( $multipleSelectFields &&
                             array_key_exists($property, $multipleSelectFields ) ) { //fix to display student checkboxes
                    $key = $property;
                    $paramsNew = array($key => $result->$property );
                    if ( $key == 'test_tutoring') {
                        $name = array( $key => array('newName' => $key ,'groupName' => 'test' ));
                    } else {
                        $name = array( $key => array('newName' => $key ,'groupName' => $key ));
                    }
                    CRM_Core_OptionGroup::lookupValues( $paramsNew, $name, false );
                    $row[$key] = $paramsNew[$key]; 
                } else {
                    $row[$property] = $result->$property;
                }

                if ( ! empty( $result->$property ) ) {
                    $empty = false;
                }
            }

            if (!empty ($result->postal_code_suffix)) {
                $row['postal_code'] .= "-" . $result->postal_code_suffix;
            }
            
            
            if ($output != CRM_Core_Selector_Controller::EXPORT ||
                $context == 'smog') {
                if (empty($result->status)) {
                    $row['status'] = ts('Smart');
                } else {
                    $row['status'] = $gc[$result->status];
                }
            }
            
            if ( $output != CRM_Core_Selector_Controller::EXPORT && $output != CRM_Core_Selector_Controller::SCREEN ) {
                $row['checkbox'] = CRM_Core_Form::CB_PREFIX . $result->contact_id;

                if ( is_numeric( CRM_Utils_Array::value( 'geo_code_1', $row ) ) ) {
                    $row['action']   = CRM_Core_Action::formLink( $links, $mask   , array( 'id' => $result->contact_id ) );
                } else {
                    $row['action']   = CRM_Core_Action::formLink( $links, $mapMask, array( 'id' => $result->contact_id ) );
                }

                // allow components to add more actions
                CRM_Core_Component::searchAction( $row, $result->contact_id );

                $contact_type    = '<img src="' . $config->resourceBase . 'i/contact_';
                switch ($result->contact_type) {
                case 'Individual' :
                    $contact_type .= 'ind.gif" alt="' . ts('Individual') . '" />';
                    break;
                case 'Household' :
                    $contact_type .= 'house.png" alt="' . ts('Household') . '" height="16" width="16" />';
                    break;
                case 'Organization' :
                    $contact_type .= 'org.gif" alt="' . ts('Organization') . '" height="16" width="18" />';
                    break;
                }
                $row['contact_type'] = $contact_type;
                $row['contact_id'  ] = $result->contact_id;
                $row['sort_name'   ] = $result->sort_name;
                
            }
        
            if ( ! $empty ) {
                $rows[] = $row;
            }
        }
        //print_r($rows);
        return $rows;
    }
   
    /**
     * Given the current formValues, gets the query in local
     * language
     *
     * @param  array(reference)   $formValues   submitted formValues
     *
     * @return array              $qill         which contains an array of strings
     * @access public
     */
  
    // the current internationalisation is bad, but should more or less work
    // for most of "European" languages
    public function getQILL( )
    {
        return $this->_query->qill( );
    }

    /**
     * name of export file.
     *
     * @param string $output type of output
     * @return string name of the file
     */
    function getExportFileName( $output = 'csv') {
        return ts('CiviCRM Contact Search');
    }

    /**
     * get colunmn headers for search selector
     *
     *
     * @return array $_columnHeaders
     * @access private
     */
    private static function &_getColumnHeaders() 
    {
        if ( ! isset( self::$_columnHeaders ) )
        {
            self::$_columnHeaders = array(
                                          array('desc' => ts('Contact Type') ),
                                          array(
                                                'name'      => ts('Name'),
                                                'sort'      => 'sort_name',
                                                'direction' => CRM_Utils_Sort::ASCENDING,
                                                ),
                                          array('name' => ts('Address') ),
                                          array(
                                                'name'      => ts('City'),
                                                'sort'      => 'city',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('State'),
                                                'sort'      => 'state_province',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Postal'),
                                                'sort'      => 'postal_code',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Country'),
                                                'sort'      => 'country',
                                              'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Email'),
                                                'sort'      => 'email',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array('name' => ts('Phone') ),
                                          array('desc' => ts('Actions') ),
                                          );
        }
        return self::$_columnHeaders;
    }
    
    function &getQuery( ) {
        return $this->_query;
    }

    function &makeProperties( &$returnProperties ) {
        $properties = array( );
        foreach ( $returnProperties as $name => $value ) {
            if ( $name != 'location' ) {
                $properties[] = $name;
            } else {
                // extract all the location stuff
                foreach ( $value as $n => $v ) {
                    foreach ( $v as $n1 => $v1 ) {
                        if ( ! strpos( '_id', $n1 ) && $n1 != 'location_type' ) {
                            $properties[] = "{$n}-{$n1}";
                        }
                    }
                }
            }
        }
        return $properties;
    }

}//end of class

?>
