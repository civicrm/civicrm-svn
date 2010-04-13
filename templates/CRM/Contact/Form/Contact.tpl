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
{* This form is for Contact Add/Edit interface *}
{if $addBlock}
{include file="CRM/Contact/Form/Edit/$blockName.tpl"}
{else}
<div class="crm-form-block crm-search-form-block">
<span style="float:right;"><a href="#expand" id="expand">{ts}Expand all tabs{/ts}</a></span>
<div class="crm-submit-buttons">
   {include file="CRM/common/formButtons.tpl"}
</div>
<div class="crm-accordion-wrapper crm-contactDetails-accordion crm-accordion-open">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	{ts}Contact Details{/ts}
	
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body" id="contactDetails">
    <div id="contactDetails">
        {include file="CRM/Contact/Form/Edit/$contactType.tpl"}
        <br/>
        <table class="form-layout-compressed">
            {foreach from=$blocks item="label" key="block"}
               {include file="CRM/Contact/Form/Edit/$block.tpl"}
            {/foreach}
		</table>
		<table class="form-layout-compressed">
            <tr class="last-row">
              <td>{$form.contact_source.label}<br />
                  {$form.contact_source.html}
              </td>
              <td>{$form.external_identifier.label}<br />
                  {$form.external_identifier.html}
              </td>
              {if $contactId}
				<td><label for="internal_identifier">{ts}Internal Id{/ts}</label><br />{$contactId}</td>
			  {/if}
            </tr>            
        </table>
	<table>
	    <tr>
	      <td>{$form.image_URL.label}</td>
	      <td>{$form.image_URL.html}
 	          {if $imageURL}
 	               {include file="CRM/Contact/Page/ContactImage.tpl"}
 	          {/if}
 	      </td>
 	    </tr>
        </table>

        {*  add dupe buttons *}
        <span class="crm-button crm-button_qf_Contact_refresh_dedupe">
            {$form._qf_Contact_refresh_dedupe.html}
        </span>
        {if $isDuplicate}
            &nbsp;&nbsp;
            <span class="crm-button crm-button_qf_Contact_upload_duplicate">
                {$form._qf_Contact_upload_duplicate.html}
            </span>
        {/if}
        <div class="spacer"></div>
   </div>
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->
<div id='customData'></div>  
    {foreach from = $editOptions item = "title" key="name"}
        {include file="CRM/Contact/Form/Edit/$name.tpl"}
    {/foreach}
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl"}
</div>

</div>
{literal}
<script type="text/javascript" >
var action = "{/literal}{$action}{literal}";
var removeCustomData = true;
showTab[0] = {"spanShow":"span#contact","divShow":"div#contactDetails"};
cj(function( ) {
	cj(showTab).each( function(){ 
        if( this.spanShow ) {
            cj(this.spanShow).removeClass( ).addClass('crm-accordion-open');
            cj(this.divShow).show( );
        }
    });

	cj('.crm-accordion-body').each( function() {
		//remove tab which doesn't have any element
		if ( ! cj.trim( cj(this).text() ) ) { 
			ele     = cj(this);
			prevEle = cj(this).prev();
			cj( ele ).remove();
			cj( prevEle).remove();
		}
		//open tab if form rule throws error
		if ( cj(this).children().find('span.crm-error').text() ) {
			cj(this).show().prev().children('span:first').removeClass( 'crm-accordion-closed' ).addClass('crm-accordion-open');
		}
	});

	highlightTabs( );
});

cj('a#expand').click( function( ){
    if( cj(this).attr('href') == '#expand') {   
        var message     = {/literal}"{ts}Collapse all tabs{/ts}"{literal};
        cj(this).attr('href', '#collapse');
        cj('.crm-accordion-closed').removeClass('crm-accordion-closed').addClass('crm-accordion-open');
    } else {
        var message     = {/literal}"{ts}Expand all tabs{/ts}"{literal};
        cj('.crm-accordion-open').removeClass('crm-accordion-open').addClass('crm-accordion-closed');
        cj(this).attr('href', '#expand');
    }
    cj(this).html(message);
});

//current employer default setting
var employerId = "{/literal}{$currentEmployer}{literal}";
if ( employerId ) {
    var dataUrl = "{/literal}{crmURL p='civicrm/ajax/contactlist' h=0 q="org=1&id=" }{literal}" + employerId ;
    cj.ajax({ 
        url     : dataUrl,   
        async   : false,
        success : function(html){
            //fixme for showing address in div
            htmlText = html.split( '|' , 2);
            cj('input#current_employer').val(htmlText[0]);
            cj('input#current_employer_id').val(htmlText[1]);
        }
    }); 
}

cj("input#current_employer").click( function( ) {
    cj("input#current_employer_id").val('');
});

function showHideSignature( blockId ) {
    cj('#Email_Signature_' + blockId ).toggle( );   
}

function highlightTabs( ) {
    if ( action == 2 ) {
	//highlight the tab having data inside.
	cj('.crm-accordion-body :input').each( function() { 
		var element = cj(this).closest(".crm-accordion-body").attr("id");
		if (element) {
		eval('var ' + element + ' = "";');
		switch( cj(this).attr('type') ) {
		case 'checkbox':
		case 'radio':
		  if( cj(this).is(':checked') ) {
		    eval( element + ' = true;'); 
		  }
		  break;
		  
		case 'text':
		case 'textarea':
		  if( cj(this).val() ) {
		    eval( element + ' = true;');
		  }
		  break;
		  
		case 'select-one':
		case 'select-multiple':
		  if( cj('select option:selected' ) && cj(this).val() ) {
		    eval( element + ' = true;');
		  }
		  break;		
		  
		case 'file':
		  if( cj(this).next().html() ) eval( element + ' = true;');
		  break;
  		}
		if( eval( element + ';') ) { 
		  cj(this).closest(".crm-accordion-wrapper").addClass('crm-accordion-hasContent');
		}
	     }
       });
    }
}

function removeDefaultCustomFields( ) {
     //execute only once
     if (removeCustomData) {
	 cj(".crm-accordion-wrapper").children().each( function() {
	    var eleId = cj(this).attr("id");
	    if ( eleId.substr(0,10) == "customData" ) { cj(this).parent("div").remove(); }
	 });
	 removeCustomData = false;
     }
}
 
</script>
{/literal}

{* include common additional blocks tpl *}
{include file="CRM/common/additionalBlocks.tpl"}

{* include jscript to warn if unsaved form field changes *}
{include file="CRM/common/formNavigate.tpl"}

{/if}