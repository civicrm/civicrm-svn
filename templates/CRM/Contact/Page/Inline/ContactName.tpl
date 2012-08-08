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
<div id="crm-contactname-content">
  {if $permission EQ 'edit'}
  <div class="crm-config-option">
      <a id="edit-contactname" class="hiddenElement crm-link-action" title="{ts}click to edit{/ts}">
      <span class="batch-edit"></span>{ts}edit{/ts}
    </a>
  </div>
  {/if}

  <div class="crm-summary-display_name">{$title}</div>
</div>

{if $permission EQ 'edit'}
{literal}
<script type="text/javascript">
cj(function(){
    cj('#contactname-block').mouseenter( function() {
      cj(this).addClass('crm-inline-edit-hover');
      cj('#edit-contactname').show();
    }).mouseleave( function() {
      cj(this).removeClass('crm-inline-edit-hover');
      cj('#edit-contactname').hide();
    });

    cj('#edit-contactname').click( function() {
      var dataUrl = {/literal}"{crmURL p='civicrm/ajax/inline' h=0 q='snippet=5&reset=1&cid='}{$contactId}"{literal}; 
      
      addCiviOverlay('.crm-summary-contactname-block');
      cj.ajax({
        data: { 'class_name':'CRM_Contact_Form_Inline_ContactName' },
        url: dataUrl,
        async: false
      }).done( function(response) {
        cj( '#contactname-block' ).html( response );
      });
      
      removeCiviOverlay('.crm-summary-contactname-block');
   });
});
</script>
{/literal}
{/if}
