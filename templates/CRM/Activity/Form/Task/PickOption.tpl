{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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
*}
<div class="form-item">
<fieldset>
    <legend>{ts}Select Group of Contacts{/ts}</legend>
    <dl>
        <dt>{$form.with_contact.label}</dt><dd>{$form.with_contact.html}</dd>
        <dt>{$form.assigned_to.label}</dt><dd>{$form.assigned_to.html}</dd>
        <dt>{$form.created_by.label}</dt><dd>{$form.created_by.html}</dd>
        <dt></dt><dd>{include file="CRM/Activity/Form/Task.tpl"}</dd>
        <dt></dt><dd>{$form.buttons.html}</dd>
    </dl>
</fieldset>
</div>