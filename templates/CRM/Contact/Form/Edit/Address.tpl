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
{* This file provides the plugin for the Address block *}
{* @var $form Contains the array for the form elements and other form associated information assigned to the template by the controller*}
{* @var $blockId Contains the current address block id, and assigned in the  CRM/Contact/Form/Location.php file *}

{if $title and $className eq 'CRM_Contact_Form_Contact'}
<div id = "addressBlockId" class="crm-accordion-wrapper crm-address-accordion crm-accordion-closed">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	{$title}
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body" id="addressBlock">
{/if}

 <div id="Address_Block_{$blockId}" {if $className eq 'CRM_Contact_Form_Contact'} class="boxBlock crm-edit-address-block" {/if}>
  {if $blockId gt 1}<fieldset><legend>Additional Address</legend>{/if}
  <table class="form-layout-compressed crm-edit-address-form">
     <tr>
	 {if $className eq 'CRM_Contact_Form_Contact'}
        <td id='Address-Primary-html' colspan="2">
           <span class="crm-address-element location_type_id-address-element">{$form.address.$blockId.location_type_id.label}
           {$form.address.$blockId.location_type_id.html}</span>
           <span class="crm-address-element is_primary-address-element">{$form.address.$blockId.is_primary.html}</span>
           <span class="crm-address-element is_billing-address-element">{$form.address.$blockId.is_billing.html}</span>
        </td>
	 {/if}
        {if $blockId gt 0}
            <td>
                <a href="#" title="{ts}Delete Address Block{/ts}" onClick="removeBlock( 'Address', '{$blockId}' ); return false;">{ts}Delete this address{/ts}</a>
            </td>
        {/if}
     </tr>
     <tr>
        <td>
            {$form.use_shared_address.$blockId.html}{$form.use_shared_address.$blockId.label}{help id="id-sharedAddress" file="CRM/Contact/Form/Contact.hlp"}<br />
            <div id="shared-address-{$blockId}" class="hiddenElement">
               <table>
               {include file="CRM/Contact/Form/NewContact.tpl" blockNo="$blockId"}
               </table>
            </div>
        </td>
     </tr>
     <script type="text/javascript">
     {literal}
     cj( function( ) {
         // handle check / uncheck of checkbox
         cj( 'input[id^=use_shared_address]' ).click( function( ) {
             // get the block id
             var elementId = cj(this).attr( 'id' ).replace( ']', '').split('[');

             // based on checkbox, show or hide
             if ( cj(this).attr( 'checked') ) {
                 cj( '#shared-address-' + elementId[1]).show( );
             } else {
                 cj( '#shared-address-' + elementId[1]).hide( );
             }
         });
     });
     {/literal}
     </script>
     <tr><td>

     <table id="address_{$blockId}" style="display:block" class="form-layout-compressed">
         {* build address block w/ address sequence. *}
         {foreach item=addressElement from=$addressSequence}
              {include file=CRM/Contact/Form/Edit/Address/$addressElement.tpl}
         {/foreach}
         {include file=CRM/Contact/Form/Edit/Address/geo_code.tpl}
     </table>

     </td></tr>
  </table>
  <div class="crm-edit-address-custom_data"> 
  {include file="CRM/Contact/Form/Edit/Address/CustomData.tpl"}
  </div> 

  {if $className eq 'CRM_Contact_Form_Contact'}
      <div id="addMoreAddress{$blockId}" class="crm-add-address-wrapper">
          <a href="#" class="button" onclick="buildAdditionalBlocks( 'Address', '{$className}' );return false;"><span><div class="icon add-icon"></div>{ts}Another Address{/ts}</span></a>
      </div>
  {/if}

{if $title and $className eq 'CRM_Contact_Form_Contact'}
</div>
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->
{/if}
{literal}
<script type="text/javascript">
//to check if same location type is already selected.
function checkLocation( object, noAlert ) {
    var selectedText = cj( '#' + object + ' :selected').text();
	cj( 'td#Address-Primary-html select' ).each( function() {
		element = cj(this).attr('id');
		if ( cj(this).val() && element != object && selectedText == cj( '#' + element + ' :selected').text() ) {
			if ( ! noAlert ) {
			    var alertText = "{/literal}{ts escape='js'}Location type{/ts} {literal}" + selectedText + "{/literal} {ts escape='js'}has already been assigned to another address. Please select another location type for this address.{/ts}{literal}";
			    alert( alertText );
			}
			cj( '#' + object ).val('');
		}
	});
}
</script>
{/literal}
{literal}
<script type="text/javascript">
cj(function() {
   cj().crmaccordions(); 
});
</script>
{/literal}

