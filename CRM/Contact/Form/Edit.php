<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
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
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org.  If you have questions about the       |
 | Affero General Public License or the licensing  of CiviCRM,        |
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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/SelectValues.php';

/**
 * This class generates form components generic to all the contact types.
 * 
 * It delegates the work to lower level subclasses and integrates the changes
 * back in. It also uses a lot of functionality with the CRM API's, so any change
 * made here could potentially affect the API etc. Be careful, be aware, use unit tests.
 *
 */
class CRM_Contact_Form_Edit extends CRM_Core_Form
{
    /**
     * The contact type of the form
     *
     * @var string
     */
    protected $_contactType;

    /**
     * The contact type of the form
     *
     * @var string
     */
    protected $_contactSubType;

    /**
     * The contact id, used when editing the form
     *
     * @var int
     */
    public $_contactId;

    /**
     * the default group id passed in via the url
     *
     * @var int
     */
    protected $_gid;

    /**
     * the default tag id passed in via the url
     *
     * @var int
     */
    protected $_tid;
    
    /**
     * the group tree data
     *
     * @var array
     */
    protected $_groupTree;    

    /**
     * what blocks should we show and hide.
     *
     * @var CRM_Core_ShowHideBlocks
     */
    protected $_showHide;

    /**
     * name of de-dupe button
     *
     * @var string
     * @access protected
     */
    protected $_dedupeButtonName;

    /**
     * name of optional save duplicate button
     *
     * @var string
     * @access protected
     */
    protected $_duplicateButtonName;

    protected $_maxLocationBlocks = 0;

    protected $_editOptions = array( );

    protected $_showCommBlock = true;

    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) 
    {
        // reset action from the session
        $this->_action              = CRM_Utils_Request::retrieve('action', 'String', 
                                                                  $this, false, 'add' );
        
        $this->_dedupeButtonName    = $this->getButtonName( 'refresh', 'dedupe'    );
        $this->_duplicateButtonName = $this->getButtonName( 'next'   , 'duplicate' );
    
        // find the system config related location blocks
        require_once 'CRM/Core/BAO/Preferences.php';
        $this->_maxLocationBlocks = CRM_Core_BAO_Preferences::value( 'location_count' );

        $this->_editOptions  = CRM_Core_BAO_Preferences::valueOptions( 'contact_edit_options' );

        $configItems = array( '_showCommBlock'     => 'Communication Preferences',
                              '_showDemographics'  => 'Demographics',
                              '_showTagsAndGroups' => 'Tags and Groups',
                              '_showNotes'         => 'Notes' );

        foreach ( $configItems as $c => $t ) {
            $this->$c = $this->_editOptions[$t];
            $this->assign( substr( $c, 1 ), $this->$c );
        }

        if ( $this->_action == CRM_Core_Action::ADD ) {
            $this->_contactType = CRM_Utils_Request::retrieve( 'ct', 'String',
                                                               $this, true, null, 'REQUEST' );
            $this->_contactSubType = CRM_Utils_Request::retrieve( 'cst','String', 
                                                                  CRM_Core_DAO::$_nullObject,
                                                                  false,null,'GET' );
            $this->_gid = CRM_Utils_Request::retrieve( 'gid', 'Integer',
                                                       CRM_Core_DAO::$_nullObject,
                                                       false, null, 'GET' );
            $this->_tid = CRM_Utils_Request::retrieve( 'tid', 'Integer',
                                                       CRM_Core_DAO::$_nullObject,
                                                       false, null, 'GET' );
            if ( $this->_contactSubType ) {
                CRM_Utils_System::setTitle( ts( 'New %1', array(1 => $this->_contactSubType ) ) );
            } else {
                CRM_Utils_System::setTitle( ts( 'New %1', array(1 => $this->_contactType ) ) );
            }

            $this->_contactId = null;
        } else {
            // this is update mode, first get the id from the session
            // else get it from the REQUEST
            $ids = $this->get('ids');
            $this->_contactId = CRM_Utils_Array::value( 'contact', $ids );
          
            if ( ! $this->_contactId ) {
                $this->_contactId   = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this, true );
            }

            if ( $this->_contactId ) {
                require_once 'CRM/Contact/BAO/Contact.php';
                $contact =& new CRM_Contact_DAO_Contact( );
                $contact->id = $this->_contactId;
                if ( ! $contact->find( true ) ) {
                    CRM_Core_Error::statusBounce( ts('contact does not exist: %1', array(1 => $this->_contactId)) );
                }
                $this->_contactType = $contact->contact_type;
                $this->_contactSubType = $contact->contact_sub_type;

                // check for permissions
                if ( ! CRM_Contact_BAO_Contact::permissionedContact( $this->_contactId, CRM_Core_Permission::EDIT ) ) {
                    CRM_Core_Error::statusBounce( ts('You do not have the necessary permission to edit this contact.') );
                }

                list( $displayName, $contactImage ) = CRM_Contact_BAO_Contact::getDisplayAndImage( $this->_contactId );
                CRM_Utils_System::setTitle( $contactImage . ' ' . $displayName ); 

                //get the no of locations for the contact
                //TO DO: commented due to schema changes
                //$this->_maxLocationBlocks = CRM_Contact_BAO_Contact::getContactLocations( $this->_contactId );
                
                return;
            }

            CRM_Core_Error::statusBounce( ts('Could not get a contact_id and/or contact_type') );
        }
    }

    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) 
    {
        $defaults = array( );
        $params   = array( );

        $config =& CRM_Core_Config::singleton( );

        //TO DO: commented because of schema changes
        if ( $this->_action & CRM_Core_Action::ADD ) {
            if ( $this->_showTagsAndGroups ) {
                // set group and tag defaults if any
                if ( $this->_gid ) {
                    $defaults['group'][$this->_gid] = 1;
                }
                if ( $this->_tid ) {
                    $defaults['tag'][$this->_tid] = 1;
                }
            }

            if ( $this->_maxLocationBlocks >= 1 ) {
                // set the is_primary location for the first location
                $defaults['location']    = array( );
                
                $locationTypeKeys = array_filter(array_keys( CRM_Core_PseudoConstant::locationType() ), 'is_int' );
                sort( $locationTypeKeys );
                
                // also set the location types for each location block
                for ( $i = 0; $i < $this->_maxLocationBlocks; $i++ ) {
                    $defaults['location'][$i+1] = array( );
                    if ( $i == 0 ) {
                        require_once 'CRM/Core/BAO/LocationType.php';
                        $defaultLocation =& new CRM_Core_BAO_LocationType();
                        $locationType = $defaultLocation->getDefault();
                        $defaults['location'][$i+1]['location_type_id'] = $locationType->id;
                       
                    } else {
                        $defaults['location'][$i+1]['location_type_id'] = $locationTypeKeys[$i];
                    }
                    $defaults['location'][$i+1]['address'] = array( );
                    if( $config->defaultContactCountry ) {
                        $defaults['location'][$i+1]['address']['country_id'] = $config->defaultContactCountry;
                    }
                }
                $defaults['location'][1]['is_primary'] = true;
            }
        } else { 
            // this is update mode
            // get values from contact table
            $params['id'] = $params['contact_id'] = $this->_contactId;
            $ids = array();
            $contact = CRM_Contact_BAO_Contact::retrieve( $params, $defaults, $ids );
            $this->set( 'ids', $ids );
            
            $locationExists = array( );

            foreach( $contact->location as $loc) {
                $locationExists[] = $loc['location_type_id'];
            }
            $this->assign( 'locationExists' , $locationExists );

            $this->assign( 'contactId' , $this->_contactId );
            // also set contact_type, since this is used in showHide routines 
            // to decide whether to display certain blocks (demographics)
            $this->_contactType = CRM_Utils_Array::value( 'contact_type', $defaults );

            if ( $this->_showTagsAndGroups ) {
                // set the group and tag ids
                CRM_Contact_Form_GroupTag::setDefaults( $this->_contactId,                      
                                                        $defaults, 
                                                        CRM_Contact_Form_GroupTag::ALL );
            }
            
            // set the default for 'use_household_address' checkbox and Select-Household.
            if ( CRM_Utils_Array::value('mail_to_household_id', $defaults) ) {
                $defaults['use_household_address'] = true;
                $domainID      =  CRM_Core_Config::domainID( );   
                $query         =  "
SELECT CONCAT_WS( ', ', household_name, LEFT( street_address, 25 ) , city ) 'shared_name', 
civicrm_household.contact_id 'id'
FROM civicrm_household
LEFT JOIN civicrm_location ON civicrm_location.entity_id={$defaults['mail_to_household_id']} 
AND civicrm_location.is_primary=1 
AND civicrm_location.entity_table='civicrm_contact'
LEFT JOIN civicrm_address ON civicrm_address.location_id=civicrm_location.id
where civicrm_household.contact_id={$defaults['mail_to_household_id']}";
                
                $nullArray = array( );
                $dao = CRM_Core_DAO::executeQuery( $query, $nullArray );
                $dao->fetch( );
                $this->assign('defaultSharedHousehold', trim( $dao->shared_name ));
            }
        }

        if ( ! empty( $_POST ) ) {
            $this->setShowHide( $_POST, true );
        } else {
            if ( $this->_action & CRM_Core_Action::ADD ) {
                $this->setShowHide( $defaults, false );
            } else {
                $this->setShowHide( $defaults, true );
            }
        }

        // do we need inactive options ?
        if ($this->_action & ( CRM_Core_Action::VIEW | CRM_Core_Action::BROWSE ) ) {
            $inactiveNeeded = true;
            $viewMode = true;
        } else {
            $viewMode = false;
            $inactiveNeeded = false;
        }

        if ( $this->_action & CRM_Core_Action::UPDATE ) {
            $rel = CRM_Contact_BAO_Relationship::getRelationship($this->_contactId);
            krsort($rel);
            foreach ($rel as $key => $value) {
                if ($value['relation'] == 'Employee of') {
                    $defaults['current_employer'] =  $value['name'];
                    break;
                }
            }
        }

        //set defaults for country-state dojo widget
        if ( ! empty ( $defaults['location'] ) ) {
            $countries      =& CRM_Core_PseudoConstant::country( );
            $stateProvinces =& CRM_Core_PseudoConstant::stateProvince( false, false );
            
            foreach ( $defaults['location'] as $key => $value ) {
                if ( isset( $value['address'] ) ) {

                    // hack, check if we have created a country element
                    if ( isset( $this->_elementIndex[ "location[$key][address][country_id]" ] ) ) {
                        // hack, check if we have created a country element
                        $countryValue = $this->getElementValue( "location[$key][address][country_id]" );
                        if ( $countryValue ) {
                            if ( ! is_numeric( $countryValue ) ) {
                                $this->assign( "country_{$key}_value", 
                                               $this->getElementValue( "location[$key][address][country_id]" ) );
                                $this->assign( "country_{$key}_id", 
                                               $this->getElementValue( "location[$key][address][country_id]" ) );
                            } else {
                                $this->assign( "country_{$key}_value",  $countries[$countryValue] );
                                $this->assign( "country_{$key}_id"   ,  $countryValue );
                            }
                        } else if ( isset($value['address']['country_id']) ) {
                            $countryId = $value['address']['country_id'];
                            if ( $countryId ) {
                                $this->assign( "country_{$key}_value",  CRM_Utils_Array::value($countryId,$countries) );
                                $this->assign( "country_{$key}_id"   ,  $countryId );
                            }
                        }
                    }
                    
                    if ( isset( $this->_elementIndex[ "location[$key][address][state_province_id]" ] ) ) {
                        $stateValue = null;
                        $stateValue = $this->getElementValue( "location[$key][address][state_province_id]" );
                        
                        if ( $stateValue ) {
                            if ( ! is_numeric( $stateValue ) ) {
                                $this->assign( "state_province_{$key}_value", 
                                               $this->getElementValue( "location[$key][address][state_province_id]" ) );
                                $this->assign( "state_province_{$key}_id", 
                                               $this->getElementValue( "location[$key][address][state_province_id]" ) );
                            } else {
                                $this->assign( "state_province_{$key}_value",  $stateProvinces[$stateValue] );
                                $this->assign( "state_province_{$key}_id"   ,  $stateValue );
                            }
                        } else  if ( isset($value['address']['state_province_id']) ) {
                            $stateProvinceId = $value['address']['state_province_id'];
                            if ( $stateProvinceId ) {
                                $this->assign( "state_province_{$key}_value",  $stateProvinces[$stateProvinceId] );
                                $this->assign( "state_province_{$key}_id"   ,  $stateProvinceId );
                            }
                        }
                    }
                    
                    if ( isset( $value['address']['display']) ) {
                        $this->assign( "location_{$key}_address_display", 
                                       str_replace("\n", "<br/>", $value['address']['display']) );
                    }
                }
            }
        }

        //DO TO: comment because of schema changes
        CRM_Core_BAO_CustomGroup::setDefaults( $this->_groupTree, $defaults, $viewMode, $inactiveNeeded );
        return $defaults;
    }

    /**
     * Fix what blocks to show/hide based on the default values set
     *
     * @param array   $defaults the array of default values
     * @param boolean $force    should we set show hide based on input defaults
     *
     * @return void
     */
    function setShowHide( &$defaults, $force ) 
    {
        $this->_showHide =& new CRM_Core_ShowHideBlocks( );

        if ( $this->_showCommBlock ) {
            $this->_showHide->addShow( 'commPrefs' );
        }

        if ( $this->_showDemographics &&
             $this->_contactType == 'Individual' ) {
            $this->_showHide->addShow( 'id_demographics_show' );
            $this->_showHide->addHide( 'id_demographics' );
        }

        // first do the defaults showing
        $config =& CRM_Core_Config::singleton( );
        CRM_Contact_Form_Location::setShowHideDefaults( $this->_showHide,
                                                        $this->_maxLocationBlocks );
 
        if ( $this->_showNotes && 
             ( $this->_action & CRM_Core_Action::ADD ) ) {
            // notes are only included in the template for New Contact
            $this->_showHide->addShow( 'id_notes_show' );
            $this->_showHide->addHide( 'id_notes' );
        }

        if ( $this->_showTagsAndGroups ) {
            //add group and tags
            $contactGroup = $contactTag = array( );
            if ($this->_contactId) {
                $contactGroup =& CRM_Contact_BAO_GroupContact::getContactGroup( $this->_contactId, 'Added' );
                $contactTag   =& CRM_Core_BAO_EntityTag::getTag($this->_contactId);
            }
            
            if ( empty($contactGroup) || empty($contactTag) ) {
                $this->_showHide->addShow( 'group_show' );
                $this->_showHide->addHide( 'group' );
            } else {
                $this->_showHide->addShow( 'group' );
                $this->_showHide->addHide( 'group_show' );
            }
        }

        // is there any demographic data?
        if ( $this->_showDemographics ) {
            if ( CRM_Utils_Array::value( 'gender_id'  , $defaults ) ||
                 CRM_Utils_Array::value( 'is_deceased', $defaults ) ||
                 CRM_Utils_Array::value( 'birth_date' , $defaults ) ) {
                $this->_showHide->addShow( 'id_demographics' );
                $this->_showHide->addHide( 'id_demographics_show' );
            }
        }

        if ( $force ) {
            $locationDefaults = CRM_Utils_Array::value( 'location', $defaults );
            $config =& CRM_Core_Config::singleton( );
            CRM_Contact_Form_Location::updateShowHide( $this->_showHide,
                                                       $locationDefaults,
                                                       $this->_maxLocationBlocks );
        }
        
        $this->_showHide->addToTemplate( );
    }

    /**
     * This function is used to add the rules (mainly global rules) for form.
     * All local rules are added near the element
     *
     * @return None
     * @access public
     * @see valid_date
     */
    function addRules( )
    {
        $this->addFormRule( array( 'CRM_Contact_Form_' . $this->_contactType, 'formRule' ), $this->_contactId );
    }

    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        require_once 'CRM/Contact/Form/Location.php';

        // assign a few constants used by all display elements
        // we can obsolete this when smarty can access class constans directly
        $config =& CRM_Core_Config::singleton( );
        $this->assign( 'locationCount', $this->_maxLocationBlocks + 1 );
        $this->assign( 'blockCount'   , CRM_Contact_Form_Location::BLOCKS + 1 );
        $this->assign( 'contact_type' , $this->_contactType );
        
        require_once(str_replace('_', DIRECTORY_SEPARATOR, "CRM_Contact_Form_" . $this->_contactType) . ".php");
        eval( 'CRM_Contact_Form_' . $this->_contactType . '::buildQuickForm( $this, $this->_action );' );

        // add the communications block
        if ( $this->_showCommBlock ) {
            self::buildCommunicationBlock($this);
        }

        //hack the address sequence so that state province always comes after country
        $addressSequence = $config->addressSequence();
        $key = array_search( 'country', $addressSequence);
        unset($addressSequence[$key]);

        $key = array_search( 'state_province', $addressSequence);
        unset($addressSequence[$key]);

        $addressSequence = array_merge( $addressSequence, array ( 'country', 'state_province' ) );
        $this->assign( 'addressSequence', $addressSequence );

        /* Entering the compact location engine */ 
        $location =& CRM_Contact_Form_Location::buildLocationBlock( $this, $this->_maxLocationBlocks );
        /* End of locations */
        
        // add note block
        if ( $this->_showNotes &&
             ( $this->_action & CRM_Core_Action::ADD ) ) {
            require_once 'CRM/Contact/Form/Note.php';
            $note =& CRM_Contact_Form_Note::buildNoteBlock($this);
        }

        //add tags and groups block
        require_once 'CRM/Contact/Form/GroupTag.php';
        $groupTag =& CRM_Contact_Form_GroupTag::buildGroupTagBlock($this, $this->_contactId, CRM_Contact_Form_GroupTag::ALL );

        //Custom Group Inline Edit form
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $this->_groupTree =& CRM_Core_BAO_CustomGroup::getTree($this->_contactType, $this->_contactId,0,$this->_contactSubType);
        CRM_Core_BAO_CustomGroup::buildQuickForm( $this, $this->_groupTree, 'showBlocks1', 'hideBlocks1' );
        
        if ( $this->_showNotes ) {
            CRM_Core_ShowHideBlocks::links( $this, 'notes', '' , '' );
        }

        // add the dedupe button
        $this->addElement('submit', 
                          $this->_dedupeButtonName,
                          ts( 'Check for Matching Contact(s)' ) );
        $this->addElement('submit', 
                          $this->_duplicateButtonName,
                          ts( 'Save Matching Contact' ) );
        $this->addElement('submit', 
                          $this->getButtonName( 'next'   , 'sharedHouseholdDuplicate' ),
                          ts( 'Save With Duplicate Household' ) );

        $session = & CRM_Core_Session::singleton( );
        $uploadNames = $session->get( 'uploadNames' );
        if ( is_array( $uploadNames ) && ! empty ( $uploadNames ) ) {
            $buttonType = 'upload';
        } else {
            $buttonType = 'next';
        }

        $this->addButtons( array(
                                 array ( 'type'      => $buttonType,
                                         'name'      => ts('Save'),
                                         'subName'   => 'view',
                                         'isDefault' => true   ),
                                 array ( 'type'      => $buttonType,
                                         'name'      => ts('Save and New'),
                                         'subName'   => 'new' ),
                                 array ( 'type'       => 'cancel',
                                         'name'      => ts('Cancel') ) ) );
    }

       
    /**
     * Form submission of new/edit contact is processed.
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        // check if dedupe button, if so return.
        $buttonName = $this->controller->getButtonName( );
        if ( $buttonName == $this->_dedupeButtonName ) {
            return;
        }
        
        // store the submitted values in an array
        $params = $this->controller->exportValues( $this->_name );

	    $params['contact_type'] = $this->_contactType;

        if ( $this->_contactId ) {
            $params['contact_id'] = $this->_contactId;
        }

        if ( $this->_showDemographics ) {
            if( ! isset( $params['is_deceased'] ) || $params['is_deceased'] != 1 ) { 
                $params['deceased_date'] = null;
            }
        }

        // action is taken depending upon the mode
        $ids = array();
        require_once 'CRM/Utils/Hook.php';
        if ($this->_action & CRM_Core_Action::UPDATE) {
            // if update get all the valid database ids
            // from the session
            $ids = $this->get('ids');
            CRM_Utils_Hook::pre( 'edit', $params['contact_type'], $ids['contact'], $params );
        } else {
            CRM_Utils_Hook::pre( 'create', $params['contact_type'], null, $params );
        }

        // format custom data
        // get mime type of the uploaded file
        if ( !empty($_FILES) ) {
            foreach ( $_FILES as $key => $value) {
                $files = array( );
                if ( $params[$key] ) {
                    $files['name'] = $params[$key];
                }
                if ( $value['type'] ) {
                    $files['type'] = $value['type']; 
                }
                $params[$key] = $files;
            }
        }

        $customData = array( );
        foreach ( $params as $key => $value ) {
            if ( $customFieldId = CRM_Core_BAO_CustomField::getKeyID($key) ) {
                CRM_Core_BAO_CustomField::formatCustomField( $customFieldId, $customData,
                                                             $value, $params['contact_type'], null, $this->_contactId);
            }
        }
    
        //special case to handle if all checkboxes are unchecked
        $customFields = CRM_Core_BAO_CustomField::getFields( $params['contact_type'] );
        
        if ( !empty($customFields) ) {
            foreach ( $customFields as $k => $val ) {
                if ( in_array ( $val[3], array ('CheckBox','Multi-Select') ) &&
                     ! CRM_Utils_Array::value( $k, $customData ) ) {
                    CRM_Core_BAO_CustomField::formatCustomField( $k, $customData,
                                                                 '', $params['contact_type'], null, $this->_contactId);
                }
            }
        }
    
        if (! empty($customData) ) {
            $params['custom'] = $customData;
        }

        if ( $this->_showCommBlock ) {
            // this is a chekbox, so mark false if we dont get a POST value
            $params['is_opt_out'] = CRM_Utils_Array::value( 'is_opt_out', $params, false );
        }
        
        // copy household address, if use_household_address option (for individual form) is checked
        if ( $this->_contactType == 'Individual' ) {
            if ( $params['use_household_address'] ) {
                if ( !$params['shared_option'] && $params['create_household'] ) {
                    CRM_Contact_Form_Individual::createSharedHousehold( $params );
                } elseif ( $params['shared_option'] ) {
                    CRM_Contact_Form_Individual::copyHouseholdAddress( $params );
                }
            } else {
                $params['mail_to_household_id'] = false;
            }
        }
        
        require_once 'CRM/Contact/BAO/Contact.php';
        $contact =& CRM_Contact_BAO_Contact::create($params, $ids, $this->_maxLocationBlocks, true, false );

        // add/edit/delete the relation of individual with household, if use-household-address option is checked/unchecked.
        if ( $this->_contactType == 'Individual' ) {
            CRM_Contact_Form_Individual::handleSharedRelation( $contact->id, $params );
        }
        
        if ( $this->_contactType == 'Household' && ( $this->_action & CRM_Core_Action::UPDATE ) ) {
            //TO DO: commented because of schema changes
            //CRM_Contact_Form_Household::synchronizeIndividualAddresses( $contact->id );
        }

        //add contact to gruoup
        require_once 'CRM/Contact/BAO/GroupContact.php';
        CRM_Contact_BAO_GroupContact::create( $params['group'], $params['contact_id'] );

        //add contact to tags
        require_once 'CRM/Core/BAO/EntityTag.php';
        CRM_Core_BAO_EntityTag::create( $params['tag'], $params['contact_id'] );
        
        
        // here we replace the user context with the url to view this contact
        $session =& CRM_Core_Session::singleton( );
        CRM_Core_Session::setStatus(ts('Your %1 contact record has been saved.', array(1 => $contact->contact_type_display)));

        $buttonName = $this->controller->getButtonName( );
        if ( ($buttonName == $this->getButtonName( 'next', 'new' ) ) ||
             ($buttonName == $this->getButtonName( 'upload', 'new' ) ) ) {
            require_once 'CRM/Utils/Recent.php';

            // add the recently viewed contact
            list( $displayName, $contactImage ) = CRM_Contact_BAO_Contact::getDisplayAndImage( $contact->id );
            CRM_Utils_Recent::add( $displayName,
                                   CRM_Utils_System::url( 'civicrm/contact/view', 'reset=1&cid=' . $contact->id ),
                                   $contactImage,
                                   $contact->id );
            $session->replaceUserContext(CRM_Utils_System::url('civicrm/contact/add', 'reset=1&ct=' . $contact->contact_type ) );
        } else {
            $session->replaceUserContext(CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid=' . $contact->id));
        }

        //add relationship for the contact
        if ( isset( $params['current_employer'] ) && $params['current_employer'] ) {
            CRM_Contact_BAO_Contact::makeCurrentEmployerRelationship($contact->id, 
                                                                     $params['current_employer']);

        }
    
        // now invoke the post hook
        if ($this->_action & CRM_Core_Action::UPDATE) {
            CRM_Utils_Hook::post( 'edit', $params['contact_type'], $contact->id, $contact );
        } else {
            CRM_Utils_Hook::post( 'create', $params['contact_type'], $contact->id, $contact );
        }
    }

    /**
     * Create communication preferences block for the contact.
     *
     * @param object $form - CRM_Core_Form (or it's subclass)
     * @return none
     *
     * @access public
     * @static
     */
    public static function buildCommunicationBlock(&$form)
    {
        // since the pcm - preferred comminication method is logically
        // grouped hence we'll use groups of HTML_QuickForm

        $privacy = array();

        // checkboxes for DO NOT phone, email, mail
        // we take labels from SelectValues
        $t = CRM_Core_SelectValues::privacy();
        $privacy[] = HTML_QuickForm::createElement('advcheckbox', 'do_not_phone', null, $t['do_not_phone']);
        $privacy[] = HTML_QuickForm::createElement('advcheckbox', 'do_not_email', null, $t['do_not_email']);
        $privacy[] = HTML_QuickForm::createElement('advcheckbox', 'do_not_mail' , null, $t['do_not_mail']);
        $privacy[] = HTML_QuickForm::createElement('advcheckbox', 'do_not_trade', null, $t['do_not_trade']);

        $form->addGroup($privacy, 'privacy', ts('Privacy'), '&nbsp;');

        // preferred communication method 
        require_once 'CRM/Core/PseudoConstant.php';
        $comm = CRM_Core_PseudoConstant::pcm(); 

        $commPreff = array();
        foreach ( $comm as $k => $v ) {
            $commPreff[] = HTML_QuickForm::createElement('advcheckbox', $k , null, $v );
        }
        $form->addGroup($commPreff, 'preferred_communication_method', ts('Method'));

        $form->add('select', 'preferred_mail_format', ts('Email Format'), CRM_Core_SelectValues::pmf());

        $form->add('checkbox', 'is_opt_out', ts( 'User Opt Out' ) );
    }


    /**
     * global validation rules for the form
     *
     * @param array $fields posted values of the form
     * @param array $errors list of errors to be posted back to the form
     *
     * @return void
     * @static
     * @access public
     */
    static function formRule(&$fields, &$errors)
    {
        $primaryOpenId = null;

        // make sure that at least one field is marked is_primary
        if ( array_key_exists( 'location', $fields ) && is_array( $fields['location'] ) ) {
            $locationKeys = array_keys( $fields['location']);
            $isPrimary  = false;
            $dataExists = false;
            $locTypeId = false;
            foreach ( $locationKeys as $locationId ) {
                if ( array_key_exists( 'is_primary', $fields['location'][$locationId] ) ) {
                    if ( $fields['location'][$locationId]['is_primary'] ) {
                        if ( $isPrimary ) {
                            $errors["location[$locationId][is_primary]"] = ts('Only one location can be marked as primary.');
                        }
                        $isPrimary = true;
                    }

                    // only harvest OpenID from the primary locations
                    if ( array_key_exists( 'openid', $fields['location'][$locationId] ) &&
                         is_array( $fields['location'][$locationId]['openid'] )         &&
                         empty( $primaryOpenId ) ) {
                        foreach ( $fields['location'][$locationId]['openid'] as $idx => $openId ) {
                            if ( array_key_exists( 'openid', $openId ) ) {
                                $primaryOpenId = $openId['openid'];
                                break;
                            }
                        }
                    }
                }
                if ( self::locationDataExists( $fields['location'][$locationId] ) ) {
                    $dataExists = true;
                    if ( ! CRM_Utils_Array::value( 'location_type_id', $fields['location'][$locationId] ) ) {
                        $errors["location[$locationId][location_type_id]"] = ts('The Location Type should be set if there is any location information');
                    }
                }
                require_once 'CRM/Core/BAO/Location.php';
                //  for checking duplicate location type.
                //print "\$ids:";
                //print_r($ids);
                //print "<br/>";
                if (CRM_Core_BAO_Location::dataExists( $fields, $locationId, $ids )) {
                    if ($locTypeId == $fields['location'][$locationId]['location_type_id']) {
                        $errors["location[$locationId][location_type_id]"] = ts('Two locations cannot have same location type');
                    }
                    $locTypeId = $fields['location'][$locationId]['location_type_id'];
                }
            }

            if ( $dataExists && ! $isPrimary ) {
                $errors["location[1][is_primary]"] = ts('One location should be marked as primary.');
            }
        }
        return $primaryOpenId;
    }

    /**
     * is there any real significant data in the hierarchical location array
     *
     * @param array $fields the hierarchical value representation of this location
     *
     * @return boolean true if data exists, false otherwise
     * @static
     * @access public
     */
    static function locationDataExists( &$fields ) {
        static $skipFields = array( 'location_type_id', 'is_primary', 'phone_type', 'provider_id' );
        foreach ( $fields as $name => $value ) {
            $skipField = false;
            foreach ( $skipFields as $skip ) {
                if ( strpos( "[$skip]", $name ) !== false ) {
                    $skipField = true;
                    break;
                }
            }
            if ( $skipField ) {
                continue;
            }
            if ( is_array( $value ) ) {
                if ( self::locationDataExists( $value ) ) {
                    return true;
                }
            } else {
                if ( ! empty( $value ) ) {
                    return true;
                }
            }
        }
        return false;
    }

}

?>
