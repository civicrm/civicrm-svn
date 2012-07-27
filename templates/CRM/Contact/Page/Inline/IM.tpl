{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
{* template for building IM block*}
<div class="crm-table2div-layout" id="crm-im-content">
  <div class="crm-clear"> <!-- start of main -->
    {if $permission EQ 'edit'}
     {if $im}
     <div class="crm-config-option">
      <a id="edit-im" class="hiddenElement crm-link-action" title="{ts}click to add or edit im{/ts}">
        <span class="batch-edit"></span>{ts}add or edit im{/ts}
      </a>
    </div>
    {else}
      <div>
        <a id="edit-im" class="crm-link-action empty-im" title="{ts}click to add a im{/ts}">
          <span class="batch-edit"></span>{ts}add im{/ts}
        </a>
      </div>
     {/if}
    {/if}
    {foreach from=$im item=item}
      {if $item.name or $item.provider}
        {if $item.name}
        <div class="crm-label">{$item.provider}&nbsp;({$item.location_type})</div>
        <div class="crm-content crm-contact_im {if $item.is_primary eq 1} primary{/if}">{$item.name}</div>
        {/if}
      {/if}
    {/foreach}
   </div> <!-- end of main -->
</div>

{if $permission EQ 'edit'}
{literal}
<script type="text/javascript">
cj(function(){
    cj('#im-block').mouseenter( function() {
      cj(this).addClass('crm-inline-edit-hover');
      cj('#edit-im').show();
    }).mouseleave( function() {
      cj(this).removeClass('crm-inline-edit-hover');
      if ( !cj('#edit-im').hasClass('empty-im') ) { 
      cj('#edit-im').hide();
      }
    });

    cj('#edit-im').click( function() {
        var dataUrl = {/literal}"{crmURL p='civicrm/ajax/inline' h=0 q='snippet=5&reset=1&cid='}{$contactId}"{literal}; 
        
        addCiviOverlay('.crm-summary-im-block');
        cj.ajax({ 
          data: { 'class_name':'CRM_Contact_Form_Inline_IM' },
          url: dataUrl,
          async: false
        }).done( function(response) {
          cj( '#im-block' ).html( response );
        });
        
        removeCiviOverlay('.crm-summary-im-block');
    });
});
</script>
{/literal}
{/if}
