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
<div class="crm-table2div-layout" id="crm-contactinfo-content">
    <div class="crm-clear"> <!-- start of main -->
      {if $permission EQ 'edit'}
      <div class="crm-config-option">
          <a id="edit-contactinfo" class="hiddenElement crm-link-action" title="{ts}click to add or edit{/ts}">
          <span class="batch-edit"></span>{ts}add or edit info{/ts}
        </a>
      </div>
      {/if}

      <div class="crm-label">{ts}Employer{/ts}</div>
      <div class="crm-content crm-contact-current_employer">
        {if !empty($current_employer_id)} 
        <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$current_employer_id`"}" title="{ts}view current employer{/ts}">{$current_employer}</a>
        {/if}
      </div>
      <div class="crm-label">{ts}Position{/ts}</div>
      <div class="crm-content crm-contact-job_title">{$job_title}</div>
      <div class="crm-label">{ts}Nickname{/ts}</div>
      <div class="crm-content crm-contact-nick_name">{$nick_name}</div>

      {if !empty($legal_name)}
      <div class="crm-label">{ts}Legal Name{/ts}</div>
      <div class="crm-content crm-contact-legal_name">{$legal_name}</div>
      {/if}
      {if $sic_code}
      <div class="crm-label">{ts}SIC Code{/ts}</div>
      <div class="crm-content crm-contact-sic_code">{$sic_code}</div>
      {/if}
      <div class="crm-label">{ts}Source{/ts}</div>
      <div class="crm-content crm-contact_source">{$source}</div>

    </div> <!-- end of main -->
  </div>

{if $permission EQ 'edit'}
{literal}
<script type="text/javascript">
cj(function(){
    cj('#contactinfo-block').mouseenter( function() {
      cj(this).addClass('crm-inline-edit-hover');
      cj('#edit-contactinfo').show();
    }).mouseleave( function() {
      cj(this).removeClass('crm-inline-edit-hover');
      cj('#edit-contactinfo').hide();
    });

    cj('#edit-contactinfo').click( function() {
      var dataUrl = {/literal}"{crmURL p='civicrm/ajax/inline' h=0 q='snippet=5&reset=1&cid='}{$contactId}"{literal}; 
      
      addCiviOverlay('.crm-summary-contactinfo-block');
      cj.ajax({
        data: { 'class_name':'CRM_Contact_Form_Inline_ContactInfo' },
        url: dataUrl,
        async: false
      }).done( function(response) {
        cj( '#contactinfo-block' ).html( response );
      });
      
      removeCiviOverlay('.crm-summary-contactinfo-block');
   });
});
</script>
{/literal}
{/if}
