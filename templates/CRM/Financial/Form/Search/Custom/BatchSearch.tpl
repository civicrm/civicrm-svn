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
{* Template for "Sample" custom search component. *}
   <div id="enableDisableStatusMsg" class="success-status" style="display:none;"></div> 
<div class="crm-form-block crm-search-form-block">
<div class="crm-accordion-wrapper crm-activity_search-accordion {if $rows}crm-accordion-closed{else}crm-accordion-open{/if}">
 <div class="crm-accordion-header crm-master-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
   {ts}Edit Search Criteria{/ts}
</div><!-- /.crm-accordion-header -->
<div class="crm-accordion-body">
<div id="searchForm" class="crm-block crm-form-block crm-contact-custom-search-activity-search-form-block">
      <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
        <table class="form-layout-compressed">
            {* Loop through all defined search criteria fields (defined in the buildForm() function). *}
            {foreach from=$elements item=element}
                <tr class="crm-contact-custom-search-Constituent-search-form-block-{$element}">
                    <td class="label">{$form.$element.label}</td>
                    <td>
                                                   {$form.$element.html}
                        
                    </td>
                </tr>
            {/foreach}
        </table>
      <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
       
</div>
</div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->
</div><!-- /.crm-form-block -->

{if $rowsEmpty || $rows}

<div class="crm-content-block">
    {if $rowsEmpty}
	<div class="crm-results-block crm-results-block-empty">
    {include file="CRM/Contact/Form/Search/Custom/EmptyResults.tpl"}
    </div>
{/if}

{if $rows}
	<div class="crm-results-block">
    {* Search request has returned 1 or more matching rows. Display results and collapse the search criteria fieldset. *}
        
    {* This section handles form elements for action task select and submit *}
	<div class="crm-search-tasks">
	{include file="CRM/common/searchResultTasks.tpl"}
	</div>
    {* This section displays the rows along and includes the paging controls *}
    <div class="crm-search-results">
    
    {include file="CRM/common/pager.tpl" location="top"}

    {include file="CRM/common/pagerAToZ.tpl"}

    {strip}
    <table summary="{ts}Search results listings.{/ts}">
        <thead class="sticky">
            <th scope="col" title="Select All Rows">{$form.toggleSelect.html}</th>
            {foreach from=$columnHeaders item=header}
	    	     {if $header.name neq 'id' }
                    <th scope="col">
		    {assign var='key' value=$header.sort}
                 {if $header.sort eq 'assigned_number_trans' or $header.sort eq 'assigned_total' or !array_key_exists( $key, $sort->_response) }
                            {$header.name}
                        {else}
                            {assign var='key' value=$header.sort}
                            {$sort->_response.$key.link}
                        {/if}
                </th>	
		{/if}
            {/foreach}
            <th>&nbsp;</th>
        </thead>

        {counter start=0 skip=1 print=false}
        {foreach from=$rows item=row}
            <tr id='rowid{counter}' class="{cycle values="odd-row,even-row"}">
                {assign var=cbName value=$row.checkbox}
                <td>{$form.$cbName.html}</td>
                {foreach from=$columnHeaders item=header}
                       {assign var=fName value=$header.sort}
                        
			{if $fName eq 'sort_name'}
                            <td><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.id`"}">{$row.sort_name}</a></td> {elseif $fName neq 'id'} 
			    <td>{$row.$fName}</td>
		{/if}
		{/foreach}
                <td>{$row.action}</td>
            </tr>
        {/foreach}
    </table>
    {/strip}

<script type="text/javascript">
 {* this function is called to change the color of selected row(s) *}
    var fname = "{$form.formName}";	
    on_load_init_checkboxes(fname);
 </script>

{include file="CRM/common/pager.tpl" location="bottom"}


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

	
function closeReopen( recordID, op ) {

	var recordBAO = 'CRM_Core_BAO_Batch';	
	if ( op == 'close' ) {
       	   var st = {/literal}'{ts escape="js"}Close the Batch{/ts}'{literal};
    	} else if ( op == 'reopen' ) {
       	   var st = {/literal}'{ts escape="js"}Reopen the Batch{/ts}'{literal};
    	}

	cj("#enableDisableStatusMsg").show( );
	cj("#enableDisableStatusMsg").dialog({
		title: st,
		modal: true,
		bgiframe: true,
		position: "right",
		overlay: { 
			opacity: 0.5, 
			background: "black" 
		},

		open:function() {
       		        var postUrl = {/literal}"{crmURL p='civicrm/ajax/statusmsg' h=0 }"{literal};
		        cj.post( postUrl, { recordID: recordID, recordBAO: recordBAO, op: op  }, function( statusMessage ) {
			        if ( statusMessage.status ) {
 			            cj( '#enableDisableStatusMsg' ).show().html( statusMessage.status );
       	     		        }
			
	       	        }, 'json' );
		},
	
		buttons: { 
			"Cancel": function() { 
				cj(this).dialog("close"); 
			},
			"OK": function() {    
			        saveRecord( recordID, op, recordBAO);
			        cj(this).dialog("close");			        
			}
		} 
	});
}
		
function noServerResponse( ) {
    if ( !responseFromServer ) { 
        var serverError =  '{/literal}{ts escape="js"}There is no response from server therefore selected record is not updated.{/ts}{literal}'  + '&nbsp;&nbsp;<a href="javascript:hideEnableDisableStatusMsg();"><img title="{/literal}{ts escape="js"}close{/ts}{literal}" src="' +resourceBase+'i/close.png"/></a>';
        cj( '#enableDisableStatusMsg' ).show( ).html( serverError ); 
    }
}

function saveRecord( recordID, op, recordBAO) {
    cj( '#enableDisableStatusMsg' ).hide( );
    var postUrl = {/literal}"{crmURL p='civicrm/ajax/ar' h=0 }"{literal};
    //post request and get response
    cj.post( postUrl, { recordID: recordID, recordBAO: recordBAO, op:op, key: {/literal}"{crmKey name='civicrm/ajax/ar'}"{literal}  }, function( html ){
        responseFromServer = true;      
       
        //this is custom status set when record update success.
        if ( html.status == 'record-updated-success' ) {
               document.location.reload( );
           } 

              }, 'json' );

        //if no response from server give message to user.
        setTimeout( "noServerResponse( )", 1500 ); 
    }


</script>
{/literal}
