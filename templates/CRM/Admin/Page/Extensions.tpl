{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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

{if $action eq 1 or $action eq 2 or $action eq 8}
   {include file="CRM/Admin/Form/Extensions.tpl"}
{else}
    {if not $extEnabled}
      <div class="crm-content-block crm-block">
        <div class="messages status">
             <div class="icon inform-icon"></div>
             {capture assign='returnURL'}{crmURL p='civicrm/admin/extensions' q='reset=1'}{/capture}
             {capture assign='adminURL'}{crmURL p='civicrm/admin/setting/path' q="reset=1&destination=$returnURL"}{/capture}
             {ts 1=$adminURL}Your extensions directory is not set. Click <a href='%1'>here</a> to set the extension directory.{/ts}
        </div>
      </div>
    {else} {* extEnabled *}
      {if $action ne 1 and $action ne 2}
          <div class="action-link">
              <a href="{crmURL q="reset=1"}" id="new" class="button"><span><div class="icon refresh-icon"></div>{ts}Refresh{/ts}</span></a>
          </div>
      {/if}

      <div class="messages help">
        {ts}Extensions help.{/ts}
      </div>

      <h3>{ts}Installed extensions{/ts}</h3>
      {include file="CRM/common/enableDisable.tpl"}
      {include file="CRM/common/jsortable.tpl"}
      {if $rows}
        <div id="extensions">
          {strip}
          {* handle enable/disable actions*} 
          <table id="installed-extensions" class="display">
            <thead>
              <tr>
                <th>{ts}Extension name{/ts}</th>
                <th>{ts}Version{/ts}</th>
                <th>{ts}Enabled?{/ts}</th>
                <th>{ts}Type{/ts}</th>
                <th class="hiddenElement"></th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {foreach from=$rows item=row}
              <tr id="row_{$row.id}" class="crm-admin-options crm-admin-options_{$row.id}{if NOT $row.is_active} disabled{/if}">
                <td class="crm-admin-options-label">
                    <a class="collapsed" href="#"></a>&nbsp;{$row.label} ( {$row.key} )
                    <span class="hiddenElement description"><br/><br/>{$row.description}</span>
                </td>
                <td class="crm-admin-options-label">{$row.version}</td>
                <td class="crm-admin-options-is_active" id="row_{$row.id}_status">{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
                <td class="crm-admin-options-description">{$row.type}</td>
                <td class="order hiddenElement">{$row.weight}</td>
                <td>{$row.action|replace:'xx':$row.id}</td>
              </tr>
              {/foreach}
            </tbody>
          </table>
          {/strip}
        </div>

      {else}
        <div class="messages status">
             <div class="icon inform-icon"></div>
            {ts 1=$crmURL}There are no option values entered. You can <a href='%1'>add one</a>.{/ts}
        </div>    
      {/if}
    <br/>
    <h3>{ts}Uploaded extensions{/ts}</h3>
          {if $rowsUploaded}
            <div id="extensionsUploaded">
              {strip}
              {* handle enable/disable actions*} 
              <table id="uploaded-extensions" class="display">
                <thead>
                  <tr>
                    <th>{ts}Extension name{/ts}</th>
                    <th>{ts}Version{/ts}</th>
                    <th>{ts}Enabled?{/ts}</th>
                    <th>{ts}Type{/ts}</th>
                    <th class="hiddenElement"></th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  {foreach from=$rowsUploaded item=row}
                  <tr id="uploaded-row_{$row.id}" class="crm-admin-options crm-admin-options_{$row.id}{if NOT $row.is_active} disabled{/if}">
                    <td class="crm-admin-options-label"> <a class="collapsed" href="#"></a>&nbsp;{$row.label} ( {$row.key} )
                        <span class="hiddenElement description"><br/><br/>{$row.description}</span>
                    </td>
                    <td class="crm-admin-options-label">{$row.version}</td>	
                    <td class="crm-admin-options-is_active" id="row_{$row.id}_status">{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
                    <td class="crm-admin-options-description">{$row.grouping}</td>
                    <td class="order hiddenElement">{$row.weight}</td>
                    <td>{$row.action|replace:'xx':$row.id}</td>
                  </tr>
                  {/foreach}
                </tbody>
              </table>
              {/strip}
            </div>
          {else}
              <div class="messages status">
                   <div class="icon inform-icon"></div>
                  {ts}There are no uploaded extensions to be installed.{/ts}
              </div>    
          {/if}

          {if $action ne 1 and $action ne 2}
              <div class="action-link">
            <a href="{crmURL q="reset=1"}" id="new" class="button"><span><div class="icon refresh-icon"></div>{ts}Refresh{/ts}</span></a>
              </div>
          {/if}
          
          {literal}
          <script type="text/javascript">
            cj( function( ) {
                cj('.collapsed').click( function( ) {
                    var currentObj = cj( this );
                    if ( currentObj.hasClass( 'expanded') ) {
                        currentObj.removeClass( 'expanded' );
                        currentObj.next( ).hide( );
                    } else {
                        currentObj.addClass( 'expanded' );
                        currentObj.next( ).show( ); 
                    }
                    
                    return false;
                });
            });
          </script>
          {/literal}
    {/if}
{/if}