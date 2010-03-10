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
	<td>{$form.target_id.label}</td>
	<td>{$form.target_id.html}</td>
     </tr>
     <tr>	
     	<td>{$form.case_subject.label}</td>
	<td>{$form.case_subject.html}</td>
     </tr>
</table>     	
</div>

{literal}
<script type="text/javascript">

var unclosedCaseUrl = {/literal}"{crmURL p='civicrm/case/ajax/unclosed' h=0 q='currentCaseId='}"{literal};

var caseId    = '';
var contactId = '';

cj( "#unclosed_cases" ).autocomplete( unclosedCaseUrl, { width : 250, selectFirst : false, matchContains:true
                            }).result( function(event, data, formatted) { 
			             cj( "#unclosed_case_id" ).val( data[1] );
				     caseId    = data[1];
				     contactId = data[2];
                            }).bind( 'click', function( ) { 
			             cj( "#unclosed_case_id" ).val(''); 
			    });


eval( 'tokenClass = { tokenList: "token-input-list-facebook", token: "token-input-token-facebook", tokenDelete: "token-input-delete-token-facebook", selectedToken: "token-input-selected-token-facebook", highlightedToken: "token-input-highlighted-token-facebook", dropdown: "token-input-dropdown-facebook", dropdownItem: "token-input-dropdown-item-facebook", dropdownItem2: "token-input-dropdown-item2-facebook", selectedDropdownItem: "token-input-selected-dropdown-item-facebook", inputToken: "token-input-input-token-facebook" } ');

var tokenDataUrl  = "{/literal}{$tokenUrl}{literal}";

var hintText = "{/literal}{ts}Type in a partial or complete name or email address of an existing contact.{/ts}{literal}";
cj( "#target_id"  ).tokenInput( tokenDataUrl, { prePopulate: target_contacts,   classes: tokenClass, hintText: hintText });

var target_contacts = '';
{/literal}

{if $targetContactValues}
{foreach from=$targetContactValues key=id item=name}
     {literal} target_contacts += '{"name":"'+{/literal}"{$name}"{literal}+'","id":"'+{/literal}"{$id}"{literal}+'"},';{/literal}
{/foreach}
{literal} eval( 'target_contacts = [' + target_contacts + ']'); {/literal}
{/if}

{literal}
var target_id = null;
var toDataUrl = "{/literal}{crmURL p='civicrm/ajax/checkemail' q='id=1&noemail=1' h=0 }{literal}";
 {/literal}
{if $form.$currentElement.value}
   {literal} var {/literal}{$currentElement}{literal} = cj.ajax({ url: toDataUrl + "&cid={/literal}{$form.$currentElement.value}{literal}", async: false }).responseText;{/literal}
{/if}


{literal}
if ( target_id ) {
  eval( 'target_contacts = ' + target_id );
}

cj( "#fileOnCaseDialog" ).hide( );
function fileOnCase( action, activityID ) {
   
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
    dataUrl = dataUrl + '&activityId=' + activityID + '&cid=' + {/literal}"{$contactID}"{literal};
    
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
			        var selected_case = cj("#unclosed_cases").val( );
				var case_subject       = cj("#case_subject").val( );
				var targetContactId = cj("#target_id").val( );
				
			        if ( ! selected_case ) {
					alert('{/literal}{ts}Please select a case from the list{/ts}{literal}.');
					return false;
				}
				
				var destUrl = {/literal}"{crmURL p='civicrm/contact/view/case' q='reset=1&action=view&id=' h=0 }"{literal}; 
						
				cj(this).dialog("close"); 
				cj(this).dialog("destroy");
									
				var postUrl = {/literal}"{crmURL p='civicrm/ajax/activity/convert' h=0 }"{literal};
			        cj.post( postUrl, { activityID: activityID, caseID: caseId, contactID: contactId, newSubject: case_subject, targetContactIds: targetContactId, mode: action },
					 function( values ) {
					      if ( values.error_msg ) {
                            		          alert( "{/literal}{ts}Unable to file on case{/ts}{literal}.\n\n" + values.error_msg );
						  return false;
                            		      } else { 
						  window.location.href = destUrl + caseId + '&cid=' + contactId;    
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