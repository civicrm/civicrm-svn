Dear {contact.display_name},

{if $event.confirm_email_text AND (not $isOnWaitlist AND not $isRequireApproval)}
{$event.confirm_email_text}

{else}
Thank you for your participation.  This letter is a confirmation that your registration has been received and your status has been updated to {if $isOnWaitlist}waitlisted{else}registered{/if} for the following:

{/if}

{if $isOnWaitlist}
==========================================================={if $pricesetFieldsCount }===================={/if}

{ts}You have been added to the WAIT LIST for this event.{/ts}

{if $isPrimary}
{ts}If space becomes available you will receive an email with
a link to a web page where you can complete your registration.{/ts}
{/if}
==========================================================={if $pricesetFieldsCount }===================={/if}

{elseif $isRequireApproval}
==========================================================={if $pricesetFieldsCount }===================={/if}

{ts}Your registration has been submitted.{/ts}

{if $isPrimary}
{ts}Once your registration has been reviewed, you will receive
an email with a link to a web page where you can complete the
registration process.{/ts}

{/if}
==========================================================={if $pricesetFieldsCount }===================={/if}

{elseif $is_pay_later && !$isAmountzero}

==========================================================={if $pricesetFieldsCount }===================={/if}

{$pay_later_receipt}
==========================================================={if $pricesetFieldsCount }===================={/if}

{else}

{ts}Please print this confirmation for your records.{/ts}
{/if}


==========================================================={if $pricesetFieldsCount }===================={/if}

{ts}Event Information and Location{/ts}

==========================================================={if $pricesetFieldsCount }===================={/if}

{$event.event_title}
{$event.event_start_date|date_format:"%A"} {$event.event_start_date|crmDate}{if $event.event_end_date}-{if $event.event_end_date|date_format:"%Y%m%d" == $event.event_start_date|date_format:"%Y%m%d"}{$event.event_end_date|crmDate:0:1}{else}{$event.event_end_date|date_format:"%A"} {$event.event_end_date|crmDate}{/if}{/if}
{if $conference_sessions}


{ts}Your schedule:{/ts}
{assign var='group_by_day' value='NA'}
{foreach from=$conference_sessions item=session}
{if $session.start_date|date_format:"%Y/%m/%d" != $group_by_day|date_format:"%Y/%m/%d"}
{assign var='group_by_day' value=$session.start_date}

{$group_by_day|date_format:"%m/%d/%Y"}


{/if}
{$session.start_date|crmDate:0:1}{if $session.end_date}-{$session.end_date|crmDate:0:1}{/if} {$session.title}
{if $session.location}    {$session.location}{/if}
{/foreach}
{/if}

{if $event.participant_role neq 'Attendee' and $defaultRole}
{ts}Participant Role{/ts}: {$event.participant_role}
{/if}

{if $isShowLocation}
{if $location.address.1.name}

{$location.address.1.name}
{/if}
{if $location.address.1.street_address}{$location.address.1.street_address}
{/if}
{if $location.address.1.supplemental_address_1}{$location.address.1.supplemental_address_1}
{/if}
{if $location.address.1.supplemental_address_2}{$location.address.1.supplemental_address_2}
{/if}
{if $location.address.1.city}{$location.address.1.city}, {$location.address.1.state_province} {$location.address.1.postal_code}{if $location.address.1.postal_code_suffix} - {$location.address.1.postal_code_suffix}{/if}
{/if}

{/if}{*End of isShowLocation condition*}

{if $location.phone.1.phone || $location.email.1.email}

{ts}Event Contacts:{/ts}
{foreach from=$location.phone item=phone}
{if $phone.phone}

{if $phone.phone_type}{$phone.phone_type_display}{else}{ts}Phone{/ts}{/if}: {$phone.phone}{/if}
{/foreach}
{foreach from=$location.email item=eventEmail}
{if $eventEmail.email}

{ts}Email{/ts}: {$eventEmail.email}{/if}{/foreach}
{/if}

{capture assign=icalFeed}{crmURL p='civicrm/event/ical' q="reset=1&id=`$event.id`" h=0 a=1 fe=1}{/capture}
{ts}Download iCalendar File:{/ts} {$icalFeed}
{if $email}

==========================================================={if $pricesetFieldsCount }===================={/if}

{ts}Registered Email{/ts}

==========================================================={if $pricesetFieldsCount }===================={/if}

{$email}
{/if}

You were registered by: {$payer.name}

{if $customPre}
{foreach from=$customPre item=customPr key=i}
==========================================================={if $pricesetFieldsCount }===================={/if}

{$customPre_grouptitle.$i}
==========================================================={if $pricesetFieldsCount }===================={/if}

{foreach from=$customPr item=customValue key=customName}
{if ( $trackingFields and ! in_array( $customName, $trackingFields ) ) or ! $trackingFields}
 {$customName}: {$customValue}
{/if}
{/foreach}
{/foreach}
{/if}

{if $customPost}
{foreach from=$customPost item=customPos key=j}
==========================================================={if $pricesetFieldsCount }===================={/if}

{$customPost_grouptitle.$j} 
==========================================================={if $pricesetFieldsCount }===================={/if}

{foreach from=$customPos item=customValue key=customName}
{if ( $trackingFields and ! in_array( $customName, $trackingFields ) ) or ! $trackingFields}
 {$customName}: {$customValue}
{/if}
{/foreach}
{/foreach}
{/if}
{if $customProfile}

{foreach from=$customProfile.profile item=eachParticipant key=participantID}
==========================================================={if $pricesetFieldsCount }===================={/if}

{ts 1=$participantID+2}Participant Information - Participant %1{/ts}

==========================================================={if $pricesetFieldsCount }===================={/if}

{foreach from=$eachParticipant item=eachProfile key=pid}
----------------------------------------------------------{if $pricesetFieldsCount }--------------------{/if}

{$customProfile.title.$pid}
----------------------------------------------------------{if $pricesetFieldsCount }--------------------{/if}

{foreach from=$eachProfile item=val key=field}
{foreach from=$val item=v key=f}
{$field}: {$v}
{/foreach}
{/foreach}
{/foreach}
{/foreach}
{/if}
{if $customGroup}
{foreach from=$customGroup item=value key=customName}
=========================================================={if $pricesetFieldsCount }===================={/if}

{$customName}
=========================================================={if $pricesetFieldsCount }===================={/if}

{foreach from=$value item=v key=n}
{$n}: {$v}
{/foreach}
{/foreach}
{/if}
