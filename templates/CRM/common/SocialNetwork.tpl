{*This file should be placed in civicrm/templates/CRM/common*}
{* Add social networking buttons (Facebook like, Twitter tweet, and Google +1) to civi pages*}
<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>

<div id="crm-socialnetwork" class="crm-section">
    <h2>Help spread the word</h2>
    <div>
        <p>{ts}Please help us and let your friends, colleagues and followers know about our page.{/ts}</p>
    </div>
    {$title}
    <div class="crm_fb_tweet_buttons">
        {if $emailMode eq 'True'}
            {*use images for email*}
            <a href="http://twitter.com/share?url={$url}&amp;text={$title}" id="crm_tweet">
                <img title="Twitter Tweet Button" src="{$config->userFrameworkResourceURL}/i/tweet.png" width="55px" height="20px"  alt="Tweet Button">
            </a>

            <a href="http://www.facebook.com/plugins/like.php?href={$url}" target="_blank">
                <img title="Facebook Like Button" src="{$config->userFrameworkResourceURL}/i/fblike.png" alt="Facbook Button" />
            </a>
        {else}
            {*use advanced buttons for pages*}
            <div class="label">
                <iframe allowtransparency="true" frameborder="0" scrolling="no"
                src="http://platform.twitter.com/widgets/tweet_button.html?text={$title}&amp;url={$url}" 
                style="width:130px; height:50px;">
                </iframe>
            </div>
            <div class="label">
                <g:plusone href={$url}></g:plusone>
            </div>
            <div class="label">
                <iframe src="http://www.facebook.com/plugins/like.php?app_id=240719639306341&amp;href={$url}&amp;send=false&amp;layout=standard&amp;width=450&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:35px;" allowTransparency="true">
                </iframe>
            </div>
        {/if}
    </div>
    <div class="clear"></div>
    <div>{ts}<b>You can also share the link in an email or on your website.</b>{/ts}</div>
    <div>
        {ts 1=$pageURL}<a href="%1">%1</a>{/ts}
    </div>
</div>


