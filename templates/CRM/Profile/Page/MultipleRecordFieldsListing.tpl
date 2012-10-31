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
{if $showListing}
<h1>{ts}{$customGroupTitle}{/ts}</h1>

    {if $records and $headers}
    {include file="CRM/common/jsortable.tpl"}
   
      <div id="browseValues">
        <div>
        {strip}
          <table id="records" class="display">
	   <thead>
               <tr>
          {foreach from=$headers key=recId item=head}
             <th>{ts}{$head}{/ts}</th>
         {/foreach}
             <th></th>
             </tr>
           </thead>
     {foreach from=$records key=recId item=rows}   
       <tr class="{cycle values="odd-row,even-row"}">     
         {foreach from=$rows item=row}
            {foreach from=$row item=val key=ids}
              <td>{$val}</td>
            {/foreach}
         {/foreach}
       </tr>
     {/foreach}
          </table>
        {/strip}
       </div>
      </div>
{elseif !$records}
<div class="messages status no-popup">
  <div class="icon inform-icon"></div>&nbsp;
        {ts}No multi-record entries found. Note: check is Include in multi-record listing property of the fields you want to display in listings{/ts}
 </div>
{/if}

{if !$reachedMax}
<a accesskey="N" href="{crmURL p='civicrm/profile/edit' q="id=`$contactId`&multiRecord=add&gid=`$gid`"}" class="button"><span><div class="icon add-icon"></div>{ts}Add New Record{/ts}</span></a>
{/if}
{/if}
