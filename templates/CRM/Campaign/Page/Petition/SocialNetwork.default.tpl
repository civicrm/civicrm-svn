{literal}
<script>
if (FB != undefined) { // reprocess the XFBML tags if called in ajax (otherwise, that's done by the init)
  FB.XFBML.parse(document.getElementById('socialnetwork'));

}
</script>
{/literal}

{* 
You might have a specific page that displays more information that the form.

Check SocialNetwork.example.tpl (that is copied into SocialNetwork.tpl right now)

TODISCUSS: Worthwhile putting the form sign as default url ?
*}

