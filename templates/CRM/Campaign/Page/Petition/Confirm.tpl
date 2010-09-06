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
<div class="messages status">
      <div class="icon inform-icon"></div>&nbsp;
{if $success}
      <p>{ts 1=$display_name 2=$email 3=$group}<strong>%1 - your email address '%2' has been successfully verified.</strong>{/ts}</p>
		<p>Thank you for signing {$title}.</p>
{else}
      {ts}Oops. We encountered a problem in processing your email verification. Please contact the site administrator.{/ts}
{/if}
</div>

<!-- Social Networking -->
<div class="socialnetwork">
  <fb:like href="{$url}"></fb:like>
<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>
<a href="http://twitter.com/share?text=Sign the petition: {$title}&url={$url}" class="twitter-share-button">Tweet</a>
</div>