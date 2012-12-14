{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
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
{* this template is used for adding/editing/deleting financial type  *}
<div class="crm-form-block crm-search-form-block">
  <div class="crm-accordion-wrapper crm-activity_search-accordion {if $searchRows}crm-accordion-closed{else}crm-accordion-open{/if}">
    <div class="crm-accordion-header crm-master-accordion-header">
      <div class="icon crm-accordion-pointer"></div> 
      {ts}Edit Search Criteria{/ts}
    </div><!-- /.crm-accordion-header -->
 
    <div class="crm-accordion-body">
      <div id="searchForm" class="crm-block crm-form-block crm-contact-custom-search-activity-search-form-block">
        <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
        <table class="form-layout-compressed">
        </table> 
        <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="botttom"}</div>
      </div>
    </div>	
  </div>
</div>

{if $searchRows}
  <div id="ltype">
    <p></p>
    <div class="form-item">
      {strip}
      <table cellpadding="0" cellspacing="0" border="0">
        <thead class="sticky">
	<tr>
          <th scope="col" title="Select All Rows">{$form.toggleSelect.html}</th>
          {foreach from=$searchColumnHeader item=head}
	    <th>{$head}</th>
	  {/foreach}
          <th></th>
	</tr>
        </thead>

        {foreach from=$searchRows item=row}
        <tr id="row_{$row.id}"class="{cycle values="odd-row,even-row"} {$row.class}">
	  {assign var=cbName value=$row.checkbox}
          <td>{$form.$cbName.html}</td>
	  {foreach from=$searchColumnHeader item=rowValue key=rowKey}
	    <td>{$row.$rowKey}</td>
	  {/foreach}
	  <td>{$row.action}</td>  
        </tr>
        {/foreach}
      </table>
      {/strip}
    </div>
  </div>   
{/if}

