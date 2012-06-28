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
{* Contact Summary template for new tabbed interface. Replaces Basic.tpl *}
{if $action eq 2}
    {include file="CRM/Contact/Form/Contact.tpl"}
{else}

{include file="CRM/common/wysiwyg.tpl" includeWysiwygEditor=true}

<div class="crm-actions-ribbon">
    <ul id="actions">
        {assign var='urlParams' value="reset=1"}
        {if $searchKey}
            {assign var='urlParams' value=$urlParams|cat:"&key=$searchKey"}
            {/if}
        {if $context}
            {assign var='urlParams' value=$urlParams|cat:"&context=$context"}
        {/if}

    	{* Include the Actions and Edit buttons if user has 'edit' permission and contact is NOT in trash. *}
        {if $permission EQ 'edit' and !$isDeleted}
            <li class="crm-contact-activity">
                {include file="CRM/Contact/Form/ActionsButton.tpl"}
            </li>
            <li>
                {assign var='editParams' value=$urlParams|cat:"&action=update&cid=$contactId"}
                <a href="{crmURL p='civicrm/contact/add' q=$editParams}" class="edit button" title="{ts}Edit{/ts}">
                <span><div class="icon edit-icon"></div>{ts}Edit{/ts}</span>
                </a>
            </li>
        {/if}

        {* Check for permissions to provide Restore and Delete Permanently buttons for contacts that are in the trash. *}
        {if (call_user_func(array('CRM_Core_Permission','check'), 'access deleted contacts') and
        $is_deleted)}
            <li class="crm-contact-restore">
                <a href="{crmURL p='civicrm/contact/view/delete' q="reset=1&cid=$contactId&restore=1"}" class="delete button" title="{ts}Restore{/ts}">
                <span><div class="icon restore-icon"></div>{ts}Restore from Trash{/ts}</span>
                </a>
            </li>

            {if call_user_func(array('CRM_Core_Permission','check'), 'delete contacts')}
                <li class="crm-contact-permanently-delete">
                    <a href="{crmURL p='civicrm/contact/view/delete' q="reset=1&delete=1&cid=$contactId&skip_undelete=1"}" class="delete button" title="{ts}Delete Permanently{/ts}">
                    <span><div class="icon delete-icon"></div>{ts}Delete Permanently{/ts}</span>
                    </a>
                </li>
            {/if}

        {elseif call_user_func(array('CRM_Core_Permission','check'), 'delete contacts')}
            {assign var='deleteParams' value="&reset=1&delete=1&cid=$contactId"}
            <li class="crm-delete-action crm-contact-delete">
                <a href="{crmURL p='civicrm/contact/view/delete' q=$deleteParams}" class="delete button" title="{ts}Delete{/ts}">
                <span><div class="icon delete-icon"></div>{ts}Delete Contact{/ts}</span>
                </a>
            </li>
        {/if}

        {* Previous and Next contact navigation when accessing contact summary from search results. *}
        {if $nextPrevError}
           <li class="crm-next-action">
             {help id="id-next-prev-buttons"}&nbsp;
           </li>
        {else}
          {if $nextContactID}
           {assign var='viewParams' value=$urlParams|cat:"&cid=$nextContactID"}
           <li class="crm-next-action">
             <a href="{crmURL p='civicrm/contact/view' q=$viewParams}" class="view button" title="{$nextContactName}">
             <span title="{$nextContactName}"><div class="icon next-icon"></div>{ts}Next{/ts}</span>
             </a>
           </li>
          {/if}
          {if $prevContactID}
           {assign var='viewParams' value=$urlParams|cat:"&cid=$prevContactID"}
           <li class="crm-previous-action">
             <a href="{crmURL p='civicrm/contact/view' q=$viewParams}" class="view button" title="{$prevContactName}">
             <span title="{$prevContactName}"><div class="icon previous-icon"></div>{ts}Previous{/ts}</span>
             </a>
           </li>
          {/if}
        {/if}


        {if !empty($groupOrganizationUrl)}
        <li class="crm-contact-associated-groups">
            <a href="{$groupOrganizationUrl}" class="associated-groups button" title="{ts}Associated Multi-Org Group{/ts}">
            <span><div class="icon associated-groups-icon"></div>{ts}Associated Multi-Org Group{/ts}</span>
            </a>
        </li>
        {/if}
    </ul>
    <div class="clear"></div>
</div><!-- .crm-actions-ribbon -->

<div class="crm-block crm-content-block crm-contact-page">

    <div id="mainTabContainer" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
        <ul class="crm-contact-tabs-list">
            <li id="tab_summary" class="crm-tab-button">
            	<a href="#contact-summary" title="{ts}Summary{/ts}">
            	<span> </span> {ts}Summary{/ts}
            	<em>&nbsp;</em>
            	</a>
            </li>
            {foreach from=$allTabs key=tabName item=tabValue}
            <li id="tab_{$tabValue.id}" class="crm-tab-button crm-count-{$tabValue.count}">
            	<a href="{$tabValue.url}" title="{$tabValue.title}">
            		<span> </span> {$tabValue.title}
            		<em>{$tabValue.count}</em>
            	</a>
            </li>
            {/foreach}
        </ul>

        <div id="contact-summary" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
            {if (isset($hookContentPlacement) and ($hookContentPlacement neq 3)) or empty($hookContentPlacement)}

                {if !empty($hookContent) and isset($hookContentPlacement) and $hookContentPlacement eq 2}
                    {include file="CRM/Contact/Page/View/SummaryHook.tpl"}
                {/if}

                {if !empty($imageURL)}
                    <div id="crm-contact-thumbnail">
                        {include file="CRM/Contact/Page/ContactImage.tpl"}
                    </div>
                {/if}

                {if !empty($contact_type_label) OR !empty($current_employer_id) OR !empty($job_title) OR !empty($legal_name) OR $sic_code OR !empty($nick_name) OR !empty($contactTag) OR !empty($source)}
                <div id="contactTopBar">
                    <table>
                        {if !empty($contact_type_label) OR !empty($userRecordUrl) OR !empty($current_employer_id) OR !empty($job_title) OR !empty($legal_name) OR $sic_code OR !empty($nick_name)}
                        <tr>
                            <td class="label">{ts}Contact Type{/ts}</td>
                            <td class="crm-contact_type_label">{if isset($contact_type_label)}{$contact_type_label}{/if}</td>
                            {if !empty($current_employer_id)}
                            <td class="label">{ts}Employer{/ts}</td>
                            <td class="crm-contact-current_employer"><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$current_employer_id`"}" title="{ts}view current employer{/ts}">{$current_employer}</a></td>
                            {/if}
                            {if !empty($job_title)}
                            <td class="label">{ts}Position{/ts}</td>
                            <td class="crm-contact-job_title">{$job_title}</td>
                            {/if}
                            {if !empty($legal_name)}
                            <td class="label">{ts}Legal Name{/ts}</td>
                            <td class="crm-contact-legal_name">{$legal_name}</td>
                            {if $sic_code}
                            <td class="label">{ts}SIC Code{/ts}</td>
                            <td class="crm-contact-sic_code">{$sic_code}</td>
                            {/if}
                            {elseif !empty($nick_name)}
                            <td class="label">{ts}Nickname{/ts}</td>
                            <td class="crm-contact-nick_name">{$nick_name}</td>
                            {/if}
                        </tr>
                        {/if}
                        <tr>
                            {if !empty($contactTag)}
                            <td class="label" id="tagLink"><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=$contactId&selectedChild=tag"}" title="{ts}Edit Tags{/ts}">{ts}Tags{/ts}</a></td><td id="tags">{$contactTag}</td>
                            {/if}
                            <td class="label">{ts}CiviCRM ID{/ts}</td><td class="crm-contact-contact_id">{$contactId}</td>
                            {if !empty($userRecordUrl)}
                            <td class="label">{ts}User ID{/ts}</td><td class="crm-contact-user_record_id"><a title="View user record" class="user-record-link" href="{$userRecordUrl}">{$userRecordId}</a></td>
                            {/if}
                            {if !empty($source)}
                            <td class="label">{ts}Source{/ts}</td><td class="crm-contact_source">{$source}</td>
                            {/if}
                        </tr>
                    </table>

                    <div class="clear"></div>
                </div><!-- #contactTopBar -->
                {/if}
                <div class="contact_details">
                    <div class="contact_panel">
                        <div class="contactCardLeft">
                           <div class="crm-table2div-layout">
                              <div class="crm-clear crm-summary-block" id="email-block">
                                  {include file="CRM/Contact/Page/Inline/Email.tpl"}
                              </div>

                              {if $website}
                              <div class="crm-clear crm-summary-block">
                                {foreach from=$website item=item}
                                    {if !empty($item.url)}
                                        <div class="crm-label">{$item.website_type} {ts}Website{/ts}</div>
                                        <div class="crm-content crm-contact_website"><a href="{$item.url}" target="_blank">{$item.url}</a></div>
                                    {/if}
                                {/foreach}
                              </div>
                              {/if}
                              {if $user_unique_id}
                                  <br/>
                                  <div class="crm-clear crm-summary-block">
                                      <div class="crm-label">{ts}Unique Id{/ts}</div>
                                      <div class="crm-content crm-contact-user_unique_id">{$user_unique_id}</div>
                                  </div>
                              {/if}
                           </div>
                        </div><!-- #contactCardLeft -->

                        <div class="contactCardRight">
                            <div class="crm-table2div-layout">
                                <div class="crm-clear crm-summary-block" id="phone-block">
                                  {include file="CRM/Contact/Page/Inline/Phone.tpl"}
                                </div>
                                {if $im}
                                <div class="crm-clear crm-summary-block" id="im-block">
                                {foreach from=$im item=item}
                                    {if $item.name or $item.provider}
                                      {if $item.name}
                                        <div class="crm-label">{$item.provider}&nbsp;({$item.location_type})</div>
                                        <div class="crm-content crm-contact_im {if $item.is_primary eq 1} primary{/if}">{$item.name}</div>
                                      {/if}
                                    {/if}
                                {/foreach}
                                </div>
                                {/if}
                                {if $openid}
                                <div class="crm-clear crm-summary-block" id="openid-block">
                                {foreach from=$openid item=item}
                                    {if $item.openid}
                                      <div class="crm-label">{$item.location_type}&nbsp;{ts}OpenID{/ts}</div>
                                      <div class="crm-content crm-contact_openid {if $item.is_primary eq 1} primary{/if}"><a href="{$item.openid}">{$item.openid|mb_truncate:40}</a>
                                       </div>
                                    {/if}
                                {/foreach}
                                </div>
                                {/if}
                            </div>
                        </div><!-- #contactCardRight -->

                        <div class="clear"></div>
                    </div><!-- #contact_panel -->

					{if $address}
            <div class="contact_panel">
              {foreach from=$address item=add key=locationIndex}
                <div class="{cycle name=location values="contactCardLeft,contactCardRight"} crm-address_{$locationIndex} crm-address-block crm-address_type_{$add.location_type}">
                  <div class="crm-table2div-layout">
                    <div class="crm-clear"> <!-- start of main -->
                      <div class="crm-label">
                        {ts 1=$add.location_type}%1&nbsp;Address{/ts}
                        {if $config->mapProvider AND
                            !empty($add.geo_code_1) AND
                            is_numeric($add.geo_code_1) AND
                            !empty($add.geo_code_2) AND
                            is_numeric($add.geo_code_2)
                        }
                        <br /><a href="{crmURL p='civicrm/contact/map' q="reset=1&cid=`$contactId`&lid=`$add.location_type_id`"}" title="{ts 1=`$add.location_type`}Map %1 Address{/ts}"><span class="geotag">{ts}Map{/ts}</span></a>
                        {/if}
                      </div>
                      <div class="crm-content">
                        {if !empty($sharedAddresses.$locationIndex.shared_address_display.name)}
                          <strong>{ts}Shared with:{/ts}</strong><br />
                          {$sharedAddresses.$locationIndex.shared_address_display.name}<br />
                        {/if}
                        {$add.display|nl2br}
                      </div>
                    </div>
                  </div>

                  <!-- add custom data -->
                  {foreach from=$add.custom item=customGroup key=cgId} {* start of outer foreach *}
                    {assign var="isAddressCustomPresent" value=1}
                    {foreach from=$customGroup item=customValue key=cvId}
                    <div id="address_custom_{$cgId}_{$locationIndex}" 
                    class="crm-accordion-wrapper crm-address-custom-{$cgId}-{$locationIndex}-accordion 
                    {if $customValue.collapse_display}crm-accordion-closed{else}crm-accordion-open{/if}">
                    <div class="crm-accordion-header">
                      <div class="icon crm-accordion-pointer"></div>
                      {$customValue.title}
                    </div>
                    <div class="crm-accordion-body">
                      <div class="crm-table2div-layout">
                        <div class="crm-clear">
                          {foreach from=$customValue.fields item=customField key=cfId}
                          <div class="crm-label">
                            {$customField.field_title}
                          </div>
                          <div class="crm-content">
                            {$customField.field_value}
                          </div>
                          {/foreach}
                          </div>
                        </div>
                      </div>
                    </div>
                    {/foreach}
                  {/foreach} {* end of outer custom group foreach *}
                  <!-- end custom data -->
                  
                  </div>
                {/foreach} {* end of address foreach *}

              <div class="clear"></div>
            </div> <!-- end of contact panel -->
          {/if}

          <div class="contact_panel">
            <div class="contactCardLeft">
              <div class="crm-summary-block" id="communication-pref-block" >
                {include file="CRM/Contact/Page/Inline/CommunicationPreferences.tpl"} 
              </div>
            </div> <!-- contactCardLeft -->
            {if $contact_type eq 'Individual' AND $showDemographics}
              <div class="contactCardRight">
                <div class="crm-summary-block" id="demographic-block">
                  {include file="CRM/Contact/Page/Inline/Demographics.tpl"} 
                </div>
              </div> <!-- contactCardRight -->
            {/if}
            <div class="clear"></div>
            <div class="separator"></div>
          </div> <!-- contact panel -->
     </div><!--contact_details-->

                <div id="customFields">
                    <div class="contact_panel">
                        <div class="contactCardLeft">
                            {include file="CRM/Contact/Page/View/CustomDataView.tpl" side='1'}
                        </div><!--contactCardLeft-->

                        <div class="contactCardRight">
                            {include file="CRM/Contact/Page/View/CustomDataView.tpl" side='0'}
                        </div>

                        <div class="clear"></div>
                    </div>
                </div>
                {literal}
                <script type="text/javascript">
                    cj('.columnheader').click( function( ) {
                        var aTagObj = cj(this).find('a');
                        if ( aTagObj.hasClass( "expanded" ) ) {
                            cj(this).parent().find('tr:not(".columnheader")').hide( );
                        } else {
                            cj(this).parent().find('tr:not(".columnheader")').show( );
                        }
                        aTagObj.toggleClass("expanded");
                        return false;
                    });
                </script>
                {/literal}
                {if !empty($hookContent) and isset($hookContentPlacement) and $hookContentPlacement eq 1}
                    {include file="CRM/Contact/Page/View/SummaryHook.tpl"}
                {/if}
            {else}
                {include file="CRM/Contact/Page/View/SummaryHook.tpl"}
            {/if}
        </div>
		    <div class="clear"></div>
    </div>
 <script type="text/javascript">
 var selectedTab  = 'summary';
 var spinnerImage = '<img src="{$config->resourceBase}i/loading.gif" style="width:10px;height:10px"/>';
 {if $selectedChild}selectedTab = "{$selectedChild}";{/if}
 {literal}

 cj( function() {
  var tabIndex = cj('#tab_' + selectedTab).prevAll().length;
  cj("#mainTabContainer").tabs({ selected: tabIndex, spinner: spinnerImage,cache: true});
  cj(".crm-tab-button").addClass("ui-corner-bottom");
 });
 {/literal}
 </script>

{/if}
{if !empty($isAddressCustomPresent)}
  {literal}
  <script type="text/javascript">
  cj(function() {
    cj().crmaccordions();
  });
  </script>
  {/literal}
{/if}
<div class="clear"></div>
</div><!-- /.crm-content-block -->

{literal}
<script type="text/javascript">

cj(function(){
  var customBlock = cj('div[id^="custom-set-block-"]');
  customBlock.mouseenter( function() {
    cj(this).addClass('crm-inline-edit-hover');
    cj(this).find('a[id^="edit-custom-set-block-"]').show();
  }).mouseleave( function() {
    cj(this).removeClass('crm-inline-edit-hover');
    cj(this).find('a[id^="edit-custom-set-block-"]').hide();
  });

  cj('a[id^="edit-custom-set-block-"]').live( 'click', function() {
    var cgId   = cj(this).attr('cgId');
    var dataUrl = {/literal}"{crmURL p='civicrm/ajax/inline' h=0 q='snippet=5&reset=1&cid='}{$contactId}"{literal} + '&groupID=' + cgId;

    var response = cj.ajax({
                    type: "GET",
                    data: {'class_name':'CRM_Contact_Form_Inline_CustomData'},
                    url: dataUrl,
                    async: false
    }).responseText;

    cj( '#custom-set-block-'+ cgId ).html( response );
  });
 
});

</script>
{/literal}

