{*
Display a table of locally-available extensions.

Depends: CRM/common/enableDisable.tpl and CRM/common/jsortable.tpl
*}
{if $localExtensionRows}
  <div id="extensions">
    {strip}
    {* handle enable/disable actions*}
    <table id="extensions" class="display">
      <thead>
        <tr>
          <th>{ts}Extension name (key){/ts}</th>
          <th>{ts}Status{/ts}</th>
          <th>{ts}Version{/ts}</th>
          <th>{ts}Type{/ts}</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$localExtensionRows key=extKey item=row}
        <tr id="row_{$row.id}" class="crm-extensions crm-extensions_{$row.id}{if $row.status eq 'disabled'} disabled{/if}{if $row.status eq 'installed-missing' or $row.status eq 'disabled-missing'} extension-missing{/if}{if $row.upgradable} extension-upgradable{elseif $row.status eq 'installed'} extension-installed{/if}">
          <td class="crm-extensions-label">
              <a class="collapsed" href="#">(expand)</a>&nbsp;<strong>{$row.label}</strong><br/>({$row.key})
              {if $extAddNewEnabled && $remoteExtensionRows[$extKey] && $row.version != $remoteExtensionRows[$extKey].version}
                {capture assign='upgradeURL'}{crmURL p='civicrm/admin/extensions' q="action=update&id=$extKey&key=$extKey"}{/capture}
                <div class="crm-extensions-upgrade">{ts 1=$upgradeURL}Version {$remoteExtensionRows[$extKey].version} is available. <a href="%1">Upgrade</a>{/ts}</div>
              {/if}
          </td>
          <td class="crm-extensions-label">{$row.statusLabel} {if $row.upgradable}<br/>({ts}Outdated{/ts}){/if}</td>
          <td class="crm-extensions-label">{$row.version} {if $row.upgradable}<br/>({$row.upgradeVersion}){/if}</td>
          <td class="crm-extensions-description">{$row.type|capitalize}</td>
          <td>{$row.action|replace:'xx':$row.id}</td>
        </tr>
        <tr class="hiddenElement" id="crm-extensions-details-{$row.id}">
            <td>
                {include file="CRM/Admin/Page/ExtensionDetails.tpl" extension=$row}
            </td>
            <td></td><td></td><td></td><td></td>
        </tr>
        {/foreach}
      </tbody>
    </table>
    {/strip}
  </div>
{else}
  <div class="messages status no-popup">
       <div class="icon inform-icon"></div>
      {ts}There are no extensions to display. Please click "Refresh" to update information about available extensions.{/ts}
  </div>
{/if}
