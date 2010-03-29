{* Search form and results for Activities *}
<div class="crm-form-block crm-search-form-block">
<div class="crm-accordion-wrapper crm-advanced_search_form-accordion {if $rows}crm-accordion-closed{else}crm-accordion-open{/if}">
 <div class="crm-accordion-header crm-master-accordion-header">
  <div class="icon crm-accordion-pointer"></div>
        {ts}Edit Search Criteria{/ts}
</div><!-- /.crm-accordion-header -->
<div class="crm-accordion-body">
  <div id="searchForm" class="form-item">
    {strip} 
        <table class="form-layout">
        <tr>
           <td class="font-size12pt" colspan="2">
               {$form.sort_name.label}&nbsp;&nbsp;{$form.sort_name.html|crmReplace:class:'twenty'}&nbsp;&nbsp;&nbsp;{$form.buttons.html}
           </td>       
        </tr>
     
        {include file="CRM/Activity/Form/Search/Common.tpl"}
     
        <tr>
           <td colspan="2">{$form.buttons.html}</td>
        </tr>
        </table>
    {/strip}
  </div>
</div>
</div>
</div>

{if $rowsEmpty || $rows }
<div class="crm-content-block">
{if $rowsEmpty}
	<div class="crm-results-block crm-results-block-empty">        
	{include file="CRM/Activity/Form/Search/EmptyResults.tpl"}
	</div>
{/if}

{if $rows}
	<div class="crm-results-block">
    {* Search request has returned 1 or more matching rows. *}

       {* This section handles form elements for action task select and submit *}
       <div class="crm-search-tasks">
       {include file="CRM/common/searchResultTasks.tpl"}
		</div>
       {* This section displays the rows along and includes the paging controls *}
	   <div class="crm-search-results">
       {include file="CRM/Activity/Form/Selector.tpl" context="Search"}
		</div>       
    {* END Actions/Results section *}
</div>
{/if}
</div>
{/if}
{literal}
<script type="text/javascript">
cj(function() {
   cj().crmaccordions(); 
});
</script>
{/literal}
