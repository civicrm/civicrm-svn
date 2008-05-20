<?PHP

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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Contact/Form/Task.php';
require_once 'CRM/Event/PseudoConstant.php';
require_once 'CRM/Event/Form/EventFees.php';
require_once 'CRM/Custom/Form/CustomData.php';
require_once 'CRM/Core/BAO/CustomGroup.php';
require_once 'CRM/Contact/BAO/Contact/Location.php';

/**
 * This class generates form components for processing a participation 
 * in an event
 */
class CRM_Event_Form_Participant extends CRM_Contact_Form_Task
{
    /**
     * the values for the contribution db object
     *
     * @var array
     * @protected
     */
    public $_values;

    /**
     * Price Set ID, if the new price set method is used
     *
     * @var int
     * @protected
     */
    public $_priceSetId;

    /**
     * Array of fields for the price set
     *
     * @var array
     * @protected
     */
    public $_priceSet;

    /**
     * the id of the participation that we are proceessing
     *
     * @var int
     * @protected
     */
    public $_id;
    
    /**
     * the id of the note 
     *
     * @var int
     * @protected
     */
    protected $_noteId = null;

    /**
     * the id of the contact associated with this participation
     *
     * @var int
     * @protected
     */
    public $_contactID;
    
    /**
     * array of event values
     * 
     * @var array
     * @protected
     */
    protected $_event;

    /**
     * Are we operating in "single mode", i.e. adding / editing only
     * one participant record, or is this a batch add operation
     *
     * @var boolean
     */
    public $_single = false;

    /**
     * If event is paid or unpaid
     */
    public $_isPaidEvent;

    /**
     * Page action
     */
    public $_action;
    
    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    public function preProcess()  
    {
        $this->_showFeeBlock = CRM_Utils_Array::value( 'eventId', $_GET );
        $this->assign( 'showFeeBlock', false );
        if ( $this->_showFeeBlock ) {
            $this->assign( 'showFeeBlock', true );
            $this->assign( 'paid', true );
            return CRM_Event_Form_EventFees::preProcess( $this );
        }
        
        //custom data related code
        $this->_cdType     = CRM_Utils_Array::value( 'type', $_GET );
        $this->assign('cdType', false);
        if ( $this->_cdType ) {
            $this->assign('cdType', true);
            return CRM_Custom_Form_CustomData::preProcess( $this );
        }

        // check for edit permission
        if ( ! CRM_Core_Permission::check( 'edit event participants' ) ) {
            CRM_Core_Error::fatal( ts( 'You do not have permission to access this page' ) );
        }
        
        $this->_id        = CRM_Utils_Request::retrieve( 'id', 'Positive', $this );

        //check the mode when this form is called either single or as
        //search task action
        $this->_contactID = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this );
        if ( $this->_id || $this->_contactID ) {
            $this->_single = true;
        } else {
            //set the appropriate action
            $advanced = null;
            $builder  = null;

            $session =& CRM_Core_Session::singleton();
            $advanced = $session->get('isAdvanced');
            $builder  = $session->get('isSearchBuilder');
            
            if ( $advanced == 1 ) {
                $this->_action = CRM_Core_Action::ADVANCED;
            } else if ( $advanced == 2 && $builder = 1) {
                $this->_action = CRM_Core_Action::PROFILE;
            }
            
            parent::preProcess( );
            $this->_single    = false;
            $this->_contactID = null;
        }

        // participant id
        if ( $this->_id ) {
            require_once 'CRM/Event/BAO/ParticipantPayment.php';
            $particpant =& new CRM_Event_BAO_ParticipantPayment( );
            $particpant->participant_id = $this->_id;
            if ( $particpant->find( true ) ) {
                $this->_online = true;
            }
        }
        
        $this->assign( 'single', $this->_single );
        
        $this->_action = CRM_Utils_Request::retrieve( 'action', 'String', $this, false, 'add' );

        $this->assign( 'action'  , $this->_action   ); 

        if ( $this->_action & CRM_Core_Action::DELETE ) {
            return;
        }

        if ( $this->_id ) {
            require_once 'CRM/Core/BAO/Note.php';
            $noteDAO               = & new CRM_Core_BAO_Note();
            $noteDAO->entity_table = 'civicrm_participant';
            $noteDAO->entity_id    = $this->_id;
            if ( $noteDAO->find(true) ) {
                $this->_noteId = $noteDAO->id;
            }
            
            // assign participant id to the template
            $this->assign('participantId',  $this->_id );
        }

        if ( $this->_id ) {
            $this->_roleId = CRM_Core_DAO::getFieldValue( "CRM_Event_DAO_Participant", $this->_id, 'role_id' );
        } 

        // when fee amount is included in form
        if ( CRM_Utils_Array::value( 'hidden_feeblock', $_POST ) ) {
            eval( 'CRM_Event_Form_EventFees::preProcess( $this );' );
            eval( 'CRM_Event_Form_EventFees::buildQuickForm( $this );' );
            eval( 'CRM_Event_Form_EventFees::setDefaultValues( $this );' );
        }

        // when custom data is included in this page
        if ( CRM_Utils_Array::value( "hidden_custom", $_POST ) ) {
            eval( 'CRM_Custom_Form_Customdata::preProcess( $this );' );
            eval( 'CRM_Custom_Form_Customdata::buildQuickForm( $this );' );
            eval( 'CRM_Custom_Form_Customdata::setDefaultValues( $this );' );
        }
    }

    
    /**
     * This function sets the default values for the form in edit/view mode
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    public function setDefaultValues( ) 
    { 
        if ( $this->_showFeeBlock ) {
            return CRM_Event_Form_EventFees::setDefaultValues( $this );
        }

        if ( $this->_cdType ) {
            return CRM_Custom_Form_CustomData::setDefaultValues( $this );
        }

        $defaults = array( );
        
        if ( $this->_action & CRM_Core_Action::DELETE ) {
            return $defaults;
        }
       
        if ( $this->_id ) {
            $ids    = array( );
            $params = array( 'id' => $this->_id );
            
            require_once "CRM/Event/BAO/Participant.php";
            CRM_Event_BAO_Participant::getValues( $params, $defaults, $ids );            
            $this->_contactID = $defaults[$this->_id]['contact_id'];
        }

        if ( $this->_noteId ) {
            $defaults[$this->_id]['note'] = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Note', $this->_noteId, 'note' );
        }
        
        if ($this->_action & ( CRM_Core_Action::VIEW | CRM_Core_Action::BROWSE ) ) {
            $inactiveNeeded = true;
            $viewMode = true;
        } else {
            $viewMode = false;
            $inactiveNeeded = false;
        }
        
        //setting default register date
        if ($this->_action == CRM_Core_Action::ADD) {
            $today_date = getDate();
            $defaults[$this->_id]['register_date']['M'] = $today_date['mon'];
            $defaults[$this->_id]['register_date']['d'] = $today_date['mday'];
            $defaults[$this->_id]['register_date']['Y'] = $today_date['year'];

            $defaults[$this->_id]['register_date']['A'] = 'AM';
            if( $today_date['hours'] > 12 ) {
                $today_date['hours'] -= 12;
                $defaults[$this->_id]['register_date']['A'] = 'PM';
            }
            
            $defaults[$this->_id]['register_date']['h'] = $today_date['hours'];
            $defaults[$this->_id]['register_date']['i'] = (integer)($today_date['minutes']/15) *15;

            if ( CRM_Utils_Array::value( 'event_id' , $defaults[$this->_id] ) ) {
                $contributionTypeId =  CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_Event',
                                                                    $defaults[$this->_id]['event_id'], 
                                                                    'contribution_type_id' );
                if ( $contributionTypeId ){
                    $defaults[$this->_id]['contribution_type_id'] = $contributionTypeId;
                }
            }
        } else {
            $defaults[$this->_id]['register_date'] = CRM_Utils_Date::unformat($defaults[$this->_id]['register_date']);
            $defaults[$this->_id]['register_date']['i']  = (integer)($defaults[$this->_id]['register_date']['i']/15)*15;
           
            $defaults[$this->_id]['record_contribution'] = 0;
            $recordContribution = CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_ParticipantPayment', 
                                                               $defaults[$this->_id]['id'], 
                                                               'contribution_id', 
                                                               'participant_id' );
          
            //contribution record exists for this participation
            if ( $recordContribution ) {
                foreach( array('contribution_type_id', 'payment_instrument_id','contribution_status_id', 'receive_date' ) 
                         as $field ) {
                    $defaults[$this->_id][$field] =  CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_Contribution', 
                                                                                  $recordContribution, $field );
                }
            }
            if ( $defaults[$this->_id]['participant_is_pay_later'] ) {
                $this->assign( 'participant_is_pay_later', true );
            }
            $this->assign( 'participant_status_id', $defaults[$this->_id]['participant_status_id'] );
        }
      

        if ( isset($this->_groupTree) ) {
            CRM_Core_BAO_CustomGroup::setDefaults( $this->_groupTree, $defaults[$this->_id], $viewMode, $inactiveNeeded );
        }
          
        $this->assign( 'event_is_test', CRM_Utils_Array::value('event_is_test',$defaults[$this->_id]) );
        return $defaults[$this->_id];
    }
    
    /** 
     * Function to build the form 
     * 
     * @return None 
     * @access public 
     */ 

    public function buildQuickForm( )  
    { 
        if ( $this->_showFeeBlock ) {
            return CRM_Event_Form_EventFees::buildQuickForm( $this );
        }

        if ( $this->_cdType ) {
            return CRM_Custom_Form_CustomData::buildQuickForm( $this );
        }

        $this->applyFilter('__ALL__', 'trim');
	
        if ( $this->_action & CRM_Core_Action::DELETE ) {
            $this->addButtons(array( 
                                    array ( 'type'      => 'next', 
                                            'name'      => ts('Delete'), 
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                            'isDefault' => true   ), 
                                    array ( 'type'      => 'cancel', 
                                            'name'      => ts('Cancel') ), 
                                    ) 
                              );
            return;
        }
        

        if ( $this->_single ) {
            $urlParams = "reset=1&cid={$this->_contactID}&context=participant";
            if ( $this->_id ) {
                $urlParams .= "&action=update&id={$this->_id}";
            } else {
                $urlParams .= "&action=add";
            }
            
            if (CRM_Utils_Request::retrieve( 'past', 'Boolean', $this ) ) {
                $urlParams .= "&past=true";
            }
            
            $url = CRM_Utils_System::url( 'civicrm/contact/view/participant',
                                          $urlParams, true, null, false ); 
        } else {
            $currentPath = CRM_Utils_System::currentPath( );

            $url = CRM_Utils_System::url( $currentPath, '_qf_Participant_display=true',
                                          true, null, false  );
        }

        $this->assign("refreshURL",$url);
        $url .= "&past=true";
        $this->assign("pastURL", $url);
        
        $events = array( );
        $this->assign("past", false);
        
        require_once "CRM/Event/BAO/Event.php";
        if ( CRM_Utils_Request::retrieve( 'past', 'Boolean', $this ) || ( $this->_action & CRM_Core_Action::UPDATE ) ) {
            $events = CRM_Event_BAO_Event::getEvents( true );
            $this->assign("past", true);
        } else {
            $events = CRM_Event_BAO_Event::getEvents( );
        }
        
        $this->add('select', 'event_id',  ts( 'Event' ),  
                   array( '' => ts( '- select -' ) ) + $events,
                   true,
                   array('onchange' => "buildFeeBlock( this.value );") );
        
        $this->add( 'date', 'register_date', ts('Registration Date and Time'),
                    CRM_Core_SelectValues::date('activityDatetime' ),
                    true);   
        $this->addRule('register_date', ts('Select a valid date.'), 'qfDate');

        //need to assign custom data type and subtype to the template
        $this->assign('customDataType', 'Participant');
        $this->assign('customDataSubType',  $this->_roleId );
        $this->assign('entityId',  $this->_id );

        $this->add( 'select', 'role_id' , ts( 'Participant Role' ),
                    array( '' => ts( '- select -' ) ) + CRM_Event_PseudoConstant::participantRole( ),
                    true,
                    array('onchange' => "buildCustomData( this.value );") );
        
        $this->add( 'select', 'status_id' , ts( 'Participant Status' ),
                    array( '' => ts( '- select -' ) ) + CRM_Event_PseudoConstant::participantStatus( ),
                    true );
        
        $this->add( 'text', 'source', ts('Event Source') );
        
        $noteAttributes = CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_Note' );
        $this->add('textarea', 'note', ts('Notes'), $noteAttributes['note']);

        $session = & CRM_Core_Session::singleton( );
        $uploadNames = $session->get( 'uploadNames' );
        if ( is_array( $uploadNames ) && ! empty ( $uploadNames ) ) {
            $buttonType = 'upload';
        } else {
            $buttonType = 'next';
        }

        $this->addButtons(array( 
                                array ( 'type'      => $buttonType, 
                                        'name'      => ts('Save'), 
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                        'isDefault' => true   ), 
                                array ( 'type'      => 'cancel', 
                                        'name'      => ts('Cancel') ), 
                                ) 
                          );

        if ($this->_action == CRM_Core_Action::VIEW) { 
            $this->freeze();
        }
    }
    
    /**
     * Add local and global form rules
     *
     * @access protected
     * @return void
     */
    function addRules( ) 
    {
        $this->addFormRule( array( 'CRM_Event_Form_Participant', 'formRule'), $this->_id );
    }
    
    /**
     * global validation rules for the form
     *
     * @param array $fields posted values of the form
     *
     * @return array list of errors to be posted back to the form
     * @static
     * @access public
     */
    static function formRule( &$values, $form, $id ) 
    {
        // If $values['_qf_Participant_next'] is Delete or 
        // $values['event_id'] is empty, then return 
        // instead of proceeding further.
        
        if ( ( $values['_qf_Participant_next'] == 'Delete' ) ||  
             ( ! $values['event_id'] ) 
             ) {
            return true;
        }

        if ( $values['status_id'] == 1 || $values['status_id'] == 2 ) {
            if ( $id ) {
                $previousStatus = CRM_Core_DAO::getFieldValue( "CRM_Event_DAO_Participant", $id, 'status_id' );
            }
            if ( !( $previousStatus == 1 || $previousStatus == 2 ) ) {
                require_once "CRM/Event/BAO/Participant.php";
                $message = CRM_Event_BAO_Participant::eventFull( $values['event_id'] );
            }
        }
        if( $message ) {
            $errorMsg["_qf_default"] = $message;  
        }

        return empty( $errorMsg ) ? true : $errorMsg;
    }    
       
    /** 
     * Function to process the form 
     * 
     * @access public 
     */ 
    public function postProcess( )
    { 
        if ( $this->_action & CRM_Core_Action::DELETE ) {
            require_once "CRM/Event/BAO/Participant.php";
            CRM_Event_BAO_Participant::deleteParticipant( $this->_id );
            return;
        }
        // get the submitted form values.  
        $params = $this->controller->exportValues( $this->_name );
        
        if ( $this->_isPaidEvent ) {
            if ( ! isset( $params['priceSetId'] ) ) {
                $params['amount_level'] = $this->_values['custom']['label'][array_search( $params['amount'], 
                                                                                          $this->_values['custom']['amount_id'])];

                $params['amount']       = $this->_values['custom']['value'][array_search( $params['amount'], 
                                                                                          $this->_values['custom']['amount_id'])];
            } else {
                $lineItem = array( );
                CRM_Event_Form_Registration_Register::processPriceSetAmount( $this->_values['custom']['fields'], 
                                                                             $params, $lineItem );
                $this->set( 'lineItem', $lineItem );
                $this->assign( 'lineItem', $lineItem );
            }
	    
            $params['fee_level']                = $params['amount_level'];
            $contributionParams                 = array( );
            $contributionParams['total_amount'] = $params['amount'];
        }
        
        //fix for CRM-3086
        $params['fee_amount'] = $params['amount'];
        unset($params['amount']);
        $params['register_date'] = CRM_Utils_Date::format($params['register_date']);
        $params['receive_date' ] = CRM_Utils_Date::format($params['receive_date' ]);
        $params['contact_id'   ] = $this->_contactID;
        if ( $this->_id ) {
            $ids['participant']  = $params['id'] = $this->_id;
        }
        
        $ids['note'] = array( );
        if ( $this->_noteId ) {
            $ids['note']['id']   = $this->_noteId;
        }
        
        $status = null;
        if ( $this->_action & CRM_Core_Action::UPDATE ) {
            $participantBAO     =& new CRM_Event_BAO_Participant( );
            $participantBAO->id = $this->_id;
            $participantBAO->find( );
            while ( $participantBAO->fetch() ) {
                $status = $participantBAO->status_id;
            }
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
                                                             $value, 'Participant', null, $this->_id);
            }
        }
        if (! empty($customData) ) {
            $params['custom'] = $customData;
        }
        
        //special case to handle if all checkboxes are unchecked
        $customFields = CRM_Core_BAO_CustomField::getFields( 'Participant' );
        if ( !empty($customFields) ) {
            foreach ( $customFields as $k => $val ) {
                if ( in_array ( $val[3], array ('CheckBox','Multi-Select') ) &&
                     ! CRM_Utils_Array::value( $k, $params['custom'] ) ) {
                    CRM_Core_BAO_CustomField::formatCustomField( $k, $params['custom'],
                                                                 '', 'Participant', null, $this->_id);
                }
            }
        }
        
        require_once 'CRM/Contact/BAO/Contact.php';
        // Retrieve the name and email of the current user - this will be the FROM for the receipt email
        $session =& CRM_Core_Session::singleton( );
        $userID  = $session->get( 'userID' );
        list( $userName, 
              $userEmail ) = CRM_Contact_BAO_Contact_Location::getEmailDetails( $userID );
        require_once "CRM/Event/BAO/Participant.php";
        $participants = array();
        if ( $this->_single ) {
            $participants[] = CRM_Event_BAO_Participant::create( $params, $ids );
           
        } else {
            $ids = array( );
            foreach ( $this->_contactIds as $contactID ) {
                $params['contact_id'] = $contactID;
                $participants[]       = CRM_Event_BAO_Participant::create( $params, $ids );   
            }           
        }

        if ( isset( $params['event_id'] ) ) {
            $eventTitle = CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_Event',
                                                       $params['event_id'],
                                                       'title' );
        }

        if ( $this->_single ) {
            $this->_contactIds[] = $this->_contactID;
        }

        if ( $params['record_contribution'] ) {
            if( $ids['participant'] ) {
                $ids['contribution'] = CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_ParticipantPayment', 
                                                                    $ids['participant'], 
                                                                    'contribution_id', 
                                                                    'participant_id' );
            }
            unset($params['note']);
           
            //build contribution params 
            $config =& CRM_Core_Config::singleton();
            
            $contributionParams['currency'             ] = $config->defaultCurrency;
            $contributionParams['contact_id'           ] = $params['contact_id'];
            $contributionParams['source'               ] = "{$eventTitle}: Offline registration (by {$userName})";
            $contributionParams['non_deductible_amount'] = 'null';
          
            $contributionParams['receive_date'         ] = $params['receive_date'];
           
            $contributionParams['receipt_date'         ] = $params['send_receipt'] ? 
                $contributionParams['receive_date'] : 'null';
            $recordContribution = array( 'contribution_type_id', 
                                         'payment_instrument_id',
                                         'trxn_id',
                                         'contribution_status_id' );
           
            foreach ( $recordContribution as $f ) {
                $contributionParams[$f] = CRM_Utils_Array::value( $f, $params );
                if ( $f == 'trxn_id' ) {
                    $this->assign ( 'trxn_id',  $contributionParams[$f] );                   
                }
            }
   
            
            require_once 'CRM/Contribute/BAO/Contribution.php';
            $contributions = array( );
            if ( $this->_single ) {
                $contributions[] =& CRM_Contribute_BAO_Contribution::create( $contributionParams, $ids );
               
            } else {
                $ids = array( );
                foreach ( $this->_contactIds as $contactID ) {
                    $contributionParams['contact_id'] = $contactID;
                    $contributions[] =& CRM_Contribute_BAO_Contribution::create( $contributionParams, $ids );
               }           
            }

            //insert payment record for this participation
            if( !$ids['contribution'] ) {
                require_once 'CRM/Event/DAO/ParticipantPayment.php';
                foreach ( $this->_contactIds as $num => $contactID ) {
                    $ppDAO =& new CRM_Event_DAO_ParticipantPayment();   
                    $ppDAO->participant_id  = $participants[$num]->id;
                    $ppDAO->contribution_id = $contributions[$num]->id;
                    $ppDAO->save();
                }
            }
        }
      
        if ( $params['send_receipt'] ) {
            $receiptFrom = '"' . $userName . '" <' . $userEmail . '>';
            
            //use of CRM/Event/Form/Registration/ReceiptMessage.tpl requires variables in different format
            $event = array();
            $event['id'] = $params['event_id'];
            $event['event_title'] = $eventTitle;
           
            $event['fee_label'] = CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_Event',
                                                                      $params['event_id'],
                                                                      'fee_label' );
            $event['event_start_date'] = CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_Event',
                                                       $params['event_id'],
                                                       'start_date' );
            $event['event_end_date'] = CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_Event',
                                                                    $params['event_id'],
                                                                    'end_date' );
            $role = CRM_Event_PseudoConstant::participantRole();
            $event['participant_role'] = $role[$params['role_id']];
            $event['is_monetary'] = $this->_isPaidEvent;
            $this->assign( 'isAmountzero', 1 );
            $this->assign( 'event' , $event );
            if ( $params['receipt_text'] ) {
                $eventPage = array();
                $eventPage['confirm_email_text'] =  $params['receipt_text'];
            }
            $isShowLocation = CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_Event',
                                                           $params['event_id'],
                                                           'is_show_location' );
            $this->assign( 'isShowLocation', $isShowLocation ); 
            if ( $isShowLocation ) {
                $param_location = array( 'entity_id' => $params['event_id'] ,'entity_table' => 'civicrm_event');
                $values = array();
                require_once 'CRM/Core/BAO/Location.php';
                $location = CRM_Core_BAO_Location::getValues( $param_location, $values , true );
                $this->assign( 'location', $location );
            }             
                                 
            $status = CRM_Event_PseudoConstant::participantStatus();
            if ( $this->_isPaidEvent ) {
                $paymentInstrument = CRM_Contribute_PseudoConstant::paymentInstrument();
                $this->assign( 'amount', $contributionParams['total_amount'] );
            }
                        
            $this->assign( 'receive_date', $params['register_date'] );
            $this->assign( 'subject', ts('Event Confirmation') );
            $this->assign( 'customValues', $customValues );
            
            $template =& CRM_Core_Smarty::singleton( );
            $subject = trim( $template->fetch( 'CRM/Contribute/Form/ReceiptSubjectOffline.tpl' ) );
            $message = $template->fetch( 'CRM/Event/Form/Registration/ReceiptMessage.tpl' );
                     
            // retrieve custom data
            require_once "CRM/Core/BAO/UFGroup.php";
            $customFields = $customValues = array( );
            foreach ( $this->_groupTree as $groupID => $group ) {
                if ( $groupID == 'info' ) {
                    continue;
                }
                foreach ( $group['fields'] as $k => $field ) {
                    $field['title'] = $field['label'];
                    $customFields["custom_{$k}"] = $field;
                }
            }

            foreach ( $this->_contactIds as $num => $contactID ) {
                // Retrieve the name and email of the contact - this will be the TO for receipt email
                list( $this->_contributorDisplayName, $this->_contributorEmail, $this->_toDoNotEmail ) = CRM_Contact_BAO_Contact::getContactDetails( $contactID );
              
                CRM_Core_BAO_UFGroup::getValues( $contactID, $customFields, $customValues , false, 
                                                 array( array( 'participant_id', '=', $participants[$num]->id, 0, 0 ) ) );
               
                //Do not try to send emails if emailID is not present
                //or doNotEmail option is checked for that contact 
                if( empty($this->_contributorEmail) or $this->_toDoNotEmail ) {
                    $notSent[] = $contactID;
                } else {
                    require_once 'CRM/Utils/Mail.php';
                    if ( CRM_Utils_Mail::send( $receiptFrom,
                                               $this->_contributorDisplayName,
                                               $this->_contributorEmail,
                                               $subject,
                                               $message) ) {
                        $sent[] = $contactID;
                    } else {
                        $notSent[] = $contactID;
                    }
                }
            }
            
        }
        
        if ( ( $this->_action & CRM_Core_Action::UPDATE ) ) {
            $statusMsg = ts('Event registration information for %1 has been updated.', array(1 => $this->_contributorDisplayName));
            if ( $params['send_receipt'] ) {
                $statusMsg .= ' ' .  ts('A confirmation email has been sent to %1', array(1 => $this->_contributorEmail));
            }
        } elseif ( ( $this->_action & CRM_Core_Action::ADD ) ) {
            if ( $this->_single ) {
                $statusMsg = ts('Event registration for %1 has been added.', array(1 => $this->_contributorDisplayName));
                if ( $params['send_receipt'] ) {
                    $statusMsg .= ' ' .  ts('A confirmation email has been sent to %1.', array(1 => $this->_contributorEmail));
                }
            } else {
                $statusMsg = ts('Total Participant(s) added to event: %1.', array(1 => count($this->_contactIds)));
                if( count($notSent) > 0 ) {
                    $statusMsg .= ' ' . ts('Email has NOT been sent to %1 contact - communication preferences specify DO NOT EMAIL OR valid Email is NOT present. ', array(1 => count($notSent)));
                } else {
                    $statusMsg .= ' ' .  ts('A confirmation email has been sent to ALL participants');
                }
            }
        }
        require_once "CRM/Core/Session.php";
        CRM_Core_Session::setStatus( "{$statusMsg}" );
    }
}

