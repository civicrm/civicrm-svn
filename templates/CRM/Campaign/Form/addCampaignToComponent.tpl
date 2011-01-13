{* add campaigns to various components CRM-7362 *}

{if $campaignInfo.showAddCampaign}
    <tr class="{$campaignTrClass}">
        <td class="label">{$form.campaign_id.label}</td>
        <td class="view-value">
	    {* lets take a call, either show campaign select drop-down or show add campaign link *}		 
            {if $campaignInfo.hasCampaigns}
		{$form.campaign_id.html}
            {else}
		{ts}There are currently no Campaigns.{/ts}
		{if $campaignInfo.addCampaignURL}
		    {ts 1=$campaignInfo.addCampaignURL}If you want to associate this record with a campaign, you can <a href="%1">create a campaign here</a>.{/ts}
		{/if}
	    {/if}
        </td>
    </tr>
{/if}

