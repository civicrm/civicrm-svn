{* this template is used for viewing and editing Domain information (primarily CiviMail related values) *}

<div class="form-item">
<fieldset>
    <dl>
        <dt>{$form.name.label}</dt><dd>{$form.name.html} {help id="domain-name"}
            {edit}
                <br /><span class="description">{ts}The name of the organization or entity which owns this CiviCRM domain.{/ts}</span>
            {/edit}
            </dd>
        <dt>{$form.description.label}</dt><dd>{$form.description.html}
            {edit}
                <br /><span class="description">{ts}Optional description of this domain (useful for sites with multiple domains).{/ts}</span>
            {/edit}
            </dd>
    </dl>

    <fieldset><legend>{ts}System-generated Mail Settings{/ts}</legend>
    <dl>
        <dt>{$form.email_name.label}</dt><dd>{$form.email_name.html} {help id="from-name"}</dd>
        <dt>{$form.email_address.label}</dt><dd>{$form.email_address.html} {help id="from-email"}
            {edit}
                <br /><span class="description">{ts}( info@example.org ){/ts}</span>
            {/edit}
            </dd>
        <dt>{$form.email_domain.label}</dt><dd>{$form.email_domain.html} {help id="email-domain"}
            {edit}
                <br /><span class="description">{ts}( example.org ){/ts}</span>
            {/edit}
            </dd>
        <dt>{$form.email_return_path.label}</dt><dd>{$form.email_return_path.html} {help id="return-path"}</dd>
    </dl>
    </fieldset>
    
    {* Display the address block *}
    {capture assign=introText}{ts}CiviMail mailings must include the sending organization's address. This is done by putting the {ldelim}domain.address{rdelim} token in either the body or footer of the mailing. The token is replaced by the address entered below when the mailing is sent.{/ts}{/capture}
    {include file="CRM/Contact/Form/Address.tpl" introText=$introText} 

    <fieldset><legend>{ts}Additional Contact Information{/ts}</legend>
        <div class="description">{ts}You can also include general email and/or phone contact information in mailings.{/ts} {help id="additional-contact"}</div>
        
        {* Display the email block *}  
        {include file="CRM/Contact/Form/Email.tpl" hold=1}

        {* Display the phone block *}
        {include file="CRM/Contact/Form/Phone.tpl"} 
    </fieldset>

    <div class="spacer"></div>
    
    {if !($action eq 4)}
        <dl><dt></dt><dd>{$form.buttons.html}</dd></dl>
    {/if}
    
    {if ($action eq 4)}
    <div class="action-link">
    <a href="{crmURL q="action=update&reset=1"}" id="editDomainInfo">&raquo; {ts}Edit Domain Information{/ts}</a>
    </div>
    {/if}
</fieldset>
</div>

<script type="text/javascript">
hide('id_location_1_phone_2_show');
hide('id_location_1_email_2_show');
</script>

