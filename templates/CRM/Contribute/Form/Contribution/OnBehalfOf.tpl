{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
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
{* This file provides the HTML for the on-behalf-of form. Can also be used for related contact edit form. *}
<div id='onBehalfOfOrg' class="crm-section"></div>

{if $buildOnBehalfForm}
  <fieldset id="for_organization" class="for_organization-group">
  <legend>{$fieldSetTitle}</legend>
  {if $relatedOrganizationFound and !$organizationName}
    <div id='orgOptions' class="section crm-section">
       <div class="content">{$form.org_option.html}</div>
    </div>
  {/if}  

  <table id="select_org" class="form-layout-compressed">
    {foreach from=$form.onbehalf item=field key=fieldName}
      <tr>
       {if ( $fieldName eq 'organization_name' ) and $organizationName}
         <td id='org_name' class="label">Renew Membership for:</td>
         <td class="value">{$field.html|crmReplace:class:big}
         <span>
         <a href='#' id='createNewOrg' onclick='createNew( ); return false;'>Create new organization</a>
         </span></td>
       {else}
         <td class="label">{$field.label}</td>
         <td class="value">{$field.html}</td>
       {/if}
      </tr>
    {/foreach}
  </table>
 
  <div>{$form.mode.html}</div>
{/if}

{literal}
<script type="text/javascript">

function showOnBehalf( )
{
   if ( cj( "#is_for_organization" ).attr( 'checked' ) ) {
       var urlPath = {/literal}"{crmURL p=$urlPath h=0 q='snippet=4&onbehalf=1'}"{literal};
       urlPath     = urlPath  + '&id=' + {/literal}{$pageId}{literal};
       
       cj.ajax({
                 url     : urlPath,
                 async   : false,
		 global  : false,
	         success : function ( content ) { 		
    	                       cj( "#onBehalfOfOrg" ).html( content );
                           }
       });
   } else {
       cj( "#onBehalfOfOrg" ).html('');	
       cj( "#for_organization" ).html( '' );
       return;
   }
}

cj( "#mode" ).hide( );
cj( "#mode" ).attr( 'checked', 'checked' );

{/literal}

{if $relatedOrganizationFound}
  {if $organizationName}

    {literal}
    setOrgName( );

    function createNew( ) 
    {
       if ( cj( "#mode" ).attr( 'checked' ) ) {
           $text = "Select existing organization";
           cj( "#org_name" ).text( "Organization Name" );
           cj( "#onbehalf_organization_name" ).removeAttr( 'readonly' );

           cj( "#select_org tr td input" ).each( function( ) {
              cj(this).val( '' );
           });
           cj( "#select_org tr td select" ).each( function( ) {
                 cj(this).val( '' );
           });
           cj( "#mode" ).removeAttr( 'checked' );
       } else {
           $text = "Create new organization";
           cj( "#org_name" ).text( "Renew Membership for:" );
           cj( "#mode" ).attr( 'checked', 'checked' );
           setOrgName( );
       }
       cj( "#createNewOrg" ).text( $text );
    }
 
    function setOrgName( )
    {
       var orgName = "{/literal}{$organizationName}{literal}";
       var orgId   = "{/literal}{$orgId}{literal}";
       cj( "#onbehalf_organization_name" ).val( orgName );
       cj( "#onbehalf_organization_name" ).attr( 'readonly', true );
       setLocationDetails( orgId );
    }

  {/literal}{else}{literal}

       cj( "#orgOptions" ).show( );
       var orgOption = 0;
       selectCreateOrg( orgOption );

       cj( "input:radio[name='org_option']" ).click( function( ) {
          orgOption = cj( "input:radio[name='org_option']:checked" ).val( );
          selectCreateOrg( orgOption ); 
       });

       function selectCreateOrg( orgOption )
       {
          if ( orgOption == 0 ) {
              var dataUrl = {/literal}"{$employerDataURL}"{literal};
	      cj( '#onbehalf_organization_name' ).autocomplete( dataUrl, 
                                                                { width         : 180, 
                                                                  selectFirst   : false,
                                                                  matchContains : true
              }).result( function( event, data, formatted ) {
                   cj('#onbehalf_organization_name').val( data[0] );
                   cj('#onbehalfof_id').val( data[1] );
                   setLocationDetails( data[1] );
              });
          } else if ( orgOption == 1 ) {
              cj( "input#onbehalf_organization_name" ).removeClass( 'ac_input' ).unautocomplete( );
              cj( "#select_org tr td input" ).each( function( ) {
                 cj(this).val( '' );
              });
              cj( "#select_org tr td select" ).each( function( ) {
                 cj(this).val( '' );
              });
          }
       }

  {/literal}{/if}
   
  {* Javascript method to populate the location fields when a different existing related contact is selected *}
  {literal}
  function setLocationDetails( contactID ) 
  {
      var locationUrl = {/literal}"{$locDataURL}"{literal} + contactID + "&ufId=" + {/literal}"{$profileId}"{literal};
      cj.ajax({
            url         : locationUrl,
            dataType    : "json",
            timeout     : 5000, //Time in milliseconds
            success     : function( data, status ) {
                for (var ele in data) {
                    cj( "#"+ele ).val( data[ele] );
                }
            },
            error       : function( XMLHttpRequest, textStatus, errorThrown ) {
                console.error("HTTP error status: ", textStatus);
            }
     });
  }

{/literal}{/if}{literal}
</script>
{/literal}
</fieldset>


{literal}
<script type="text/javascript">
{/literal}
{* If mid present in the url, take the required action (poping up related existing contact ..etc) *}
{if $membershipContactID}
    {literal}
    cj( function( ) {
        cj( '#organization_id' ).val("{/literal}{$membershipContactName}{literal}");
        cj( '#organization_name' ).val("{/literal}{$membershipContactName}{literal}");
        cj( '#onbehalfof_id' ).val("{/literal}{$membershipContactID}{literal}");
        setLocationDetails( "{/literal}{$membershipContactID}{literal}" );
    });
    {/literal}
{/if}
{literal}
</script>
{/literal}