{*common template for compose mail*}
 <div><dt class="label-left">{$form.template.label}</dt><dd>{$form.template.html}</dd></div>
  <div class="accordion ui-accordion ui-widget ui-helper-reset">{help id="id-message-text" file="CRM/Contact/Form/Task/Email.hlp}
         <h3 class="head"><span class="ui-icon ui-icon-triangle-1-e" id='html'></span><a href="#">{$form.html_message.label}</a></h3>
          <div class='html'>
              <dl>
                {if $editor EQ 'textarea'}
                <span class="description">{ts}If you are composing HTML-formatted messages, you may want to enable a WYSIWYG editor (Administer CiviCRM &raquo; Global Settings &raquo; Site Preferences).{/ts}</span><br />
            {/if}
                {$form.html_message.html}<br />
                {$form.token2.label} {help id="id-token-html" file="CRM/Contact/Form/Task/Email.hlp"}<br />{*$form.token2.html*}</dl>
          </div>
        <h3 class="head"><span class="ui-icon ui-icon-triangle-1-e" id='text'></span><a href="#">{$form.text_message.label}</a></h3>
          <div class='text'>
              <dl>{$form.text_message.html|replace:'80':'105'}<br />{* expanded the text box as per jQuery tab width *}
              {$form.token1.label}{help id="id-token-text" file="CRM/Contact/Form/Task/Email.hlp"}<br/>{*$form.token1.html*}</dl>
          </div>
  </div>
{if ! $noAttach}
    <div class="spacer"></div>
    {include file="CRM/Form/attachment.tpl"}
{/if}

<div class="spacer"></div>

<div id="editMessageDetails">
    <div id="updateDetails" >
        {$form.updateTemplate.html}&nbsp;{$form.updateTemplate.label}
    </div>
    <div>
        {$form.saveTemplate.html}&nbsp;{$form.saveTemplate.label}
    </div>
</div>

<div id="saveDetails">
    {$form.saveTemplateName.label}</dt><dd>{$form.saveTemplateName.html}
</div>

{literal}
<script type="text/javascript" >
{/literal}
{if $templateSelected}
{literal}
if ( document.getElementsByName("saveTemplate")[0].checked ) {
   document.getElementById('template').selectedIndex = {/literal}{$templateSelected}{literal};  	
}
{/literal}
{/if}
{literal}
var editor = {/literal}"{$editor}"{literal};
function loadEditor()
{
    var msg =  {/literal}"{$htmlContent}"{literal};
    if (msg) {
        if ( editor == "fckeditor" ) {
            oEditor = FCKeditorAPI.GetInstance('html_message');
            oEditor.SetHTML( msg );
        } else if ( editor == "tinymce" ) {
            tinyMCE.get('html_message').setContent( msg );
        }
    }
}

function showSaveUpdateChkBox()
{
    if ( document.getElementById('template') == null ) {
        if (document.getElementsByName("saveTemplate")[0].checked){
            document.getElementById("saveDetails").style.display = "block";
            document.getElementById("editMessageDetails").style.display = "block";
        } else {
            document.getElementById("saveDetails").style.display = "none";
            document.getElementById("editMessageDetails").style.display = "none";
        }
        return;
    }

    if ( document.getElementsByName("saveTemplate")[0].checked && document.getElementsByName("updateTemplate")[0].checked == false  ) {
        document.getElementById("updateDetails").style.display = "none";
    }else if ( document.getElementsByName("saveTemplate")[0].checked && document.getElementsByName("updateTemplate")[0].checked ){
        document.getElementById("editMessageDetails").style.display = "block";	
        document.getElementById("saveDetails").style.display = "block";	
    }else if ( document.getElementsByName("saveTemplate")[0].checked == false && document.getElementsByName("updateTemplate")[0].checked ){
        document.getElementById("saveDetails").style.display = "none";
        document.getElementById("editMessageDetails").style.display = "block";
    } else {
        document.getElementById("saveDetails").style.display = "none";
        document.getElementById("editMessageDetails").style.display = "none";
    }

}

function selectValue( val ) {
    if ( !val ) {
        document.getElementById("text_message").value ="";
        document.getElementById("subject").value ="";
        if ( editor == "fckeditor" ) {
            oEditor = FCKeditorAPI.GetInstance('html_message');
            oEditor.SetHTML('');
        } else if ( editor == "tinymce" ) {
            tinyMCE.get('html_message').setContent('');
        } else {	
            document.getElementById("html_message").value = '' ;
        }
        return;
    }

    var dataUrl = {/literal}"{crmURL p='civicrm/ajax/template' h=0 }"{literal};

    cj.post( dataUrl, {tid: val}, function( data ) {
        cj("#subject").val( data.subject );

        if ( data.msg_text ) {      
            cj("#text_message").val( data.msg_text );
        } else {
            cj("#text_message").val("");
        }

        var html_body  = "";
        if (  data.msg_html ) {
           html_body = data.msg_html;
        }

        if ( editor == "fckeditor" ) {
            oEditor = FCKeditorAPI.GetInstance('html_message');
            oEditor.SetHTML( html_body );
        } else if ( editor == "tinymce" ) {
            tinyMCE.get('html_message').setContent( html_body );
        } else {	
            cj("#html_message").val( html_body );
        }
       
    }, 'json');    
}

 
document.getElementById("editMessageDetails").style.display = "block";

function verify( select )
{
    if ( document.getElementsByName("saveTemplate")[0].checked  == false ) {
        document.getElementById("saveDetails").style.display = "none";
    }
    document.getElementById("editMessageDetails").style.display = "block";

    var templateExists = true;
    if ( document.getElementById('template') == null ) {
        templateExists = false;
    }

    if ( templateExists && document.getElementById('template').value ) {
        document.getElementById("updateDetails").style.display = '';
    } else {
        document.getElementById("updateDetails").style.display = 'none';
    }

    document.getElementById("saveTemplateName").disabled = false;
}
   
function showSaveDetails(chkbox) 
{
    if (chkbox.checked) {
        document.getElementById("saveDetails").style.display = "block";
        document.getElementById("saveTemplateName").disabled = false;
    } else {
        document.getElementById("saveDetails").style.display = "none";
        document.getElementById("saveTemplateName").disabled = true;
    }
}
	
showSaveUpdateChkBox();

function tokenReplText ( )
{
    var token = document.getElementById("token1").options[document.getElementById("token1").selectedIndex].text;
    var msg       = document.getElementById("text_message").value;
    var cursorlen = document.getElementById("text_message").selectionStart;
    var textlen   = msg.length;
    document.getElementById("text_message").value = msg.substring(0, cursorlen) + token + msg.substring(cursorlen, textlen);
    var cursorPos = (cursorlen + token.length);
    document.getElementById("text_message").selectionStart = cursorPos;
    document.getElementById("text_message").selectionEnd   = cursorPos;
    document.getElementById("text_message").focus();
    verify();
}

function tokenReplHtml ( )
{
    var token2 = document.getElementById("token2").options[document.getElementById("token2").selectedIndex].text;
    var editor = {/literal}"{$editor}"{literal};
    if ( editor == "tinymce" ) {
        tinyMCE.execInstanceCommand("html_message", "mceInsertContent", false, token2);
    } else if ( editor == "fckeditor" ) {
        oEditor = FCKeditorAPI.GetInstance('html_message');
        oEditor.InsertHtml( token2 );	
    } else {
        var msg       = document.getElementById("html_message").value;
        var cursorlen = document.getElementById("html_message").selectionStart;
        var textlen   = msg.length;
        document.getElementById("html_message").value = msg.substring(0, cursorlen) + token2 + msg.substring(cursorlen, textlen);
        var cursorPos = (cursorlen + token2.length);
        document.getElementById("html_message").selectionStart = cursorPos;
        document.getElementById("html_message").selectionEnd   = cursorPos;
        document.getElementById("html_message").focus();
    }
    verify();
}
{/literal}
{if $editor eq "fckeditor"}
{literal}
	function FCKeditor_OnComplete( editorInstance )
	{
	 	oEditor = FCKeditorAPI.GetInstance('html_message');
		oEditor.SetHTML( {/literal}'{$message_html}'{literal});
		loadEditor();	
		editorInstance.Events.AttachEvent( 'OnFocus',verify ) ;
    	}
{/literal}
{/if}
{if $editor eq "tinymce"}
{literal}
	function customEvent() {
		loadEditor();
		tinyMCE.get('html_message').onKeyPress.add(function(ed, e) {
 		verify();
		});
	}

tinyMCE.init({
	oninit : "customEvent"
});

{/literal}
{/if}
{literal}

    cj(function() {
        cj('.accordion .head').addClass( "ui-accordion-header ui-helper-reset ui-state-default ui-corner-all ");
        cj('.ui-state-default, .ui-widget-content .ui-state-default').css( 'width', '95%' );
        cj('.accordion .head').hover( function() { cj(this).addClass( "ui-state-hover");
        }, function() { cj(this).removeClass( "ui-state-hover");
    }).bind('click', function() { 
        var checkClass = cj(this).find('span').attr( 'class' );
        var len        = checkClass.length;
        if ( checkClass.substring( len - 1, len ) == 's' ) {
            cj(this).find('span').removeClass().addClass('ui-icon ui-icon-triangle-1-e');
        } else {
            cj(this).find('span').removeClass().addClass('ui-icon ui-icon-triangle-1-s');
        }
        cj(this).next().toggle('blind'); return false; }).next().hide();
        cj('span#html').removeClass().addClass('ui-icon ui-icon-triangle-1-s');cj("div.html").show();          
    });
</script>
{/literal}
