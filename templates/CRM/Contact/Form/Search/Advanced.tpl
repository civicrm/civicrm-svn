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
{* Master tpl for Advanced Search *}

{include file="CRM/Contact/Form/Search/Intro.tpl"}


<div class="crm-accordion-wrapper crm-advanced_search_form-accordion crm-accordion-closed">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div>
  {if $savedSearch}
    {ts 1=$savedSearch.name}Edit %1 Smart Group Criteria{/ts}
  {else}
    {ts}Edit Search Criteria{/ts}
  {/if}
 </div>
 <div class="crm-accordion-body">
  {include file="CRM/Contact/Form/Search/AdvancedCriteria.tpl"}
 </div>
</div>  

{if $rowsEmpty}
    {include file="CRM/Contact/Form/Search/EmptyResults.tpl"}
{/if}

{if $rows}
    {* Search request has returned 1 or more matching rows. Display results and collapse the search criteria fieldset. *}

    {if ! $ssID}
        {* Don't collapse search criteria when we are editing smart group criteria. *}
        {assign var="showBlock" value="'searchForm_show'"}
        {assign var="hideBlock" value="'searchForm'"}
    {/if}
    
    <fieldset>
    
       {* This section handles form elements for action task select and submit *}
       {include file="CRM/Contact/Form/Search/ResultTasks.tpl"}

       {* This section displays the rows along and includes the paging controls *}
       <p>
       {include file="CRM/Contact/Form/Selector.tpl"}
       </p>

    </fieldset>
    {* END Actions/Results section *}

{/if}

{literal}
<script type="text/javascript">
cj(function() {
	cj().crmaccordions(); 
	});
</script>
{/literal}

