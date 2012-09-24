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
 * Adds inline help
 *
 * @param array  $params the function params
 * @param object $smarty reference to the smarty object
 *
 * @return string the help html to be inserted
 * @access public
 */
function smarty_function_help($params, &$smarty) {
  if (!isset($params['id']) || !isset($smarty->_tpl_vars['config'])) {
    return;
  }

  $help = '';
  if (isset($params['text'])) {
    $help = '<div class="crm-help">' . $params['text'] . '</div>';
  }

  if (isset($params['file'])) {
    $file = $params['file'];
  }
  elseif (isset($smarty->_tpl_vars['tplFile'])) {
    $file = $smarty->_tpl_vars['tplFile'];
  }
  else {
    return $help;
  }

  $file = str_replace(array('.tpl', '.hlp'), '', $file);
  if ($params['id'] == 'accesskeys') {
    $file = 'CRM/common/accesskeys';
  }
  $smarty->assign('id', $params['id'] . '-title');
  $name = trim($smarty->fetch($file . '.hlp'));
  $title = ts('%1 Help', array(1 => $name));
  return '<a class="helpicon" title="'.$title.'" href="javascript:cj().crmTooltip(\''.$params['id'].'\', \''.$file.'\', \''.$name.'\')">&nbsp;</a>';
}

