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
{* template for building website block*}
<div class="crm-table2div-layout" id="crm-website-content">
  <div class="crm-clear"> <!-- start of main -->
    {if $permission EQ 'edit'}
      {if $website}
        <div class="crm-config-option">
          <a id="edit-website" class="hiddenElement crm-link-action" title="{ts}click to add or edit website numbers{/ts}">
            <span class="batch-edit"></span>{ts}add or edit website{/ts}
          </a>
        </div>
      {else}
        <div>
          <a id="edit-website" class="crm-link-action empty-website" title="{ts}click to add a website number{/ts}">
            <span class="batch-edit"></span>{ts}add website{/ts}
          </a>
        </div>
      {/if}
    {/if}

    {foreach from=$website item=item}
      {if !empty($item.url)}
      <div class="crm-label">{$item.website_type} {ts}Website{/ts}</div>
      <div class="crm-content crm-contact_website"><a href="{$item.url}" target="_blank">{$item.url}</a></div>
      {/if}
    {/foreach}

    </div> <!-- end of main -->
</div>

{if $permission EQ 'edit'}
{literal}
<script type="text/javascript">
cj(function(){
    cj('#website-block').mouseenter( function() {
      cj(this).addClass('crm-inline-edit-hover');
      cj('#edit-website').show();
    }).mouseleave( function() {
      cj(this).removeClass('crm-inline-edit-hover');
      if ( !cj('#edit-website').hasClass('empty-website') ) { 
        cj('#edit-website').hide();
      }
    });

    cj('#edit-website').click( function() {
        var dataUrl = {/literal}"{crmURL p='civicrm/ajax/inline' h=0 q='snippet=5&reset=1&cid='}{$contactId}"{literal}; 
        
        addCiviOverlay('.crm-summary-website-block');
        cj.ajax({ 
              data: { 'class_name':'CRM_Contact_Form_Inline_Website' },
              url: dataUrl,
              async: false
        }).done( function(response) {
          cj( '#website-block' ).html( response );
        });
        
      removeCiviOverlay('.crm-summary-website-block');
    });
});
</script>
{/literal}
{/if}
