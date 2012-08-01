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
{* This file provides the template for inline editing of websites *}
<table class="crm-inline-edit-form">
    <tr>
      <td colspan="5">
        <div class="crm-submit-buttons"> 
          {include file="CRM/common/formButtons.tpl"}
        </div>
      </td>
    </tr>

    <tr>
      <td>{ts}Website{/ts}
        {help id="id-website" file="CRM/Contact/Form/Contact.hlp"}
        {if $actualBlockCount lt 5 }
          &nbsp;&nbsp;<span id="add-more-website" title="{ts}click to add more{/ts}"><a class="crm-link-action">{ts}add{/ts}</a></span>
        {/if}
      </td>
      <td>{ts}Website Type{/ts}</td>
      <td>&nbsp;</td>
    </tr>

    {section name='i' start=1 loop=$totalBlocks}
    {assign var='blockId' value=$smarty.section.i.index} 
    <tr id="Website_Block_{$blockId}" {if $blockId gt $actualBlockCount}class="hiddenElement"{/if}>
      <td>{$form.website.$blockId.url.html|crmReplace:class:twenty}&nbsp;</td>
      <td>{$form.website.$blockId.website_type_id.html}</td>
      <td>
        {if $blockId > 1} 
          <a class="crm-delete-website crm-link-action" title="{ts}delete website block{/ts}">{ts}delete{/ts}</a>
        {/if}
       </td>
    </tr>
    {/section}
</table>

{include file="CRM/Contact/Form/Inline/InlineCommon.tpl"}

{literal}
<script type="text/javascript">
    cj( function() {
      // handle delete of block
      cj('.crm-delete-website').click( function(){
        cj(this).closest('tr').each(function(){
          cj(this).find('input').val('');
          cj(this).addClass('hiddenElement');
        });
      });

      // add more and set focus to new row
      cj('#add-more-website').click(function() {
        var rowSelector = cj('tr[id^="Website_Block_"][class="hiddenElement"] :first').parent(); 
        rowSelector.removeClass('hiddenElement');
        var rowId = rowSelector.attr('id').replace('Website_Block_', '');
        cj('#website_' + rowId + '_url').focus();
        if ( cj('tr[id^="Website_Block_"][class="hiddenElement"]').length == 0  ) {
          cj('#add-more-website').hide();
        }
      });

      // error handling / show hideen elements duing form validation
      cj('tr[id^="Website_Block_"]' ).each( function() {
          if( cj(this).find('td:first span').length > 0 ) {
            cj(this).removeClass('hiddenElement');
          } 
      });

      // add ajax form submitting
      inlineEditForm( 'Website', 'website-block', {/literal}{$contactId}{literal} ); 
 
    });

</script>
{/literal}
