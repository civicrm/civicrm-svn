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
{* Check for Status message for the page (stored in session->getStatus). Status is cleared on retrieval. *}

{if $session->getStatus(false)}
  {assign var="status" value=$session->getStatus(true)}
  {foreach name=statLoop item=statItem from=$status}
    {assign var="infoType" value=$statItem.type}
    {assign var="infoTitle" value=$statItem.title}
    {assign var="infoMessage" value=$statItem.text}
    {assign var="infoOptions" value=$statItem.options}
    {include file="CRM/common/info.tpl"}
  {/foreach}
{/if}

{if !$urlIsPublic AND $config->debug}
  {assign var="infoType" value="alert"}
  {capture assign=infoTitle}{ts}Warning{/ts}{/capture}
  {capture assign=infoMessage}{ts}Debug is currently enabled in Global Settings.{/ts} {docURL page="developer/development-environment/debugging"}{/capture}
  {capture assign=infoOptions}{ldelim}"expires": 10000{rdelim}{/capture}
  {include file="CRM/common/info.tpl"}
{/if}
