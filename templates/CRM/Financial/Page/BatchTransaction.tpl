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

 <div id="enableDisableStatusMsg" class="success-status" style="display:none;"></div> 
    <div id="help">
        <p>{ts}Financial types are used to categorize contributions for reporting and accounting purposes. These are also referred to as <strong>Funds</strong>. You may set up as many types as needed. Each type can carry an accounting code which can be used to map contributions to codes in your accounting system. Commonly used financial types are: Donation, Campaign Contribution, Membership Dues...{/ts}</p>
    </div>

{if $rows}
<div id="ltype">
<p></p>
    <div class="form-item">
        {strip}
	{* handle enable/disable actions*}
 	{include file="CRM/common/enableDisable.tpl"}
        <table cellpadding="0" cellspacing="0" border="0">
           <thead class="sticky">
	    <tr>
            <th scope="col" title="Select All Rows">{$form.toggleSelects.html}</th>
             {foreach from=$columnHeader item=head}
	     <th>{$head}</th>
	     {/foreach}
            <th></th>
	    </tr>
          </thead>

         {foreach from=$rows item=row}
        <tr id="row_{$row.id}"class="{cycle values="odd-row,even-row"} {$row.class}">
	 {assign var=cbName value=$row.checkbox}
                <td>{$form.$cbName.html}</td>
	    {foreach from=$columnHeader item=rowValue key=rowKey}
	    <td>{$row.$rowKey}</td>
	    {/foreach}
	   <td>{$row.action}</td>  
        </tr>
        {/foreach}
         </table>
        {/strip}

        {if $action ne 1 and $action ne 2}
	    <div class="action-link">
    	<a href="{crmURL q="action=add&reset=1"}" id="newFinancialType" class="button"><span><div class="icon add-icon"></div>{ts}Add Financial Type{/ts}</span></a>
        </div>
        {/if}
    </div>
</div>
{else}
    <div class="messages status">
        <div class="icon inform-icon"></div>
        {capture assign=crmURL}{crmURL q="action=add&reset=1"}{/capture}
        {ts 1=$crmURL}There are no Financial Types entered. You can <a href='%1'>add one</a>.{/ts}
    </div>    
{/if}
 {include file="CRM/Financial/Form/BatchTransaction.tpl"}


 {literal}
<script type="text/javascript">
	
	
function assignRemove( recordID, op ) {

	var recordBAO = 'CRM_Financial_BAO_EntityFinancialItem';	
	if ( op == 'remove' ) {
       	   var st = {/literal}'{ts escape="js"}Remove from Batch{/ts}'{literal};
    	} else if ( op == 'assign' ) {
       	   var st = {/literal}'{ts escape="js"}Assign to Batch{/ts}'{literal};
    	}
	var entityID = {/literal}"{$entityID}"{literal};
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
			        saveRecord( recordID, op, recordBAO, entityID);
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

function saveRecord( recordID, op, recordBAO, entityID ) {
    cj( '#enableDisableStatusMsg' ).hide( );
    var postUrl = {/literal}"{crmURL p='civicrm/ajax/ar' h=0 }"{literal};
alert(entityID);
    //post request and get response
    cj.post( postUrl, { recordID: recordID, recordBAO: recordBAO, op:op, entityID:entityID, key: {/literal}"{crmKey name='civicrm/ajax/ar'}"{literal}  }, function( html ){
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