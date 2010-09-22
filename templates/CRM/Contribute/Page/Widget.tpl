{literal}
<style>
    .crm-contribute-widget {
        font-size:12px;
        font-family:Helvetica, Arial, sans;
        padding:6px;
        -moz-border-radius: 	    4px;
        -webkit-border-radius: 	4px;
        -khtml-border-radius: 	4px;
        border-radius:			4px;
        border:1px solid #96C0E7;
        width:200px;
    }
    .crm-contribute-widget h5 {
        font-size:14px;
        padding:3px;
        margin: 0px;
        text-align:center;
        -moz-border-radius: 	4px;
        -webkit-border-radius: 	4px;
        -khtml-border-radius: 	4px;
        border-radius:			4px;
    }

    .crm-contribute-widget .crm-amounts {
        height:1em;
        margin:.8em 0px;
        fon-size:13px;
    }
    .crm-contribute-widget .crm-amount-low {
        float:left;
    }
    .crm-contribute-widget .crm-amount-high {
        float:right;
    }
    .crm-contribute-widget .crm-percentage {
        margin:0px 30%;
        text-align:center;
    }
    .crm-contribute-widget .crm-amount-bar {
        background-color:#FFF;
        width:100%;
        display:block;
        border:1px solid #CECECE;
        -moz-border-radius: 	4px;
        -webkit-border-radius: 	4px;
        -khtml-border-radius: 	4px;
        border-radius:			4px;
        margin-bottom:.8em;
    }
    .crm-contribute-widget .crm-amount-fill {
        background-color:#2786C2;
        height:1em;
        display:block;
        -moz-border-radius: 	4px 0px 0px 4px;
        -webkit-border-radius: 	4px 0px 0px 4px;
        -khtml-border-radius: 	4px 0px 0px 4px;
        border-radius:			4px 0px 0px 4px;
    }
    .crm-contribute-widget .crm-amount-raised-wrapper {
        margin-bottom:.8em;
    }
    .crm-contribute-widget .crm-amount-raised {
        font-weight:bold;
    }
    .crm-contribute-widget .crm-amount-total {
        font-weight:bold;
    }

    .crm-contribute-widget .crm-logo {
        text-align:center;
    }

    .crm-contribute-widget .crm-comments,
    .crm-contribute-widget .crm-donors{
        font-size:11px;
        margin-bottom:.8em;
    }

    .crm-contribute-widget .crm-contribute-button {
        display:block;
        background-color:#CECECE;
        -moz-border-radius: 	    4px;
        -webkit-border-radius: 	4px;
        -khtml-border-radius: 	4px;
        border-radius:			4px;
        text-align:center;
        margin:0px 10% .8em 10%;
        text-decoration:none;
        color:#556C82;
        padding:2px;
        font-size:13px;
    }

    .crm-contribute-widget .crm-home-url {
        text-decoration:none;
        border:0px;
    }

    .crm-contribute-widget .crm-contribute-button-inner {
        padding:2px;
        display:block;
    }
</style>
<style>
    .crm-contribute-widget { 
        background-color: {/literal}{$form.color_main.value}{literal}; /* background color */
        border-color:{/literal}{$form.color_bg.value}{literal}; /* border color */
    }

    .crm-contribute-widget h5 {
        color: {/literal}{$form.color_title.value}{literal};
        background-color: {/literal}{$form.color_main_bg.value}{literal};
    } /* title */

    .crm-contribute-widget .crm-amount-raised { color:#000; }
    .crm-contribute-widget .crm-amount-total { color:#000; }
    .crm-contribute-widget .crm-amount-bar  /* progress bar */
        background-color:{/literal}{$form.color_bar.value}{literal};
        border-color:#CECECE;
    }
    .crm-contribute-widget .crm-amount-fill { background-color:#2786C2; }

    .crm-contribute-widget .crm-contribute-button { /* button color */
        background-color:{/literal}{$form.color_button.value}{literal};
        color:{/literal}{$form.color_about_link.value}{literal};
    }

    .crm-contribute-widget .crm-comments,
    .crm-contribute-widget .crm-donors{
        color:{/literal}{$form.color_main_text.value}{literal} /* other color*/
    }

</style>
{/literal}

<div id="crm_cpid_{$cpageId}" class="crm-contribute-widget">
    <h5 id="crm_cpid_{$cpageId}_title">Title</h5>
    <div class="crm-amounts">
        <div id="crm_cpid_{$cpageId}_amt_hi" class="crm-amount-high">$500</div>
        <div id="crm_cpid_{$cpageId}_amt_low" class="crm-amount-low">$0</div>
        <div id="crm_cpid_{$cpageId}_percentage" class="crm-percentage">20%</div>
    </div>
    <div class="crm-amount-bar">
        <div class="crm-amount-fill" id="crm_cpid_{$cpageId}_amt_fill"></div>
    </div>
    <div class="crm-amount-raised-wrapper">
        Raised <span id="crm_cpid_{$cpageId}_amt_raised" class="crm-amount-raised">$1,7350</span> of <span id="crm_cpid_{$cpageId}_amt_total" class="crm-amount-total">$7,500</span>.
    </div>
    <div class="crm-logo"><a class="crm-home-url" href="{$form.url_homepage.value}"><img src="{$form.url_logo.value}" alt={ts}Logo{/ts}></a></div>
    <div id="crm_cpid_{$cpageId}_donors" class="crm-donors">
        14 Donors
    </div>
    <div id="crm_cpid_{$cpageId}_comments" class="crm-comments">
        This campaign is ongoing.
    </div>
    <div class="crm-contribute-button-wrapper">
        <a href='{crmURL p="civicrm/contribute/transact" q="reset=1&id=$cpageId" h=0 a=1}' class="crm-contribute-button"><span class="crm-contribute-button-inner" id="crm_cpid_{$cpageId}_btn_txt">Contribute</span></a>
    </div>
</div>

{literal}

<script type="text/javascript">
    //create onDomReady Event
    window.onDomReady = DomReady;

    //Setup the event
    function DomReady(fn) { //W3C
        if(document.addEventListener) {
            document.addEventListener("DOMContentLoaded", fn, false);
        } else { //IE
            document.onreadystatechange = function(){readyState(fn)}
        }
    }

    //IE execute function
    function readyState(fn) {
        //dom is ready for interaction
        if(document.readyState == "interactive") {
            fn();
        }
    }

    window.onDomReady(onReady);

    function onReady( ) {
        var crmCurrency = jsondata.currencySymbol;
        var cpid        = {/literal}{$cpageId}{literal};
        document.getElementById('crm_cpid_'+cpid+'_title').innerHTML        = jsondata.title;
        document.getElementById('crm_cpid_'+cpid+'_amt_hi').innerHTML       = crmCurrency+jsondata.money_target;
        document.getElementById('crm_cpid_'+cpid+'_amt_low').innerHTML      = crmCurrency+jsondata.money_low;
        document.getElementById('crm_cpid_'+cpid+'_amt_raised').innerHTML   = crmCurrency+jsondata.money_raised;
        document.getElementById('crm_cpid_'+cpid+'_amt_total').innerHTML    = crmCurrency+jsondata.money_target;
        document.getElementById('crm_cpid_'+cpid+'_comments').innerHTML     = jsondata.about;
        document.getElementById('crm_cpid_'+cpid+'_donors').innerHTML       = jsondata.num_donors;
        document.getElementById('crm_cpid_'+cpid+'_btn_txt').innerHTML      = jsondata.button_title;
        var percentComplete = 0;
        if ( jsondata.money_raised > 0 ) {
          percentComplete = (jsondata.money_raised/jsondata.money_target)*100+'%';
        }
        document.getElementById('crm_cpid_'+cpid+'_amt_fill').style.width   = percentComplete;
        document.getElementById('crm_cpid_'+cpid+'_percentage').innerHTML   = percentComplete;
    }
    
</script>
{/literal}
<script type="text/javascript" src="{$config->userFrameworkResourceURL}/extern/widget.php?cpageId={$cpageId}&widgetId={$widget_id}"></script>

