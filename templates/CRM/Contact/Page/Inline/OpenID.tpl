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
{* template for building OpenID block*}
<div class="crm-table2div-layout" id="crm-openid-content">
  <div class="crm-clear"> <!-- start of main -->
    {if $permission EQ 'edit'}
      {if $openid}
        <div class="crm-config-option">
          <a id="edit-openid" class="hiddenElement crm-link-action" title="{ts}click to add or edit OpenID  {/ts}">
            <span class="batch-edit"></span>{ts}add or edit OpenID{/ts}
          </a>
        </div>
      {else}
        <div>
          <a id="edit-openid" class="crm-link-action empty-openid" title="{ts}click to add a OpenID{/ts}">
            <span class="batch-edit"></span>{ts}add OpenID{/ts}
          </a>
        </div>
      {/if}
    {/if}

    {foreach from=$openid item=item}
      {if $item.openid}
      <div class="crm-label">{$item.location_type}&nbsp;{ts}OpenID{/ts}</div>
      <div class="crm-content crm-contact_openid {if $item.is_primary eq 1} primary{/if}"><a href="{$item.openid}">{$item.openid|mb_truncate:40}</a>
      </div>
      {/if}
    {/foreach}
   </div> <!-- end of main -->
</div>

{if $permission EQ 'edit'}
{literal}
<script type="text/javascript">
cj(function(){
    cj('#openid-block').mouseenter( function() {
      cj(this).addClass('crm-inline-edit-hover');
      cj('#edit-openid').show();
    }).mouseleave( function() {
      cj(this).removeClass('crm-inline-edit-hover');
      if ( !cj('#edit-openid').hasClass('empty-openid') ) { 
      cj('#edit-openid').hide();
      }
    });

    cj('#edit-openid').click( function() {
      var dataUrl = {/literal}"{crmURL p='civicrm/ajax/inline' h=0 q='snippet=5&reset=1&cid='}{$contactId}"{literal}; 
      
      addCiviOverlay('.crm-summary-openid-block');
      cj.ajax({ 
        data: { 'class_name':'CRM_Contact_Form_Inline_OpenID' },
        url: dataUrl,
        async: false
      }).done( function(response) {
        cj( '#openid-block' ).html( response );
      });
      
      removeCiviOverlay('.crm-summary-openid-block');
    });
});
</script>
{/literal}
{/if}
