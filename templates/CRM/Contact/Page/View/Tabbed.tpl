{* Contact Summary template for new tabbed interface. Replaces Basic.tpl *}
{if $action eq 2}
  {include file="CRM/Contact/Form/Edit.tpl"}
{else}

    <div id="mainTabContainer" dojoType="dijit.layout.TabContainer" class ="tundra" style="width: 100%; height: 1600px;" >

<div id="summary" dojoType="dijit.layout.ContentPane" title="Summary" selected="true" class ="tundra" style="overflow: auto; width: 100%; height: 100%;">

{* View Contact Summary *}
<div id="contact-name" class="section-hidden section-hidden-border">
   <div>
    <label><span class="font-size12pt">{$displayName}</span></label>{if $nick_name}&nbsp;&nbsp;({$nick_name}){/if}
    {if $permission EQ 'edit'}
        &nbsp; &nbsp; <input type="button" value="{ts}Edit{/ts}" name="edit_contact_info" onclick="window.location='{crmURL p='civicrm/contact/add' q="reset=1&action=update&cid=$contactId"}';"/>
    {/if}
    &nbsp; &nbsp; <input type="button" value="{ts}vCard{/ts}" name="vCard_export" onclick="window.location='{crmURL p='civicrm/contact/view/vcard' q="reset=1&cid=$contactId"}';"/>
    &nbsp; &nbsp; <input type="button" value="{ts}Print{/ts}" name="contact_print" onclick="window.location='{crmURL p='civicrm/contact/view/print' q="reset=1&print=1&cid=$contactId"}';"/>
    {if $permission EQ 'edit'}
        &nbsp; &nbsp; <input type="button" value="{ts}Delete{/ts}" name="contact_delete" onclick="window.location='{crmURL p='civicrm/contact/view/delete' q="reset=1&delete=1&cid=$contactId"}';"/>
    {/if}
    {if $dashboardURL } &nbsp; &nbsp; <a href="{$dashboardURL}">&raquo; {ts}View Contact Dashboard{/ts}</a> {/if}
    {if $url } &nbsp; &nbsp; <a href="{$url}">&raquo; {ts}View User Record{/ts}</a> {/if}
    <table class="form-layout-compressed">
    <tr>
        {if $source}<td><label>{ts}Source{/ts}:</label></td><td>{$source}</td>{/if}
        {if $contactTag}<td><label>{ts}Tags{/ts}:</label></td><td>{$contactTag}</td>{/if}
        {if !$source}<td colspan="2"></td>{/if}
        {if !$contactTag}<td colspan="2"></td>{/if}
    </tr>
    <tr>
        {if $job_title}<td><label>{ts}Job Title{/ts}:</label></td><td>{$job_title}</td>{/if}
        {if $home_URL}<td><label>{ts}Website{/ts}</label></td><td><a href="{$home_URL}" target="_blank">{$home_URL}</a></td>{/if}
        {if !$job_title}<td colspan="2"></td>{/if}
        {if !$home_URL}<td colspan="2"></td>{/if}
    </tr>
    </table>
   </div>
</div>

{* Include links to enter Activities if session has 'edit' permission *}

{if $permission EQ 'edit'}
    {include file="CRM/Contact/Page/View/ActivityLinks.tpl"}
{/if}

{* Display populated Locations. Primary location expanded by default. *}
{foreach from=$location item=loc key=locationIndex}

<div id="location_{$locationIndex}_show" class="section-hidden section-hidden-border">
  <a href="#" onclick="hide('location_{$locationIndex}_show'); show('location_{$locationIndex}'); return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="{ts}open section{/ts}"/></a><label>{$loc.location_type}{if $loc.name} - {$loc.name}{/if}{if $locationIndex eq 1} {ts}(primary location){/ts}{/if}</label>
  {if $preferred_communication_method_display eq 'Email'}&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <label>{ts}Preferred Email:{/ts}</label> {$loc.email.1.email}
  {elseif $preferred_communication_method_display eq 'Phone'}&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <label>{ts}Preferred Phone:{/ts}</label> {$loc.phone.1.phone}{/if}
</div>

<div id="location_{$locationIndex}" class="section-shown">
  <fieldset>
   <legend{if $locationIndex eq 1} class="label"{/if}>
    <a href="#" onclick="hide('location_{$locationIndex}'); show('location_{$locationIndex}_show'); return false;"><img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="{ts}close section{/ts}"/></a>{$loc.location_type}{if $loc.name} - {$loc.name}{/if}{if $locationIndex eq 1} {ts}(primary location){/ts}{/if}
   </legend>

  <div class="col1">
   {foreach from=$loc.phone item=phone}
     {if $phone.phone}
        {if $phone.is_primary eq 1}<strong>{/if}
        {if $phone.phone_type}{$phone.phone_type_display}:{/if} {$phone.phone}
        {if $phone.is_primary eq 1}</strong>{/if}
        <br />
     {/if}
   {/foreach}

   {foreach from=$loc.email item=email}
      {if $email.email}
        {if $email.is_primary eq 1}<strong>{/if}
        {ts}Email:{/ts} <a href="mailto:{$email.email}">{$email.email}</a>
        {if $email.is_primary eq 1}</strong>{/if}
      {/if}
      {if $email.on_hold}
	    <span class="status-hold">&nbsp;(On Hold)</span>
	  {/if}
      {if $email.is_bulkmail}
	    <span class="status-hold">&nbsp;(Bulk Mailings)</span>
	  {/if}
	<br />
   {/foreach}

   {foreach from=$loc.im item=im key=imKey}
     {if $im.name or $im.provider}
        {if $im.is_primary eq 1}<strong>{/if}
        {ts}Instant Messenger:{/ts} {if $im.name}{$im.name}{/if} {if $im.provider}( {$im.provider} ) {/if}
        {if $im.is_primary eq 1}</strong>{/if}
        <br />
     {/if}
   {/foreach}

  {foreach from=$loc.user_unique_id item=user_unique_id}
    {if $user_unique_id.user_unique_id}
      {if $user_unique_id.is_primary eq 1}<strong>{/if}
      {ts}User_Unique_Id:{/ts} {if $user_unique_id.user_unique_id}{$user_unique_id.user_unique_id}{/if}
      {if $user_unique_id.is_primary eq 1}</strong>{/if}
     </br>
    {/if}
  {/foreach}
   </div>

   <div class="col2">

    {*if $config->mapAPIKey AND $loc.is_primary AND $loc.address.geo_code_1 AND $loc.address.geo_code_2*}
    {if $config->mapAPIKey AND $loc.address.geo_code_1 AND $loc.address.geo_code_2}
        <a href="{crmURL p='civicrm/contact/map' q="reset=1&cid=$contactId&lid=`$loc.address.location_id`"}" title="{ts}Map Primary Address{/ts}">{ts}Map this Address{/ts}</a><br />
    {/if}
    {$loc.address.display|nl2br}
  </div>
  <div class="spacer"></div>
  </fieldset>
</div>
{/foreach}

<div id="commPrefs_show" class="section-hidden section-hidden-border">
  <a href="#" onclick="hide('commPrefs_show'); show('commPrefs'); return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="{ts}open section{/ts}"/></a><label>{ts}Communications Preferences{/ts}</label><br />
 </div>

<div id="commPrefs" class="section-shown">
 <fieldset>
  <legend><a href="#" onclick="hide('commPrefs'); show('commPrefs_show'); return false;"><img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="{ts}close section{/ts}"/></a>{ts}Communications Preferences{/ts}</legend>
  <div class="col1">
    <label>{ts}Privacy:{/ts}</label>
    <span class="font-red upper">
    {foreach from=$privacy item=privacy_val key=privacy_label}
      {if $privacy_val eq 1}{$privacy_values.$privacy_label} &nbsp; {/if}
    {/foreach}
    {if $is_opt_out}
      {ts}DO NOT SEND BULK EMAIL{/ts}
    {/if}
    </span>
  </div>
  <div class="col2">
    <label>{ts}Method:{/ts}</label> {$preferred_communication_method_display}
  </div>
  <div class="col2">
    <label>{ts}Email Format Preference:{/ts}</label> {$preferred_mail_format_display}
  </div>
  <div class="spacer"></div>
 </fieldset>
</div>


{if $contact_type eq 'Individual'}
<div id="demographics_show" class="section-hidden section-hidden-border">
  <a href="#" onclick="hide('demographics_show'); show('demographics'); return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="{ts}open section{/ts}"/></a><label>{ts}Demographics{/ts}</label><br />
 </div>

<div id="demographics" class="section-shown">
  <fieldset>
   <legend><a href="#" onclick="hide('demographics'); show('demographics_show'); return false;"><img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="{ts}close section{/ts}"/></a>{ts}Demographics{/ts}</legend>
   <div class="col1">
    <label>{ts}Gender:{/ts}</label> {$gender_display}<br />
    {if $is_deceased eq 1}
        <label>{ts}Contact is Deceased{/ts}</label><br />
    {/if}
    {if $deceased_date}
        <label>{ts}Date Deceased:{/ts}</label> {$deceased_date|crmDate}
    {/if}
   </div>
   <div class="col2">
    <label>{ts}Date of Birth:{/ts}</label> {$birth_date|crmDate}<br />
    {if $age.y}  
    <label>{ts}Age :{/ts}</label> {$age.y} Year{if $age.y gt 1}s{/if}. <br />
    {/if}
    {if $age.m} 
    <label>{ts}Age :{/ts}</label> {$age.m} Month{if $age.m gt 1}s{/if}. <br />                              
    {/if}       
    </div>
   <div class="spacer"></div>
  </fieldset>
 </div>
 {/if}

 {include file="CRM/Contact/Page/View/InlineCustomData.tpl"}
</div>

{foreach from=$allTabs key=tabName item=tabValue}
  <div id="{$tabValue.id}" dojoType="dijit.layout.ContentPane" href="{$tabValue.url}" title="{$tabValue.title}" class ="tundra"></div>
{/foreach}
</div>

{literal}
 <script type="text/javascript">

   init_blocks = function( ) {
{/literal}
      var showBlocks = new Array({$showBlocks});
      var hideBlocks = new Array({$hideBlocks});
{literal}
      on_load_init_blocks( showBlocks, hideBlocks );
  }

  dojo.addOnLoad( init_blocks );
 </script>
{/literal}

{/if}
