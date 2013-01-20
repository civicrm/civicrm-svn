{*
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
*}

<div class="crm-block crm-form-block crm-campaign-survey-contact-form-block">
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>

  <table class="form-layout-compressed">
    <tr class="crm-campaign-survey-main-form-block-contact_profile_id">
      <td class="label">{$form.contact_profile_id.label}</td>
      <td class="view-value">{$form.contact_profile_id.html}&nbsp;
        <span class="profile-links"></span>

        <div class="description">{ts}Select the Profile for Survey.{/ts}</div>
        <div class="profile-create">
          <a href="{crmURL p='civicrm/admin/uf/group/add' q='reset=1&action=add'}" target="_blank">
          {ts}Click here for new profile{/ts}
          </a>
        </div>
      </td>
    </tr>
    <tr class="crm-campaign-survey-main-form-block-activity_profile_id">
      <td class="label">{$form.activity_profile_id.label}</td>
      <td class="view-value">{$form.activity_profile_id.html}&nbsp;
        <span class="profile-links"></span>

        <div class="description">{ts}Select the Profile for Survey.{/ts}</div>
        <div class="profile-create">
          <a href="{crmURL p='civicrm/admin/uf/group/add' q='reset=1&action=add'}" target="_blank">
          {ts}Click here for new profile{/ts}
          </a>
        </div>
      </td>
    </tr>
  </table>

  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
