<?php
/*
 +----------------------------------------------------------------------+
 | CiviCRM version 1.0                                                  |
 +----------------------------------------------------------------------+
 | Copyright (c) 2005 Donald A. Lobo                                    |
 +----------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                      |
 |                                                                      |
 | CiviCRM is free software; you can redistribute it and/or modify it   |
 | under the terms of the Affero General Public License Version 1,      |
 | March 2002.                                                          |
 |                                                                      |
 | CiviCRM is distributed in the hope that it will be useful, but       |
 | WITHOUT ANY WARRANTY; without even the implied warranty of           |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                 |
 | See the Affero General Public License for more details at            |
 | http://www.affero.org/oagpl.html                                     |
 |                                                                      |
 | A copy of the Affero General Public License has been been            |
 | distributed along with this program (affero_gpl.txt)                 |
 +----------------------------------------------------------------------+
*/

/**
 * This is CiviCRM's Smarty gettext plugin
 *
 * @package CRM
 * @author Piotr Szotkowski <shot@caltha.pl>
 * @author Michal Mach <mover@artnet.org>
 * @copyright Donald A. Lobo 01/15/2005
 * $Id$
 *
 */

/** 
 * Smarty block function, provides gettext support for smarty.
 * See CRM_Core_I18n class documentation for details.
 *
 * @author Michal Mach <mover@artnet.org>
 * @copyright Donald A. Lobo 01/15/2005
 * $Id$
 */

function smarty_block_t( $params, $text, &$smarty )
{
   $i18n = CRM_Core_I18n::singleton( );
   return $i18n->crm_translate( $text, $params );
}

?>
