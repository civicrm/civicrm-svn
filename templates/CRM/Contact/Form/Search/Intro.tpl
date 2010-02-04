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
{* $context indicates where we are searching, values = "search,advanced,smog,amtg" *}
{* smog = 'show members of group'; amtg = 'add members to group' *}
{if $context EQ 'smog'}
    {if $permissionedForGroup}
    {capture assign=addMembersURL}{crmURL q="context=amtg&amtgID=`$group.id`&reset=1"}{/capture}
    <div class="action-link">
        <a href="{$addMembersURL}">&raquo; {ts 1=$group.title}Add Members to %1{/ts}</a>
    </div>
    {/if}
    
    {* Provide link to modify smart group search criteria if we are viewing a smart group (ssID = saved search ID) *}
    {if $ssID}
        {if $ssMappingID}
            {capture assign=editSmartGroupURL}{crmURL p="civicrm/contact/search/builder" q="reset=1&force=1&ssID=`$ssID`"}{/capture}
        {else}
            {capture assign=editSmartGroupURL}{crmURL p="civicrm/contact/search/advanced" q="reset=1&force=1&ssID=`$ssID`"}{/capture}
        {/if} 
        <div class="action-link">
            <a href="{$editSmartGroupURL}">&raquo; {ts 1=$group.title}Edit Smart Group Search Criteria for %1{/ts}</a>
        </div>
    {/if}
{/if}
