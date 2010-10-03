{* 
You might have a specific page that displays more information that the form.

Check SocialNetwork.drupal as an example

*}
{assign var=image_url value=$config->userFrameworkBaseURL}
{capture assign=petitionURL}{$config->userFrameworkBaseURL}{crmURL p='civicrm/petition/sign' q="sid=$petition_id"}{/capture}
	<h2>Help spread the word about our petition</h2>
	Please help us and let your friends, colleagues and followers know about our campaign.
	<h3>Do you use Facebook or Twitter ?</h3>
  <div id="crm_socialnetwork">
		<p>Share it on Facebook or tweet it on Twitter.</p>
		<a href="http://www.facebook.com/sharer.php?u={$petitionURL}" id="crm_fbshare">
			<img src="{$config->userFrameworkBaseURL}{$config->resourceBase}i/fbshare.png" width="70px" height="28px" alt="Facebook Share Button">
		</a>
		<a href="http://twitter.com/share?url={$petitionURL}&amp;text=Sign this, I did" id="crm_tweet">
			<img src="{$config->userFrameworkBaseURL}{$config->resourceBase}/i/tweet.png" width="55px" height="20px"  alt="Tweet Button">
		</a>		  
  </div
	<h3>Do you have a website for your organisation or yourself?</h3>
	<p>You can write a story about it - don't forget to add the link to <a href="{$petitionURL}">{$petitionURL}.</a></p>


