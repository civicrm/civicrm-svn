<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                  |
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
 | Foundation at info[AT]civicrm[DOT]org.  If you have questions       |
 | about the Affero General Public License or the licensing  of       |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | http://www.civicrm.org/licensing/                                  |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@civicrm.org>
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Core/Page.php';

/**
 * Create a page for displaying CiviCRM Profile Fields.
 *
 * Heart of this class is the run method which checks
 * for action type and then displays the appropriate
 * page.
 *
 */
class CRM_UF_Page_Field extends CRM_Core_Page {
    
    /**
     * The group id of the field
     *
     * @var int
     * @access protected
     */
    protected $_gid;

    /**
     * The action links that we need to display for the browse screen
     *
     * @var array
     * @access private
     */
    private static $_actionLinks;


    /**
     * Get the action links for this page.
     *
     * @return array $_actionLinks
     *
     */
    function &actionLinks()
    {
        if (!isset(self::$_actionLinks)) {
            // helper variable for nicer formatting
            $disableExtra = ts('Are you sure you want to disable this CiviCRM Profile field?');
            self::$_actionLinks = array(
                                        CRM_Core_Action::UPDATE  => array(
                                                                          'name'  => ts('Edit'),
                                                                          'url'   => 'civicrm/admin/uf/group/field',
                                                                          'qs'    => 'reset=1&action=update&id=%%id%%&gid=%%gid%%',
                                                                          'title' => ts('Edit CiviCRM Profile Field') 
                                                                          ),
                                        CRM_Core_Action::PREVIEW    => array(
                                                                          'name'  => ts('Preview'),
                                                                          'url'   => 'civicrm/admin/uf/group/field',
                                                                          'qs'    => 'action=preview&id=%%id%%&field=1',
                                                                          'title' => ts('Preview CiviCRM Profile Field'),
                                                                          ),
                                        CRM_Core_Action::DISABLE => array(
                                                                          'name'  => ts('Disable'),
                                                                          'url'   => 'civicrm/admin/uf/group/field',
                                                                          'qs'    => 'action=disable&id=%%id%%',
                                                                          'title' => ts('Disable CiviCRM Profile Field'),
                                                                          'extra' => 'onclick = "return confirm(\'' . $disableExtra . '\');"',
                                                                          ),
                                        CRM_Core_Action::ENABLE  => array(
                                                                          'name'  => ts('Enable'),
                                                                          'url'   => 'civicrm/admin/uf/group/field',
                                                                          'qs'    => 'action=enable&id=%%id%%',
                                                                          'title' => ts('Enable CiviCRM Profile Field'),
                                                                          ),
                                        CRM_Core_Action::DELETE  => array(
                                                                          'name'  => ts('Delete'),
                                                                          'url'   => 'civicrm/admin/uf/group/field',
                                                                          'qs'    => 'action=delete&id=%%id%%',
                                                                          'title' => ts('Enable CiviCRM Profile Field'),
                                                                          ),
                                        );
        }
        return self::$_actionLinks;
    }

    /**
     * Browse all CiviCRM Profile group fields.
     *
     * @return void
     * @access public
     * @static
     */
    function browse()
    {
        $ufField = array();
        $ufFieldBAO =& new CRM_Core_BAO_UFField();
        
        // fkey is gid
        $ufFieldBAO->uf_group_id = $this->_gid;
        $ufFieldBAO->orderBy('weight', 'field_name');
        $ufFieldBAO->find();
        
        $locationType = array( );
        $locationType =& CRM_Core_PseudoConstant::locationType();
        
        require_once 'CRM/Contact/BAO/Contact.php';
        $fields =& CRM_Contact_BAO_Contact::exportableFields( 'All', false, true );
        require_once "CRM/Contribute/BAO/Contribution.php";
        $fields = array_merge(CRM_Contribute_BAO_Contribution::getContributionFields(), $fields);

        if ( CRM_Core_Permission::access( 'Quest' ) ) {
            require_once 'CRM/Quest/BAO/Student.php';
            $fields = array_merge(CRM_Quest_BAO_Student::exportableFields(), $fields);
        }

        $select = array( );
        foreach ($fields as $name => $field ) {
            if ( $name ) {
                $select[$name] = $field['title'];
            }
        }
        $select['group'] = ts('Group(s)'); 
        $select['tag'  ] = ts('Tag(s)');
	//print_r($fields);
        while ($ufFieldBAO->fetch()) {
            $ufField[$ufFieldBAO->id] = array();
            $phoneType = $locType = '';
            CRM_Core_DAO::storeValues( $ufFieldBAO, $ufField[$ufFieldBAO->id]);
            CRM_Core_DAO_UFField::addDisplayEnums($ufField[$ufFieldBAO->id]);

	    $ufField[$ufFieldBAO->id]['label'] = $ufFieldBAO->label; 

            $action = array_sum(array_keys($this->actionLinks()));
            if ($ufFieldBAO->is_active) {
                $action -= CRM_Core_Action::ENABLE;
            } else {
                $action -= CRM_Core_Action::DISABLE;
            }
            
            $ufField[$ufFieldBAO->id]['action'] = CRM_Core_Action::formLink(self::actionLinks(), $action, 
                                                                            array('id'  => $ufFieldBAO->id,
                                                                                  'gid' => $this->_gid));
        }
        $this->assign('ufField', $ufField);
    }


    /**
     * edit CiviCRM Profile data.
     *
     * editing would involved modifying existing fields + adding data to new fields.
     *
     * @param string $action the action to be invoked

     * @return void
     * @access public
     */
    function edit($action)
    {
        // create a simple controller for editing CiviCRM Profile data
        $controller =& new CRM_Core_Controller_Simple('CRM_UF_Form_Field', ts('CiviCRM Profile Field'), $action);
        
        // set the userContext stack
        $session =& CRM_Core_Session::singleton();
        $session->pushUserContext(CRM_Utils_System::url('civicrm/admin/uf/group/field','reset=1&action=browse&gid=' . $this->_gid));
        $controller->set('gid', $this->_gid);
        $controller->setEmbedded(true);
        $controller->process();
        $controller->run();
    }


    /**
     * Run the page.
     *
     * This method is called after the page is created. It checks for the  
     * type of action and executes that action. 
     *
     * @return void
     * @access public
     *
     */
    function run()
    {
        $this->assign( 'dojoIncludes', "dojo.require('dojo.widget.SortableTable');" );

        // get the group id
        $this->_gid = CRM_Utils_Request::retrieve('gid', 'Positive',
                                                  $this, false, 0);
        if ($this->_gid) {
            require_once 'CRM/Core/BAO/UFGroup.php';
            $groupTitle = CRM_Core_BAO_UFGroup::getTitle($this->_gid);
            $this->assign('gid', $this->_gid);
            $this->assign('groupTitle', $groupTitle);
            CRM_Utils_System::setTitle(ts('%1 - CiviCRM Profile Fields', array(1 => $groupTitle)));
        }

        // get the requested action
        $action = CRM_Utils_Request::retrieve('action', 'String',
                                              $this, false, 'browse'); // default to 'browse'

        // assign vars to templates
        $this->assign('action', $action);

        $id = CRM_Utils_Request::retrieve('id', 'Positive',
                                          $this, false, 0);
        
        // what action to take ?
        if ($action & (CRM_Core_Action::UPDATE | CRM_Core_Action::ADD | CRM_Core_Action::VIEW | CRM_Core_Action::DELETE)) {
            $this->edit($action);   // no browse for edit/update/view/delete
        } else if ( $action & CRM_Core_Action::PREVIEW ) {
            $this->preview($id,$this->_gid) ;
        } else {
            require_once 'CRM/Core/BAO/UFField.php';
            if ($action & CRM_Core_Action::DISABLE) {
                CRM_Core_BAO_UFField::setIsActive($id, 0);
            } else if ($action & CRM_Core_Action::ENABLE) {
                CRM_Core_BAO_UFField::setIsActive($id, 1);
            } 
            $this->browse();
        }

        // Call the parents run method
        parent::run();
    }

    /**
     * Preview custom field
     *
     * @param int $id custom field id
     * @return void
     * @access public
     */
    function preview($fieldId,$groupId)
    {
        $controller =& new CRM_Core_Controller_Simple('CRM_UF_Form_Preview', ts('Preview Custom Data'), CRM_Core_Action::PREVIEW);
        $session =& CRM_Core_Session::singleton();
        $session->pushUserContext(CRM_Utils_System::url('civicrm/admin/uf/group/field','reset=1&action=browse&gid=' . $this->_gid));
        $controller->set('fieldId', $fieldId);
        $controller->set('id', $groupId);
        $controller->setEmbedded(true);
        $controller->process();
        $controller->run();
    }

}

?>
