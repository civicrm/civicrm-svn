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

{if $buildOnBehalfForm or $onBehalfRequired}
<div id='onBehalfOfOrg' class="crm-section">
  <fieldset id="for_organization" class="for_organization-group">
  <legend>{$fieldSetTitle}</legend>
  {if ( $relatedOrganizationFound or $onBehalfRequired ) and !$organizationName}
    <div id='orgOptions' class="section crm-section">
       <div class="content">
        {$form.org_option.html}
       </div>
    </div>
  {/if}  

  <table id="select_org" class="form-layout-compressed">
    {foreach from=$form.onbehalf item=field key=fieldName}
      <tr>
       {if ( $fieldName eq 'organization_name' ) and $organizationName}
         <td id='org_name' class="label">{$field.label}</td>
         <td class="value">
            {$field.html|crmReplace:class:big}
            <span>
                ( <a href='#' id='createNewOrg' onclick='createNew( ); return false;'>{ts}Enter a new organization{/ts}</a> )
            </span>
            <div id="id-onbehalf-orgname-enter-help" class="description">
                {ts}Organization details have been prefilled for you. If this is not the organization you want to use, click "Enter a new organization" above.{/ts}
            </div>
         </td>
       {else}
         <td class="label">{$field.label}</td>
         <td class="value">
            {$field.html}
            {if $fieldName eq 'organization_name'}
                <div id="id-onbehalf-orgname-help" class="description">{ts}Start typing the name of an organization that you have saved previously to use it again. Otherwise click "Enter a new organization" above.{/ts}</div>
            {/if}
            </td>
       {/if}
      </tr>
    {/foreach}
  </table>
 
  <div>{$form.mode.html}</div>
</div>
{/if}

{literal}
<script type="text/javascript">
var onBehalfRequired = {/literal}"{$onBehalfRequired}"{literal};
cj( "div#id-onbehalf-orgname-help").hide( );

function showOnBehalf( onBehalfRequired )
{
    if ( cj( "#is_for_organization" ).attr( 'checked' ) || onBehalfRequired ) {
            cj( "#for_organization" ).html( '' );
            var reset   = {/literal}"{$reset}"{literal};
            var urlPath = {/literal}"{crmURL p=$urlPath h=0 q='snippet=4&onbehalf=1'}"{literal};
            urlPath     = urlPath  + {/literal}"{$urlParams}"{literal};
            if ( reset ) {
                urlPath = urlPath + '&reset=' + reset;
            }
       
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

function resetValues( filter )
{
   if ( filter ) {
       cj( "#select_org tr td" ).find( 'input[type=text], select, textarea' ).each(function( ) {
          if ( cj(this).attr('name') != 'onbehalf[organization_name]' ) {
              cj(this).val( '' );
          }
       });
   } else {
       cj( "#select_org tr td" ).find( 'input[type=text], select, textarea' ).each(function( ) {
          cj(this).val( '' );
       });
   }
   cj( "#select_org tr td" ).find( 'input[type=radio], input[type=checkbox]' ).each(function( ) {
      cj(this).attr('checked', false);
   });
}

cj( "#mode" ).hide( );
cj( "#mode" ).attr( 'checked', 'checked' );

{/literal}

{if ( $relatedOrganizationFound or $onBehalfRequired ) and $reset}
  {if $organizationName}

    {literal}
    setOrgName( );

    function createNew( ) 
    {
       if ( cj( "#mode" ).attr( 'checked' ) ) {
           $text = ' {/literal}{ts escape="js"}Use existing organization{/ts}{literal} ';
           cj( "#onbehalf_organization_name" ).removeAttr( 'readonly' );
           cj( "#mode" ).removeAttr( 'checked' );

           resetValues( false );
       } else {
           $text = ' {/literal}{ts escape="js"}Enter a new organization{/ts}{literal} ';
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
       var orgOption = cj( "input:radio[name=org_option]:checked" ).val( );
       selectCreateOrg( orgOption, false );

       cj( "input:radio[name='org_option']" ).click( function( ) {
          orgOption = cj( "input:radio[name='org_option']:checked" ).val( );
          selectCreateOrg( orgOption, true ); 
       });

       function selectCreateOrg( orgOption, reset )
       {
          if ( orgOption == 0 ) {
              cj( "div#id-onbehalf-orgname-help").show( );
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
              cj( "div#id-onbehalf-orgname-help").hide( );
              if ( reset ) {
	          resetValues( false );
              }
          }
       }

  {/literal}{/if}
   
  {* Javascript method to populate the location fields when a different existing related contact is selected *}
  {literal}
  function setLocationDetails( contactID ) 
  {
      resetValues( true );
      var locationUrl = {/literal}"{$locDataURL}"{literal} + contactID + "&ufId=" + {/literal}"{$profileId}"{literal};
      cj.ajax({
            url         : locationUrl,
            dataType    : "json",
            timeout     : 5000, //Time in milliseconds
            success     : function( data, status ) {
                for (var ele in data) { 
                   if ( data[ele].type == 'Radio' ) {
                       if ( data[ele].value ) {
                           cj( "input[name='"+ ele +"']" ).filter( "[value=" + data[ele].value + "]" ).attr( 'checked', 'checked' );
                       }
		   } else if ( data[ele].type == 'CheckBox' ) {
		       if ( data[ele].value ) {
                           cj( "input[name='"+ ele +"']" ).attr( 'checked','checked' );
                       }
                   } else {
                       cj( "#" + ele ).val( data[ele].value );
                   }
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