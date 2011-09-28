{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
<div class="crm-block crm-form-block crm-note-form-block">
    <div class="content crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
        <table class="form-layout">
            <tr>
                <td class="label">{$form.name.label}</td><td>{$form.name.html}</td>
            </tr>
            <tr>
                <td class="label">{$form.cms_name.label}</td><td>{$form.cms_name.html}</td>
            </tr>
            <tr>
                <td class="label">{$form.cms_pass.label}</td><td>{$form.cms_pass.html}</td>
            </tr>
            <tr>
                <td class="label">{$form.cms_confirm_pass.label}</td><td>{$form.cms_confirm_pass.html}</td>
            </tr>
            <tr>
                <td class="label">{$form.email.label}</td><td>{$form.email.html}</td>
            </tr>
        </table>


    <div class="crm-section note-buttons-section no-label">
     <div class="content crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
     <div class="clear"></div> 
    </div>
    </div>

</div>
