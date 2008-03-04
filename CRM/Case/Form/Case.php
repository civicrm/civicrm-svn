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

require_once 'CRM/Core/Form.php';

/**
 * This class generates form components for processing a case
 * 
 */
class CRM_Case_Form_Case extends CRM_Core_Form
{
    /**
     * the id of the case that we are proceessing
     *
     * @var int
     * @protected
     */
    protected $_id;


    /**
     * the id of the contact associated with this contribution
     *
     * @var int
     * @protected
     */
    protected $_contactID;

    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    public function preProcess()  
    {  
        $this->_contactID = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this );
        $this->_id        = CRM_Utils_Request::retrieve( 'id', 'Integer', $this );
        $this->_activityID = CRM_Utils_Request::retrieve('activity_id','Integer',$this);
        $this->_context = CRM_Utils_Request::retrieve('context','String',$this); 
        $this->assign('context', $this->_context);
        $this->_caseid = CRM_Utils_Request::retrieve('caseid','Integer',$this);
        $this->assign('enableCase', true );
    }

    function setDefaultValues( ) 
    {
        $defaults = array( );
        require_once 'CRM/Case/BAO/Case.php' ;
        if ( isset( $this->_id ) ) {
            $params = array( 'id' => $this->_id );
            CRM_Case_BAO_Case::retrieve($params, $defaults, $ids);
        }        
        $defaults['case_type_id'] = explode( CRM_Case_BAO_Case::VALUE_SEPERATOR, CRM_Utils_Array::value( 'case_type_id' , $defaults ) );
        
        if ( $this->_action & CRM_Core_Action::ADD ) {
            $defaults['start_date'] = array( );
            CRM_Utils_Date::getAllDefaultValues( $defaults['start_date'] );
        }

        return $defaults;
    }
    
    /** 
     * Function to build the form 
     * 
     * @return None 
     * @access public 
     */ 
    public function buildQuickForm( )
    {
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

        $this->add( 'text', 'subject', ts('Subject'), 'maxlength=128', true);

        require_once 'CRM/Core/OptionGroup.php';        
        $caseStatus  = CRM_Core_OptionGroup::values('case_status');
        $this->add('select', 'status_id',  ts( 'Case Status' ),  
                    $caseStatus , true  );

        $caseType = CRM_Core_OptionGroup::values('case_type');
        $this->add('select', 'case_type_id',  ts( 'Case Type' ),  
                   $caseType , true, array("size"=>"5",  "multiple"));
        

                        
        $this->add( 'date', 'start_date', ts('Start Date'),
                    CRM_Core_SelectValues::date('activityDate' ),
                    true);   
        $this->addRule('start_date', ts('Select a valid date.'), 'qfDate');
        
        $this->add( 'date', 'end_date', ts('End Date'),
                    CRM_Core_SelectValues::date('activityDate' ),
                    false); 
        $this->addRule('end_date', ts('Select a valid date.'), 'qfDate');
        
        $this->add('textarea', 'details', ts('Notes'));

        $this->addFormRule( array( 'CRM_Case_Form_Case', 'formRule' ) );
        
        if ( $this->_action & CRM_Core_Action::VIEW ) {
            $this->freeze( );
            $this->addButtons(array(  
                                    array ( 'type'      => 'cancel',  
                                            'name'      => ts('Done'),  
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',  
                                            
                                            'isDefault' => true   )
                                    )
                              );
        } else {
            $this->addButtons(array( 
                                    array ( 'type'      => 'next',
                                            'name'      => ts('Save'), 
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                            'isDefault' => true   ), 
                                    array ( 'type'      => 'cancel', 
                                            'name'      => ts('Cancel') ), 
                                    ) 
                              );
        }
    }
    
    /**  
     * global form rule  
     *  
     * @param array $fields  the input form values  
     * @param array $files   the uploaded files if any  
     * @param array $options additional user data  
     *  
     * @return true if no errors, else array of errors  
     * @access public  
     * @static  s
     */  
    static function formRule( &$values ) {

        $errors = array( ); 

        $start = CRM_Utils_Date::format( $values['start_date'] );
        $end   = CRM_Utils_Date::format( $values['end_date'  ] );
        if ( ($end < $start) && ($end != 0) ) {
            $errors['end_date'] = ts( 'End date should be later than Start date' );
            return $errors;
        }
        
        return true;
    }
    
    /** 
     * Function to process the form 
     * 
     * @access public 
     * @return None 
     */ 
    public function postProcess( )
    {
        if ( $this->_action & CRM_Core_Action::DELETE ) { 
            require_once 'CRM/Case/BAO/Case.php';
            CRM_Case_BAO_Case::deleteCase( $this->_id );
            CRM_Core_Session::setStatus( ts("Selected Case has been deleted."));
            return;
        }

        // get the submitted form values.  
        $params = $this->controller->exportValues( $this->_name );
        
        if ( $this->_action & CRM_Core_Action::UPDATE ) {
            $params['id'] = $this->_id ;
        }
        
        $params['contact_id'  ] = $this->_contactID;
        $params['start_date'  ] = CRM_Utils_Date::format( $params['start_date'] );
        $params['end_date'    ] = CRM_Utils_Date::format( $params['end_date'] );
        $params['case_type_id'] = CRM_Case_BAO_Case::VALUE_SEPERATOR.implode(CRM_Case_BAO_Case::VALUE_SEPERATOR, $params['case_type_id'] ).CRM_Case_BAO_Case::VALUE_SEPERATOR;
        
        require_once 'CRM/Case/BAO/Case.php';
        $case = CRM_Case_BAO_Case::create( $params );

        // set status message
        CRM_Core_Session::setStatus( ts('Case \'%1\' has been saved.', array( 1 => $params['subject'] ) ) );
    }
}


