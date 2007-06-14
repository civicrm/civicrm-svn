{* This file provides the plugin for the Address block in the Location block *}

{* @var $form Contains the array for the form elements and other form associated information assigned to the template by the controller *}
{* @var location.$index Contains the current location id, and assigned in the Location.tpl file *}

{*<script type="text/javascript" src="{crmURL p='civicrm/server/stateCountry' q="set=1&path=civicrm/server/stateCountry"}"></script>
<script type="text/javascript" src="{$config->resourceBase}js/StateCountry.js"></script>*}
 
<fieldset><legend>{ts}Address{/ts}</legend>
{if $introText}
    <div class="description">{$introText}</div>
{/if}
{php}
$config =& CRM_Core_Config::singleton( );
$addressSequence = $config->addressSequence();
$this->assign( 'addressSequence', $addressSequence );
{/php}	
{foreach item=addressElement from=$addressSequence}
    <span id="id_location_{$index}_address_{$addressElement}">
        {include file=CRM/Contact/Form/Address/$addressElement.tpl}
    </span>
{/foreach}

{include file=CRM/Contact/Form/Address/geo_code.tpl}

<!-- Spacer div forces fieldset to contain floated elements -->
<div class="spacer"></div>
</fieldset>

