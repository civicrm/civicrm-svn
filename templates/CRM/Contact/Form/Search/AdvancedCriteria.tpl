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
{* Advanced Search Criteria Fieldset *}
{literal}
<script type="text/javascript">
// bind first click of accordion header to load crm-accordion-body with snippet
// everything else taken care of by cj().crm-accordions()
  cj(document).ready( function() {
    cj('.crm-search_criteria_basic-accordion .crm-accordion-header').addClass('active');
    cj('.crm-ajax-accordion').on('click', '.crm-accordion-header:not(.active)', function() {
      loadPanes(cj(this).attr('id'));
    });
    cj('.crm-ajax-accordion.crm-accordion-open .crm-accordion-header').each(function(index) {
      loadPanes(cj(this).attr('id'));
    });
    cj('.crm-ajax-accordion').on('click', '.close-accordion', function() {
      var header = cj(this).parent();
      header.next().html('');
      header.removeClass('active');
      header.parent().removeClass('crm-accordion-open').addClass('crm-accordion-closed');
      cj(this).remove();
      return false;
    });
  });
// load panes function calls for snippet based on id of crm-accordion-header
  function loadPanes( id ) {
    var url = "{/literal}{crmURL p='civicrm/contact/search/advanced' q="snippet=1&qfKey=`$qfKey`&searchPane=" h=0}{literal}" + id;
    var header = cj('#' + id);
    var body = cj('div.crm-accordion-body.' + id);
    if ( header.length > 0 && body.length > 0 && !body.html() ) {
      body.html('<div class="crm-loading-element"><span class="loading-text">{/literal}{ts}Loading{/ts}{literal}...</span></div>');
      cj.ajax({
        url : url,
        success: function(data) {
          body.html(data);
          header.addClass('active');
          header.append('<a href="#" class="close-accordion" title="{/literal}{ts}Remove from search criteria{/ts}{literal}">{/literal}{ts}Reset{/ts}{literal} [x]</a>');
        }
      });
    }
  }
</script>
{/literal}

    {if $context EQ 'smog' || $context EQ 'amtg' || $savedSearch}
          <h3>
          {if $context EQ 'smog'}{ts}Find Contacts within this Group{/ts}
          {elseif $context EQ 'amtg'}{ts}Find Contacts to Add to this Group{/ts}
          {elseif $savedSearch}{ts 1=$savedSearch.name}%1 Smart Group Criteria{/ts} &nbsp; {help id='id-advanced-smart'}
          {/if}
          </h3>
        {/if}

{strip}
<div class="crm-accordion-wrapper crm-search_criteria_basic-accordion crm-accordion-open">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div>
  {ts}Basic Criteria{/ts}
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">
        {include file="CRM/Contact/Form/Search/Criteria/Basic.tpl"}
    </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

    {foreach from=$allPanes key=paneName item=paneValue}
      <div class="crm-accordion-wrapper crm-ajax-accordion crm-{$paneValue.id}-accordion {if $paneValue.open eq 'true' and $openedPanes.$paneName}crm-accordion-open{else}crm-accordion-closed{/if}">
       <div class="crm-accordion-header" id="{$paneValue.id}">
         <div class="icon crm-accordion-pointer"></div>
         {$paneName}
       </div>
       <div class="crm-accordion-body {$paneValue.id}"></div>
       </div>
    {/foreach}
    <div class="spacer"></div>

    <table class="form-layout">
        <tr>
            <td>{$form.buttons.html}</td>
        </tr>
    </table>
{/strip}
