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
{* Search form and results for voters *}
{assign var="showBlock" value="'searchForm'"}
{assign var="hideBlock" value="'searchForm_show'"}
<div class="crm-block crm-form-block crm-search-form-block">
<div id="searchForm_show" class="form-item">
  <a href="#" onclick="hide('searchForm_show'); show('searchForm'); return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="{ts}open section{/ts}" /></a>
  <label>
        {ts}Edit Search Criteria{/ts}
  </label>
</div>

<div id="searchForm" class="form-item">
    {strip} 
        <table class="form-layout">
        <tr>
            <td class="font-size12pt">
                {$form.sort_name.label}
            </td>
            <td>			
		{$form.sort_name.html|crmReplace:class:'twenty'}
            </td>       
        </tr>
	<tr>
            <td class="font-size12pt">
                {$form.street_name.label}
       	    </td>
            <td>	
                {$form.street_name.html}
            </td>
	</tr>	

	<tr>
            <td class="font-size12pt">
                {$form.street_number.label}
       	    </td>
            <td>	
                {$form.street_number.html}
            </td>
	</tr>

        <tr>
            <td class="font-size12pt">
                {$form.street_type.label}
       	    </td>
            <td>	
                {$form.street_type.html}
            </td>
	</tr>

	<tr>
            <td class="font-size12pt">
                {$form.street_address.label}
	    </td>
            <td>
                {$form.street_address.html}
            </td>
	</tr>
	<tr>
            <td class="font-size12pt">
                {$form.city.label}
            </td>
            <td>
                {$form.city.html}
            </td>
	</tr>
	<tr>
            <td class="font-size12pt">
                {$form.status_id.label}
            </td>
            <td>
	        {$form.status_id.html}
            </td>
	</tr>
	<tr>
            <td class="font-size12pt">
                {$form.campaign_survey_id.label}
            </td>
            <td>
	        {$form.campaign_survey_id.html}
            </td>
	</tr>
        <tr>
           <td colspan="2">{$form.buttons.html}</td>
        </tr>
        </table>
    {/strip}

</div>
</div>
    {if $rowsEmpty || $rows}
<div class="crm-content-block">
{if $rowsEmpty}
    {include file="CRM/Campaign/Form/Search/EmptyResults.tpl"}
{/if}

{if $rows}
    {* Search request has returned 1 or more matching rows. Display results and collapse the search criteria fieldset. *}
    {assign var="showBlock" value="'searchForm_show'"}
    {assign var="hideBlock" value="'searchForm'"}
    
    {* Search request has returned 1 or more matching rows. *}
    <fieldset>
    
       {* This section handles form elements for action task select and submit *}
       {include file="CRM/common/searchResultTasks.tpl" context="Campaign"}

       {* This section displays the rows along and includes the paging controls *}
       <p></p>
       {include file="CRM/Campaign/Form/Selector.tpl" context="Search"}
       
    </fieldset>
    {* END Actions/Results section *}

{/if}
</div>
{/if}
<script type="text/javascript">
    var showBlock = new Array({$showBlock});
    var hideBlock = new Array({$hideBlock});

{* hide and display the appropriate blocks *}
    on_load_init_blocks( showBlock, hideBlock );
</script>
