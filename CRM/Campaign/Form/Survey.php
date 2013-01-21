<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */

/**
 * This class generates form components for processing a survey
 *
 */
class CRM_Campaign_Form_Survey extends CRM_Core_Form {

  /**
   * The id of the object being edited
   *
   * @var int
   */
  protected $_surveyId;

  /**
   * action
   *
   * @var int
   */
  protected $_action;

  public function preProcess() {
    if (!CRM_Campaign_BAO_Campaign::accessCampaign()) {
      CRM_Utils_System::permissionDenied();
    }

    $this->_action   = CRM_Utils_Request::retrieve('action', 'String', $this, FALSE, 'add', 'REQUEST');
    $this->_surveyId = CRM_Utils_Request::retrieve('id', 'Positive', $this, FALSE);

    if ($this->_surveyId) {
      $this->_single = TRUE;

      $params = array('id' => $this->_surveyId);
      CRM_Campaign_BAO_Survey::retrieve($params, $surveyInfo);

      CRM_Utils_System::setTitle(ts('Configure Survey - %1', array(1 => $surveyInfo['title'])));
    }

    $this->assign('action', $this->_action);
    $this->assign('surveyId', $this->_surveyId);

    if (class_exists('CRM_Profilemockup_Page_ProfileEditor')) {
      // CRM-11480, CRM-11682
      CRM_Profilemockup_Page_ProfileEditor::registerProfileScripts();
    }

    CRM_Campaign_Form_Survey_TabHeader::build($this);
  }

  /**
   * Function to actually build the form
   *
   * @param null
   *
   * @return void
   * @access public
   */
  public function buildQuickForm() {
    $session = CRM_Core_Session::singleton();

    if ($this->_surveyId) {
      $buttons = array(
        array(
          'type' => 'upload',
          'name' => ts('Save'),
          'isDefault' => TRUE,
        ),
        array(
          'type' => 'upload',
          'name' => ts('Save and Done'),
          'spacing' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
          'subName' => 'done',
        ),
      );
    } 
    else {
      $buttons = array(
        array(
          'type' => 'upload',
          'name' => ts('Continue >>'),
          'spacing' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
          'isDefault' => TRUE,
        ), 
      );
    }
    $buttons[] = 
      array(
            'type' => 'cancel',
            'name' => ts('Cancel'),
            );
    $this->addButtons($buttons);
  }

  function endPostProcess() {
    // make submit buttons keep the current working tab opened.
    if ($this->_action & CRM_Core_Action::UPDATE) {
      $className = CRM_Utils_String::getClassName($this->_name);
      $subPage   = strtolower($className);
      CRM_Core_Session::setStatus(ts("'%1' information has been saved.", array(1 => $className)), ts('Saved'), 'success');

      $this->postProcessHook();

      CRM_Utils_System::redirect(CRM_Utils_System::url("civicrm/survey/configure/{$subPage}",
                                                       "action=update&reset=1&id={$this->_surveyId}"));
    }
    else if ($this->_action & CRM_Core_Action::ADD) {
      CRM_Utils_System::redirect(CRM_Utils_System::url("civicrm/survey/configure/contact",
                                                       "action=update&reset=1&id={$this->_surveyId}"));
    }
  }

  function getTemplateFileName() {
    if ($this->controller->getPrint() == CRM_Core_Smarty::PRINT_NOFORM ||
      $this->getVar('_surveyId') <= 0 ||
      ($this->_action & CRM_Core_Action::DELETE)
    ) {
      return parent::getTemplateFileName();
    }
    else {
      return 'CRM/Campaign/Form/Survey/Tab.tpl';
    }
  }
}

