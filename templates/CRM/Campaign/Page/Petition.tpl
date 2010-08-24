{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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
{* this template is used for displaying signature information *}

{if $signatures} 

 {include file="CRM/common/enableDisable.tpl"}
 {include file="CRM/common/jsortable.tpl"}
  <div id="signatureList">
    <table id="options" class="display">
      <thead>
        <tr>
		  <th>{ts}Petition ID{/ts}</th>
		  <th>{ts}Petition Title{/ts}</th> 
          <th>{ts}Signed By{/ts}</th>
		  <th>{ts}Date{/ts}</th>
		  <th>{ts}Status{/ts}</th>	  
        </tr>
      </thead>
      {foreach from=$signatures item=signature}
        <tr id="row_{$signature.id}">
          <td>{$signature.source_record_id}</td>
          <td>{$signature.survey_title}</td>
          <td><a href="/civicrm/contact/view?reset=1&cid={$signature.contactId}#Activities">{$signature.source_contact_id}</a></td>
          <td>{$signature.activity_date_time}</td>
          <td>{if $signature.status_id eq 1}Unconfirmed{/if}{if $signature.status_id eq 2}Confirmed{/if}</td>
        </tr>
      {/foreach}
    </table>
  </div>

{else} 
  {ts} No signature found!    {/ts} 
{/if}
