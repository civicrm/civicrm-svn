<div id="help">
    {capture assign=docURLTitle}{ts}Opens online documentation in a new window.{/ts}{/capture}
    {ts 1="http://wiki.civicrm.org/confluence//x/4Cs" 2=$docURLTitle}Use the links below to update features and content for this Event. Refer to the <a href="%1" target="_blank" title="%2">Event Administration documentation</a> for more information.{/ts}
</div>
<table class="report"> 
<tr>
    <td class="nowrap"><a href="{crmURL q="reset=1&action=update&id=`$id`&subPage=EventInfo"}" id="idEventInformationandSettings">&raquo; {ts}Event Information and Settings{/ts}</a></td>
    <td>{ts}Set event title, type (conference, performance etc.), description, start and end dates, maximum number of participants, and activate the event.{/ts}</td>
</tr>
<tr>
    <td class="nowrap"><a href="{crmURL q="reset=1&action=update&id=`$id`&subPage=Location"}" id="idLocation">&raquo; {ts}Event Location{/ts}</a></td>
    <td>{ts}Set event location and event contact information (email and phone).{/ts}</td>
</tr>
<tr>
    <td class="nowrap"><a href="{crmURL q="reset=1&action=update&id=`$id`&subPage=Fee"}" id="idFee">&raquo; {ts}Event Fees{/ts}</a></td>
    <td>{ts}Determine if the event is free or paid. For paid events, set the fee level(s) and contribution type assigned to fees.{/ts}</td>
</tr>
<tr>
    <td class="nowrap"><a href="{crmURL q="reset=1&action=update&id=`$id`&subPage=Registration"}" id="idRegistration">&raquo; {ts}Online Registration{/ts}</a></td>
    <td>{ts}Determine whether an online registration page is available. If so, configure registration page elements.{/ts}</td>
</tr>
<tr>
    <td class="nowrap"><a href="{crmURL p='civicrm/event/info' q="reset=1&id=`$id`"}" id="idDisplayEvent">&raquo; {ts}View Event Info{/ts}</a></td>
    <td>{ts}View the Event Information page as it will be displayed to site visitors.{/ts}</td>
</tr>
<tr>
    <td class="nowrap"><a href="{crmURL p='civicrm/event/register' q="reset=1&action=preview&id=`$id`"}" id="idTest-drive">&raquo; {ts}Test-drive Registration{/ts}</a></td>
    <td>{ts}Test-drive the entire online registration process - including custom fields, confirmation, thank-you page, and receipting. Fee payment transactions will be directed to your payment processor's test server. <strong>No live financial transactions will be submitted. However, a contact record will be created or updated and participant and contribution records will be saved to the database. Use obvious test contact names so you can review and delete these records as needed.</strong>{/ts}</td>
</tr>
{if $is_active}
<tr>
    <td class="nowrap"><a href="{crmURL p='civicrm/event/register' q="reset=1&id=`$id`"}" id="idLive">&raquo; {ts}Live Registration{/ts}</a></td>
    <td>{ts}Review your customized <strong>LIVE</strong> online event registration page here. Use the following URL in links and buttons on any website to send visitors to this live page{/ts}:<br />
        <strong>{crmURL p='civicrm/event/register' q="reset=1&id=`$id`"}</strong>
    </td>
</tr>
{/if}

</table>
