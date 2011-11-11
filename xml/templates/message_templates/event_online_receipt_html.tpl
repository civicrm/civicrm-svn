<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <title></title>
</head>
<body>

{capture assign=headerStyle}colspan="2" style="text-align: left; padding: 4px; border-bottom: 1px solid #999; background-color: #eee;"{/capture}
{capture assign=labelStyle }style="padding: 4px; border-bottom: 1px solid #999; background-color: #f7f7f7;"{/capture}
{capture assign=valueStyle }style="padding: 4px; border-bottom: 1px solid #999;"{/capture}

<center>
 <table width="500" border="0" cellpadding="0" cellspacing="0" id="crm-event_receipt" style="font-family: Arial, Verdana, sans-serif; text-align: left;">

  <!-- BEGIN HEADER -->
  <!-- You can add table row(s) here with logo or other header elements -->
  <!-- END HEADER -->

  <!-- BEGIN CONTENT -->

  <tr>
   <td>
	<p>Dear {contact.display_name},</p>

    {if $event.confirm_email_text AND (not $isOnWaitlist AND not $isRequireApproval)}
     <p>{$event.confirm_email_text|htmlize}</p>

    {else}
	<p>Thank you for your participation.  This letter is a confirmation that your registration has been received and your status has been updated to <strong>{if $isOnWaitlist}waitlisted{else}registered{/if}</strong> for the following:</p>

    {/if}

    <p>
    {if $isOnWaitlist}
     <p>{ts}You have been added to the WAIT LIST for this event.{/ts}</p>
     {if $isPrimary}
       <p>{ts}If space becomes available you will receive an email with
a link to a web page where you can complete your registration.{/ts}</p>
     {/if}
    {elseif $isRequireApproval}
     <p>{ts}Your registration has been submitted.{/ts}</p>
     {if $isPrimary}
      <p>{ts}Once your registration has been reviewed, you will receive
an email with a link to a web page where you can complete the
registration process.{/ts}</p>
     {/if}
    {elseif $is_pay_later && !$isAmountzero}
     <p>{$pay_later_receipt}</p> {* FIXME: this might be text rather than HTML *}
    {else}
     <p>{ts}Please print this confirmation for your records.{/ts}</p>
    {/if}

   </td>
  </tr>
  <tr>
   <td>
    <table width="500" style="border: 1px solid #999; margin: 1em 0em 1em; border-collapse: collapse;">
     <tr>
      <th {$headerStyle}>
       {ts}Event Information and Location{/ts}
      </th>
     </tr>
     <tr>
      <td colspan="2" {$valueStyle}>
       {$event.event_title}<br />
       {$event.event_start_date|date_format:"%A"} {$event.event_start_date|crmDate}{if $event.event_end_date}-{if $event.event_end_date|date_format:"%Y%m%d" == $event.event_start_date|date_format:"%Y%m%d"}{$event.event_end_date|crmDate:0:1}{else}{$event.event_end_date|date_format:"%A"} {$event.event_end_date|crmDate}{/if}{/if}
      </td>
     </tr>


     {if $conference_sessions}
      <tr>
       <td colspan="2" {$labelStyle}>
	{ts}Your schedule:{/ts}
       </td>
      </tr>
      <tr>
       <td colspan="2" {$valueStyle}>
	{assign var='group_by_day' value='NA'}
	{foreach from=$conference_sessions item=session}
	 {if $session.start_date|date_format:"%Y/%m/%d" != $group_by_day|date_format:"%Y/%m/%d"}
	  {assign var='group_by_day' value=$session.start_date}
          <em>{$group_by_day|date_format:"%m/%d/%Y"}</em><br />
	 {/if}
	 {$session.start_date|crmDate:0:1}{if $session.end_date}-{$session.end_date|crmDate:0:1}{/if} {$session.title}<br />
	 {if $session.location}&nbsp;&nbsp;&nbsp;&nbsp;{$session.location}<br />{/if}
	{/foreach}
       </td>
      </tr>
     {/if}

     {if $event.participant_role neq 'Attendee' and $defaultRole}
      <tr>
       <td {$labelStyle}>
        {ts}Participant Role{/ts}
       </td>
       <td {$valueStyle}>
        {$event.participant_role}
       </td>
      </tr>
     {/if}

     {if $isShowLocation}
      <tr>
       <td colspan="2" {$valueStyle}>
        {if $location.address.1.name}
         {$location.address.1.name}<br />
        {/if}
        {if $location.address.1.street_address}
         {$location.address.1.street_address}<br />
        {/if}
        {if $location.address.1.supplemental_address_1}
         {$location.address.1.supplemental_address_1}<br />
        {/if}
        {if $location.address.1.supplemental_address_2}
         {$location.address.1.supplemental_address_2}<br />
        {/if}
        {if $location.address.1.city}
         {$location.address.1.city}, {$location.address.1.state_province} {$location.address.1.postal_code}{if $location.address.1.postal_code_suffix} - {$location.address.1.postal_code_suffix}{/if}<br />
        {/if}
       </td>
      </tr>
     {/if}

     {if $location.phone.1.phone || $location.email.1.email}
      <tr>
       <td colspan="2" {$labelStyle}>
        {ts}Event Contacts:{/ts}
       </td>
      </tr>
      {foreach from=$location.phone item=phone}
       {if $phone.phone}
        <tr>
         <td {$labelStyle}>
          {if $phone.phone_type}
           {$phone.phone_type_display}
          {else}
           {ts}Phone{/ts}
          {/if}
         </td>
         <td {$valueStyle}>
          {$phone.phone}
         </td>
        </tr>
       {/if}
      {/foreach}
      {foreach from=$location.email item=eventEmail}
       {if $eventEmail.email}
        <tr>
         <td {$labelStyle}>
          {ts}Email{/ts}
         </td>
         <td {$valueStyle}>
          {$eventEmail.email}
         </td>
        </tr>
       {/if}
      {/foreach}
     {/if}
     <tr>
      <td colspan="2" {$valueStyle}>
       {capture assign=icalFeed}{crmURL p='civicrm/event/ical' q="reset=1&id=`$event.id`" h=0 a=1 fe=1}{/capture}
       <a href="{$icalFeed}">{ts}Download iCalendar File{/ts}</a>
      </td>
     </tr>
     {if $event.is_share}
        <tr>
            <td colspan="2" {$valueStyle}>
                {capture assign=eventUrl}{crmURL p='civicrm/event/info' q="id=`$event.id`&reset=1" a=true fe=1 h=1}{/capture}
                {include file="CRM/common/SocialNetwork.tpl" emailMode=True url=$eventUrl title=$event.title pageURL=$eventUrl}
            </td>
        </tr>
     {/if}
     {if $email}
      <tr>
       <th {$headerStyle}>
        {ts}Registered Email{/ts}
       </th>
      </tr>
      <tr>
       <td colspan="2" {$valueStyle}>
        {$email}
       </td>
      </tr>
     {/if}
     <tr>
       <th {$headerStyle}>
         {ts}You were registered by:{/ts}
       </th>
     </tr>
     <tr>
       <td colspan="2" {$valueStyle}>
	 {$payer.name}
       </td>
     </tr>

     {if $customPre}
      <tr>
       <th {$headerStyle}>
        {$customPre_grouptitle}
       </th>
      </tr>
      {foreach from=$customPre item=value key=customName}
       {if ( $trackingFields and ! in_array( $customName, $trackingFields ) ) or ! $trackingFields}
        <tr>
         <td {$labelStyle}>
          {$customName}
         </td>
         <td {$valueStyle}>
          {$value}
         </td>
        </tr>
       {/if}
      {/foreach}
     {/if}

     {if $customPost}
      <tr>
       <th {$headerStyle}>
        {$customPost_grouptitle}
       </th>
      </tr>
      {foreach from=$customPost item=value key=customName}
       {if ( $trackingFields and ! in_array( $customName, $trackingFields ) ) or ! $trackingFields}
        <tr>
         <td {$labelStyle}>
          {$customName}
         </td>
         <td {$valueStyle}>
          {$value}
         </td>
        </tr>
       {/if}
      {/foreach}
     {/if}

     {if $customProfile}
      {foreach from=$customProfile item=value key=customName}
       <tr>
        <th {$headerStyle}>
         {ts 1=$customName+1}Participant Information - Participant %1{/ts}
        </th>
       </tr>
       {foreach from=$value item=val key=field}
        {if $field eq 'additionalCustomPre' or $field eq 'additionalCustomPost'}
         <tr>
          <td colspan="2" {$labelStyle}>
           {if $field eq 'additionalCustomPre'}
            {$additionalCustomPre_grouptitle}
           {else}
            {$additionalCustomPost_grouptitle}
           {/if}
          </td>
         </tr>
         {foreach from=$val item=v key=f}
          <tr>
           <td {$labelStyle}>
            {$f}
           </td>
           <td {$valueStyle}>
            {$v}
           </td>
          </tr>
         {/foreach}
        {/if}
       {/foreach}
      {/foreach}
     {/if}

     {if $customGroup}
      {foreach from=$customGroup item=value key=customName}
       <tr>
        <th {$headerStyle}>
         {$customName}
        </th>
       </tr>
       {foreach from=$value item=v key=n}
        <tr>
         <td {$labelStyle}>
          {$n}
         </td>
         <td {$valueStyle}>
          {$v}
         </td>
        </tr>
       {/foreach}
      {/foreach}
     {/if}

    </table>
   </td>
  </tr>
 </table>
</center>

</body>
</html>
