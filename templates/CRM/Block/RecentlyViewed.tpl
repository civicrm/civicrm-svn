{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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
{* Displays recently viewed objects (contacts and other objects like groups, notes, etc. *}
<div id="recently-viewed">
    <ul>
    {foreach from=$recentlyViewed item=item}
         <li class="crm-recently-viewed" ><a  href="#" title="{$item.title}">
         {if $item.image_url}
            <span class="icon crm-icon {if $item.subtype}{$item.subtype}{else}{$item.type}{/if}-icon" style="background: url('{$item.image_url}')"></span>
         {else}
            <span class="icon crm-icon {$item.type}{if $item.subtype}-subtype{/if}-icon"></span>
         {/if}
         {if $item.isDeleted}<del>{/if}{$item.title|mb_truncate:25:"..":true}{if $item.isDeleted}</del>{/if}</a>
         <ul class="crm-recentview-wrapper" style="display:none;">
      
         <li><br /><a href='{$item.url}'>View</a> &nbsp;
	           {if $item.edit_url}<a href='{$item.edit_url}'>Edit</a> &nbsp;{/if}    
		   {if $item.delete_url}<a href='{$item.delete_url}'>Delete</a> &nbsp;{/if}
	</li>
         </ul>
         </li>
	 
    {/foreach}
   </ul>
</div>
{literal}
<script type="text/javascript">
    $( function( ) {
       	$('li.crm-recently-viewed')
	.addClass('crm-processed')
	.hover(
		function(e)  {
		    $(this).addClass('crm-recentview-active');
		
		      var pos       = $(this).parent().offset();
		      var eleWidth  = cj(this).width( );
		      var eleHeight = cj(this).height( );
		    
		      var addStyle  = 'display:block;';
		      if ( eleWidth == 'undefined' ) {
		      	 eleWidth   = 180;
		      }
		      if ( eleHeight == 'undefined' ) {
		         eleHeight  = 24;    
		      }
		      		      
		      var linkCount = $('ul li',this).children().size();
		      if ( linkCount ) {
		      	addStyle += 'width:'+ ( linkCount*30) + 'px;'; 
		      }		      

 		      if ( pos.left >= 150 ) { 
		      	addStyle += 'margin-left:-'+ (linkCount*30+2) + 'px;';
			addStyle += 'margin-top:-'+ (eleHeight+2) + 'px;';
		        if ($(this).children('ul.crm-recentview-wrapper-right').length == '' ) {
		          $(this).children('ul').addClass('crm-recentview-wrapper-right');
		        }
		        $(this).children('ul').attr('style', addStyle);
		      } else {
		        addStyle += 'margin-left:'+ (eleWidth-1) + 'px;';
		      	addStyle += 'margin-top:-'+ (eleHeight+2) + 'px;';
		        if ($(this).children('ul.crm-recentview-wrapper-left').length == '' ) {
		      	  $(this).children('ul').addClass('crm-recentview-wrapper-left');
		        }
		        $(this).children('ul').attr('style', addStyle);
		      }	 
		      
		},
		function(){
		 $(this).children('ul.crm-recentview-wrapper').attr('style','display:none' );
		 $(this).removeClass('crm-recentview-active');
		}
		)
	.click(function() {return true;});	
	
    });
 
{/literal}
</script>