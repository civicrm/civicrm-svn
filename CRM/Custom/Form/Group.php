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
 * form to process actions on the group aspect of Custom Data
 */
class CRM_Custom_Form_Group extends CRM_Core_Form {

    /**
     * the group id saved to the session for an update
     *
     * @var int
     * @access protected
     */
    protected $_id;

    /**
     * Function to set variables up before form is built
     * 
     * @param null
     * 
     * @return void
     * @access public
     */
    public function preProcess()
    {
        require_once 'CRM/Core/BAO/CustomGroup.php';
        // current group id
        $this->_id = $this->get('id');

        // setting title for html page
        if ($this->_action == CRM_Core_Action::UPDATE) {
            $title = CRM_Core_BAO_CustomGroup::getTitle($this->_id);
            CRM_Utils_System::setTitle(ts('Edit %1', array(1 => $title)));
        } else if ($this->_action == CRM_Core_Action::VIEW) {
            $title = CRM_Core_BAO_CustomGroup::getTitle($this->_id);
            CRM_Utils_System::setTitle(ts('Preview %1', array(1 => $title)));
        } else {
            CRM_Utils_System::setTitle(ts('New Custom Data Group'));
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
     * @static
     */
    static function formRule(&$fields, &$files, $self) {
        $errors = array();

        $extends = array('Activity','Relationship','Group','Contribution','Membership', 'Event','Participant');
        if(in_array($fields['extends'][0],$extends) && $fields['style'] == 'Tab' ) {
            $errors['style'] = 'Display Style should be Inline for this Class';
        }

        //checks the given custom group doesnot start with digit
        $title = $fields['title']; 
        if ( ! empty( $title ) ) {
            $asciiValue = ord($title{0});//gives the ascii value
            if($asciiValue>=48 && $asciiValue<=57) {
                $errors['title'] = ts("Group's Name should not start with digit");
            } 
        }

        return empty($errors) ? true : $errors;
    }
    
    

    /**
     * This function is used to add the rules (mainly global rules) for form.
     * All local rules are added near the element
     *
     * @param null
     * 
     * @return void
     * @access public
     * @see valid_date
     */
    function addRules( )
    {
        $this->addFormRule( array( 'CRM_Custom_Form_Group', 'formRule' ), $this ); 
    }
    
    /**
     * Function to actually build the form
     * 
     * @param null
     * 
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        $this->applyFilter('__ALL__', 'trim');
        
        $attributes = CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_CustomGroup' );

        // title
        $this->add('text', 'title', ts('Group Name'), $attributes['title'], true);
            
        require_once "CRM/Contribute/PseudoConstant.php";
        require_once "CRM/Member/BAO/MembershipType.php";
        $sel1 = CRM_Core_SelectValues::customGroupExtends();
        $sel2= array();
        $sel2['Activity']     = array("" => "-- Any --") + CRM_Core_PseudoConstant::activityType( false );
        $sel2['Contribution'] = array("" => "-- Any --") + CRM_Contribute_PseudoConstant::contributionType( );
        $sel2['Membership']   = array("" => "-- Any --") + CRM_Member_BAO_MembershipType::getMembershipTypes( false );
        $sel2['Event']        = array("" => "-- Any --") + CRM_Core_OptionGroup::values('event_type');
        $sel2['Participant']  = array("" => "-- Any --") + CRM_Core_OptionGroup::values('participant_role');
        
        require_once "CRM/Contact/BAO/Relationship.php";
        $relTypeInd =  CRM_Contact_BAO_Relationship::getContactRelationshipType(null,'null',null,'Individual');
        $relTypeOrg =  CRM_Contact_BAO_Relationship::getContactRelationshipType(null,'null',null,'Organization');
        $relTypeHou =  CRM_Contact_BAO_Relationship::getContactRelationshipType(null,'null',null,'Household');
        $allRelationshipType =array();
        $allRelationshipType = array_merge(  $relTypeInd , $relTypeOrg);
        $allRelationshipType = array_merge( $allRelationshipType, $relTypeHou);
        
        $sel2['Relationship'] = array("" => "-- Any --") + $allRelationshipType;
        
        require_once "CRM/Core/Component.php";
        $cSubTypes = CRM_Core_Component::contactSubTypes();
        
        if ( !empty( $cSubTypes ) ) {
            $contactSubTypes = array( );
            foreach($cSubTypes as $key => $value ) {
                $contactSubTypes[$key] = $key;
            }
            $sel2['Contact']  =  array("" => "-- Any --") + $contactSubTypes;
        } else {
            if( !isset( $this->_id ) ){
                $formName = 'document.forms.' . $this->_name;
                
                $js  = "<script type='text/javascript'>\n";
                $js .= "{$formName}['extends[1]'].style.display = 'none';\n";
                $js .= "</script>";
                $this->assign( 'initHideBlocks', $js );
            }
        }
        
        $sel =& $this->addElement('hierselect', "extends", ts('Used For'), array('onClick' => "showHideStyle();",
                                                                                 'name'=>"extends[0]"
                                                                                 ));
        $sel->setOptions(array($sel1,$sel2));
        
        // which entity is this custom data group for ?
        // for update action only allowed if there are no custom values present for this group.
        // $extendsElement = $this->add('select', 'extends', ts('Used For'), CRM_Core_SelectValues::customGroupExtends());
        
        if ($this->_action == CRM_Core_Action::UPDATE) { 
            $sel->freeze();
            $this->assign('gid', $this->_id);
        }
        
        // help text
        $this->addWysiwyg( 'help_pre', ts('Pre-form Help'), $attributes['help_pre']);
        $this->addWysiwyg( 'help_post', ts('Post-form Help'), $attributes['help_post']);

        // weight
        $this->add('text', 'weight', ts('Order'), $attributes['weight'], true);
        $this->addRule('weight', ts('is a numeric field') , 'numeric');

        // display style
        $this->add('select', 'style', ts('Display Style'), CRM_Core_SelectValues::customGroupStyle());
       
        // is this group collapsed or expanded ?
        $this->addElement('checkbox', 'collapse_display', ts('Collapse this group on initial display'));

        // is this group active ?
        $this->addElement('checkbox', 'is_active', ts('Is this Custom Data Group active?') );

        $this->addButtons(array(
                                array ( 'type'      => 'next',
                                        'name'      => ts('Save'),
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                        'isDefault' => true   ),
                                array ( 'type'      => 'cancel',
                                        'name'      => ts('Cancel') ),
                                )
                          );
        
        // views are implemented as frozen form
        if ($this->_action & CRM_Core_Action::VIEW) {
            $this->freeze();
            $this->addElement('button', 'done', ts('Done'), array('onclick' => "location.href='civicrm/admin/custom/group?reset=1&action=browse'"));
        }
    }

    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     * 
     * @param null
     * 
     * @return array   array of default values
     * @access public
     */
    function setDefaultValues()
    {
        $defaults = array();
    
        if ($this->_action == CRM_Core_Action::ADD) {
            $defaults['weight'] = CRM_Utils_Weight::getDefaultWeight('CRM_Core_DAO_CustomGroup');
        }

        if (isset($this->_id)) {
            $params = array('id' => $this->_id);
            CRM_Core_BAO_CustomGroup::retrieve($params, $defaults);
            
        } else {
            $defaults['is_active'] = 1;
            $defaults['style'] = 'Inline';
        }

        if ( isset ($defaults['extends'] ) ){     
            $extends = $defaults['extends'];
            unset($defaults['extends']);
            $defaults['extends'][0] = $extends;
            $defaults['extends'][1] = CRM_Utils_Array::value( 'extends_entity_column_value',
                                                              $defaults );
        }
        
        return $defaults;
        
    }
    
    /**
     * Process the form
     * 
     * @param null
     * 
     * @return void
     * @access public
     */
    public function postProcess( )
    {
        // get the submitted form values.
        $params = $this->controller->exportValues('Group');
        $fieldLength =  CRM_Core_DAO::getAttribute('CRM_Core_DAO_CustomGroup', 'name');
              
        // create custom group dao, populate fields and then save.           
        $group =& new CRM_Core_DAO_CustomGroup();
        $group->title            = $params['title'];
        $group->name             = CRM_Utils_String::titleToVar($params['title'], $fieldLength['maxlength'] );
        $group->extends          = $params['extends'][0];

        if ( ($params['extends'][0] == 'Relationship') && !empty($params['extends'][1])) {
            $group->extends_entity_column_value = str_replace( array('_a_b', '_b_a'), array('', ''), $params['extends'][1]);
        } elseif ( empty($params['extends'][1]) ) {
            $group->extends_entity_column_value = null;
        } else {
            $group->extends_entity_column_value = $params['extends'][1];
        }
        
        $group->style            = $params['style'];
        $group->collapse_display = CRM_Utils_Array::value('collapse_display', $params, false);


        if ($this->_id) {
            $oldWeight = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomGroup', $this->_id, 'weight', 'id' );
        }
        $group->weight =
            CRM_Utils_Weight::updateOtherWeights('CRM_Core_DAO_CustomGroup', $oldWeight, $params['weight']);

        $group->help_pre         = $params['help_pre'];
        $group->help_post        = $params['help_post'];
        $group->is_active        = CRM_Utils_Array::value('is_active'      , $params, false);

        $tableName = null;
        if ($this->_action & CRM_Core_Action::UPDATE) {
            $group->id = $this->_id;
        } else {
            // lets create the table associated with the group and save it
            $tableName = $group->table_name =
                "civicrm_value_" .
                strtolower( CRM_Utils_String::munge( $group->title, '_', 32 ) );
            $group->is_multiple = 0;
        }
        
        // enclose the below in a transaction
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );

        $group->save();
        if ( $tableName ) {
            // now append group id to table name, this prevent any name conflicts
            // like CRM-2742
            $tableName .= "_{$group->id}";
            $group->table_name = $tableName;
            CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_CustomGroup',
                                         $group->id,
                                         'table_name',
                                         $tableName );

            // now create the table associated with this group
            CRM_Core_BAO_CustomGroup::createTable( $group );
        }

        // reset the cache
        require_once 'CRM/Core/BAO/Cache.php';
        CRM_Core_BAO_Cache::deleteGroup( 'contact fields' );

        $transaction->commit( );

        if ($this->_action & CRM_Core_Action::UPDATE) {
            CRM_Core_Session::setStatus(ts('Your Group \'%1 \' has been saved.', array(1 => $group->title)));
        } else {
            $url = CRM_Utils_System::url( 'civicrm/admin/custom/group/field', 'reset=1&action=add&gid=' . $group->id);
            CRM_Core_Session::setStatus(ts('Your Group \'%1\' has been added. You can add custom fields to this group now.',
                                           array(1 => $group->title)));
            $session =& CRM_Core_Session::singleton( );
            $session->replaceUserContext($url);
        }
    }
}

