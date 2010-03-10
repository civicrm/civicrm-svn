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
{* CiviCase -  build activity to a case*}
<div id="fileOnCaseDialog">
<table class="form-layout">
     <tr>
	<td>{$form.unclosed_cases.label}</td>
     	<td>{$form.unclosed_cases.html}</td>
     </tr>
     <tr>
	<td>{$form.target_contact_id.label}</td>
	<td>{$form.target_contact_id.html}</td>
     </tr>
     <tr>	
     	<td>{$form.case_activity_subject.label}</td>
	<td>{$form.case_activity_subject.html}</td>
     </tr>
</table>     	
</div>

{literal}
<script type="text/javascript">
var target_contact = target_contact_id = selectedCaseId = contactId = '';

var unclosedCaseUrl = {/literal}"{crmURL p='civicrm/case/ajax/unclosed' h=0 q='currentCaseId='}{$currentCaseId}"{literal};
cj( "#unclosed_cases" ).autocomplete( unclosedCaseUrl, { width : 250, selectFirst : false, matchContains:true
                                    }).result( function(event, data, formatted) { 
			                          cj( "#unclosed_case_id" ).val( data[1] );
				                  contactId = data[2];
				                  selectedCaseId = data[1];
                                              }).bind( 'click', function( ) { 
			                          cj( "#unclosed_case_id" ).val('');
						  contactId = selectedCaseId = ''; 
			                      });
{/literal}
{if $targetContactValues}
{foreach from=$targetContactValues key=id item=name}
   {literal} 
   target_contact += '{"name":"'+{/literal}"{$name}"{literal}+'","id":"'+{/literal}"{$id}"{literal}+'"},';{/literal}
{/foreach}
   {literal}
   eval( 'target_contact = [' + target_contact + ']'); 
   {/literal}
{/if}

{if $form.target_contact_id.value}
     {literal}
     var toDataUrl = "{/literal}{crmURL p='civicrm/ajax/checkemail' q='id=1&noemail=1' h=0 }{literal}"; 
     var target_contact_id = cj.ajax({ url: toDataUrl + "&cid={/literal}{$form.$currentElement.value}{literal}", async: false }).responseText;
     {/literal}
{/if}

{literal}
if ( target_contact_id ) {
  eval( 'target_contact = ' + target_contact_id );
}

eval( 'tokenClass = { tokenList: "token-input-list-facebook", token: "token-input-token-facebook", tokenDelete: "token-input-delete-token-facebook", selectedToken: "token-input-selected-token-facebook", highlightedToken: "token-input-highlighted-token-facebook", dropdown: "token-input-dropdown-facebook", dropdownItem: "token-input-dropdown-item-facebook", dropdownItem2: "token-input-dropdown-item2-facebook", selectedDropdownItem: "token-input-selected-dropdown-item-facebook", inputToken: "token-input-input-token-facebook" } ');

var tokenDataUrl  = "{/literal}{$tokenUrl}{literal}";
var hintText = "{/literal}{ts}Type in a partial or complete name or email address of an existing contact.{/ts}{literal}";
cj( "#target_contact_id" ).tokenInput(tokenDataUrl,{prePopulate: target_contact, classes: tokenClass, hintText: hintText });

cj( "#fileOnCaseDialog" ).hide( );

function fileOnCase( action, activityID, currentCaseId ) {
    if ( action == "move" ) {
        dialogTitle = "Move to Case";
    } else if ( action == "copy" ) {
      	dialogTitle = "Copy to Case";
    } else if ( action == "file" ) {
      	dialogTitle = "File On Case";   
    }

    if ( !activityID ) {
	activityID = {/literal}"{$entityID}"{literal};
    }
    
    var dataUrl = {/literal}"{crmURL p='civicrm/case/addToCase' q='reset=1&snippet=4' h=0}"{literal};
    dataUrl = dataUrl + '&activityId=' + activityID + '&caseId=' + currentCaseId + '&cid=' + {/literal}"{$contactID}"{literal};

    cj.ajax({
              url     : dataUrl,
	      success : function ( content ) { 		
    	                   cj("#fileOnCaseDialog").show( ).html( content ).dialog({
		             title       : dialogTitle,
		             modal       : true,
			     bgiframe    : true,
	    	             width       : 600,
		             height      : 300,
		             overlay     : { opacity: 0.5, background: "black" },
		             beforeclose : function( event, ui ) {
                                              cj(this).dialog("destroy");
                                           },

  		             open        : function() {  },

	      buttons : { 
			"Ok": function() { 
				var subject         = cj("#case_activity_subject").val( );
				var targetContactId = cj("#target_contact_id").val( );
				
			        if ( !cj("#unclosed_cases").val( )  ) {
			           alert('{/literal}{ts}Please select a case from the list{/ts}{literal}.');
				   return false;
				}
						
				cj(this).dialog("close"); 
				cj(this).dialog("destroy");
									
				var postUrl = {/literal}"{crmURL p='civicrm/ajax/activity/convert' h=0 }"{literal};
			        cj.post( postUrl, { activityID: activityID, caseID: selectedCaseId, contactID: contactId, newSubject: subject, targetContactIds: targetContactId, mode: action },
					 function( values ) {
					      if ( values.error_msg ) {
                            		          alert( "{/literal}{ts}Unable to file on case{/ts}{literal}.\n\n" + values.error_msg );
						  return false;
                            		      } else {
					          var destUrl = {/literal}"{crmURL p='civicrm/contact/view/case' q='reset=1&action=view&id=' h=0 }"{literal}; 
						  window.location.href = destUrl + selectedCaseId + '&cid=' + contactId;    
					      }
                        	         }
                    		      );
			},

			"Cancel": function() { 
				cj(this).dialog("close"); 
				cj(this).dialog("destroy"); 
			} 
		} 

	   });
       }
  });
}
</script>
{/literal}