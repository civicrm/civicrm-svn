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
{* This form is for Contact Add/Edit interface *}
{if $addBlock}
  {include file="CRM/Contact/Form/Edit/$blockName.tpl"}
{else}
  {include file="CRM/Contact/Form/Edit/Lock.tpl"}
  <div class="crm-form-block crm-search-form-block">
    {if call_user_func(array('CRM_Core_Permission','check'), 'administer CiviCRM') }
      <a href='{crmURL p="civicrm/admin/setting/preferences/display" q="reset=1"}' title="{ts}Click here to configure the panes.{/ts}"><span class="icon settings-icon"></span></a>
    {/if}
    <span style="float:right;"><a href="#expand" id="expand">{ts}Expand all tabs{/ts}</a></span>
    <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
    </div>

    {* include overlay js *}
    {include file="CRM/common/overlay.tpl"}

    <div class="crm-accordion-wrapper crm-contactDetails-accordion crm-accordion-open">
      <div class="crm-accordion-header">
        <div class="icon crm-accordion-pointer"></div>
        {ts}Contact Details{/ts}
      </div><!-- /.crm-accordion-header -->
      <div class="crm-accordion-body" id="contactDetails">
        <div id="contactDetails">
          <div class="crm-section contact_basic_information-section">
          {include file="CRM/Contact/Form/Edit/$contactType.tpl"}
          </div>
          <table class="crm-section contact_information-section form-layout-compressed">
            {foreach from=$blocks item="label" key="block"}
              {include file="CRM/Contact/Form/Edit/$block.tpl"}
            {/foreach}
          </table>
          <table class="crm-section contact_source-section form-layout-compressed">
            <tr class="last-row">
              <td>{$form.contact_source.label} {help id="id-source"}<br />
                {$form.contact_source.html|crmAddClass:twenty}
              </td>
              <td>{$form.external_identifier.label}&nbsp;{help id="id-external-id"}<br />
                {$form.external_identifier.html|crmAddClass:six}
              </td>
              {if $contactId}
                <td><label for="internal_identifier">{ts}Internal Id{/ts}{help id="id-internal-id"}</label><br /><input type="text" class="six form-text medium" size="20" disabled="disabled" value="{$contactId}"></td>
              {/if}
            </tr>
          </table>
          <table class="image_URL-section form-layout-compressed">
            <tr>
              <td>
                {$form.image_URL.label}&nbsp;&nbsp;{help id="id-upload-image" file="CRM/Contact/Form/Contact.hlp"}<br />
                {$form.image_URL.html|crmAddClass:twenty}
                {if !empty($imageURL)}
                {include file="CRM/Contact/Page/ContactImage.tpl"}
                {/if}
              </td>
            </tr>
          </table>

          {*add dupe buttons *}
          <span class="crm-button crm-button_qf_Contact_refresh_dedupe">
            {$form._qf_Contact_refresh_dedupe.html}
          </span>
          {if $isDuplicate}
            &nbsp;&nbsp;
              <span class="crm-button crm-button_qf_Contact_upload_duplicate">
                {$form._qf_Contact_upload_duplicate.html}
              </span>
          {/if}
          <div class="spacer"></div>
        </div>
      </div><!-- /.crm-accordion-body -->
    </div><!-- /.crm-accordion-wrapper -->
    
    <script type="text/javascript">var showTab = Array( );</script>
    {foreach from = $editOptions item = "title" key="name"}
      {if $name eq 'CustomData' }
        <div id='customData'></div>
      {/if}
    {include file="CRM/Contact/Form/Edit/$name.tpl"}
    {/foreach}
    <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
  </div>
  {literal}

  <script type="text/javascript" >
  var action = "{/literal}{$action}{literal}";
  var removeCustomData = true;
  showTab[0] = {"spanShow":"span#contact","divShow":"div#contactDetails"};
  cj(function($) {
    cj(showTab).each( function(){
      if( this.spanShow ) {
        cj(this.spanShow).removeClass( ).addClass('crm-accordion-open');
        cj(this.divShow).show( );
      }
    });
    cj().crmaccordions( );
    cj('.customDataPresent').change(function() {
      removeDefaultCustomFields( );
      cj('.crm-accordion-wrapper').not('.crm-accordion-wrapper .crm-accordion-wrapper').each(function() {
        highlightTabs(this);
      });
    });

    cj('.crm-accordion-body').each( function() {
      //remove tab which doesn't have any element
      if ( ! cj.trim( cj(this).text() ) ) {
        ele     = cj(this);
        prevEle = cj(this).prev();
        cj(ele).remove();
        cj(prevEle).remove();
      }
      //open tab if form rule throws error
      if ( cj(this).children( ).find('span.crm-error').text( ).length > 0 ) {
        cj(this).parents('.crm-accordion-closed').crmAccordionToggle();
      }
    });
    if (action == '2') {
      cj('.crm-accordion-wrapper').not('.crm-accordion-wrapper .crm-accordion-wrapper').each(function() {
        highlightTabs(this);
      });
      cj('#crm-container').on('change click', '.crm-accordion-body :input, .crm-accordion-body a', function() {
        highlightTabs($(this).parents('.crm-accordion-wrapper'));
      });
    }
    function highlightTabs(tab) {
      //highlight the tab having data inside.
      cj('.crm-accordion-body :input', tab).each( function() {
        var active = false;
          switch( cj(this).prop('type') ) {
            case 'checkbox':
            case 'radio':
              if( cj(this).is(':checked') ) {
                $('.crm-accordion-header:first', tab).addClass('active');
                return false;
              }
              break;

            case 'text':
            case 'textarea':
            case 'select-one':
            case 'select-multiple':
              if( cj(this).val() ) {
                $('.crm-accordion-header:first', tab).addClass('active');
                return false;
              }
              break;

            case 'file':
              if( cj(this).next().html() ) {
                $('.crm-accordion-header:first', tab).addClass('active');
                return false;
              }
              break;
          }
          $('.crm-accordion-header:first', tab).removeClass('active');
      });
    }
  });

  cj('a#expand').click( function( ){
    if( cj(this).attr('href') == '#expand') {
      var message = {/literal}"{ts}Collapse all tabs{/ts}"{literal};
      cj(this).attr('href', '#collapse');
      cj('.crm-accordion-closed').crmAccordionToggle();
    }
    else {
      var message     = {/literal}"{ts}Expand all tabs{/ts}"{literal};
      cj('.crm-accordion-open').crmAccordionToggle();
      cj(this).attr('href', '#expand');
    }
    cj(this).html(message);
    return false;
  });

  function showHideSignature( blockId ) {
    cj('#Email_Signature_' + blockId ).toggle( );
  }

  function removeDefaultCustomFields( ) {
    //execute only once
    if (removeCustomData) {
      cj(".crm-accordion-wrapper").children().each( function() {
        var eleId = cj(this).attr("id");
        if ( eleId && eleId.substr(0,10) == "customData" ) { cj(this).parent("div").remove(); }
      });
      removeCustomData = false;
    }

    var values = cj("#contact_sub_type").val();
    var contactType = {/literal}"{$contactType}"{literal};
    if ( values ) {
      buildCustomData(contactType, values);
    }
    else{
      values = false;
      buildCustomData(contactType);
    }
    loadMultiRecordFields(values);
  }

  function loadMultiRecordFields(subTypeValues) {
    if (subTypeValues == false) {
      var subTypeValues = null;
    }
      else if (!subTypeValues) {
      var subTypeValues = {/literal}"{$paramSubType}"{literal};
    }
    {/literal}
    {foreach from=$customValueCount item="groupCount" key="groupValue"}
    {if $groupValue}{literal}
      for ( var i = 1; i < {/literal}{$groupCount}{literal}; i++ ) {
        buildCustomData( {/literal}"{$contactType}"{literal}, subTypeValues, null, i, {/literal}{$groupValue}{literal}, true );
      }
    {/literal}
    {/if}
    {/foreach}
    {literal}
  }

  cj(function() {
    loadMultiRecordFields();
  });

  function warnSubtypeDataLoss( ) {
    var submittedSubtypes = cj('#contact_sub_type').val();
    var defaultSubtypes   = {/literal}{$oldSubtypes}{literal};

    var warning = false;
    cj.each(defaultSubtypes, function(index, subtype) {
      if ( cj.inArray(subtype, submittedSubtypes) < 0 ) {
        warning = true;
      }
    });

    if ( warning ) {
      return confirm({/literal}'{ts escape="js"}One or more contact subtypes have been de-selected from the list for this contact. Any custom data associated with de-selected subtype will be removed. Click OK to proceed, or Cancel to review your changes before saving.{/ts}'{literal});
    }
    return true;
  }

  cj("select#contact_sub_type").crmasmSelect({
    addItemTarget: 'bottom',
    animate: false,
    highlight: true,
    respectParents: true
  });

</script>
{/literal}

{* include common additional blocks tpl *}
{include file="CRM/common/additionalBlocks.tpl"}

{* include jscript to warn if unsaved form field changes *}
{include file="CRM/common/formNavigate.tpl"}

{/if}
