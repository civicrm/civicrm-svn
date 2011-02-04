<html>
<title>REST API explorer</title>
<style>
{literal}
#result {background:lightgrey;}
{/literal}
</style>
<script>
restURL = '{crmURL p="civicrm/ajax/rest"}';
if (restURL.indexOf('?') == -1 )
  restURL = restURL + '?';
else 
  restURL = restURL + '&';
{literal}
function generateQuery () {
    var version = $('#version').val();
    var entity = $('#entity').val();
    var action = $('#action').val();
    var debug = "";
    if ($('#debug').attr('checked'))
      debug= "debug=1&";
    var json = "";
    if ($('#json').attr('checked'))
      json= "json=1&";
    query="";
    if (entity == '') {query= "Choose an entity. "};
    if (action == '') {query=query + "Choose an action.";}
    if (entity == '' || action == '') {
      $('#query').val (query);
      return;
    }
    query = restURL+json+debug+'version='+version+'&entity='+entity+'&action='+action;
    $('#query').val (query);
    runQuery (query);
}

function runQuery(query) {
    var vars = [], hash,smarty = '',php = "<hr>$params = array (";
    $.get(query,function(data) {
      $('#result').text(data);
    },'text');
    $("#link").html("<a href='"+query+"' title='open in a new tab' target='_blank'>link to the REST query</a>");

    var hashes = query.slice(query.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++) {
       
        hash = hashes[i].split('=');
        switch (hash[0]) {
           case 'debug':
           case 'json':
             break;
           case 'action':
             var action= hash[1];
             break;
           case 'entity':
             var entity= hash[1];
             break;
           default:
             smarty = smarty+ hash[0] + '="'+hash[1]+ '" ';
             php = php+"'"+ hash[0] +"' =>'"+hash[1]+ "', ";
        }
    }
    $('#php').html(php + '};<br>$results=civicrm_api("'+entity+'","'+action+'",$params);</hr>');
    if (action == "get") {//using smarty only make sense for get action
      $('#smarty').html('{crmAPI var="'+entity+'S" entity="'+entity+'" action="'+action+'" '+smarty+'}<br>{foreach from=$'+entity+'S.values item='+entity+'}<br/>  &lt;li&gt;{$'+entity+'.example}&lt;/li&gt;<br>{/foreach}');
    }

}

cj(function ($) {
  $('#entity').change (function() { generateQuery();  });
  $('#action').change (function() { generateQuery();  });
  $('#version').change (function() { generateQuery();  });
  $('#debug').change (function() { generateQuery();  });
  $('#json').change (function() { generateQuery();  });
  $('#explorer').submit(function() {runQuery($('#query').val()); return false; });
});
{/literal}
</script>
<body>
<form id="explorer">
<label>version</label>
<select id="version">
  <option value="3" selected="selected">3</option>
  <option value="2">2</option>
</select>
<label>entity</label>
<select id="entity">
  <option value="" selected="selected">Choose...</option>
{crmAPI entity="entity" action="get" var="entities"}
{foreach from=$entities.values item=entity}
  <option value="{$entity}">{$entity}</option>
{/foreach}
</select>
<label>action</label>
<select id="action">
  <option value="" selected="selected">Choose...</option>
  <option value="get">get</option>
  <option value="create">create</option>
  <option value="delete">delete</option>
  <option value="getfields">getfields</option>
</select>
<label>debug</label>
<input type="checkbox" id="debug" checked="checked">
<label>json</label>
<input type="checkbox" id="json" checked="checked">
<br>
<input size="90" id="query" value="{crmURL p="civicrm/ajax/rest" q="json=1&debug=on&entity=contact&action=get&sequential=1&return=display_name,email,phone"}"/>
<div id="link"></div>
<div id="smarty" title='smarty syntax (mostly works for get actions)'></div>
<div id="php" title='php syntax, crm_api needs a few more coding to work as advertised'></div>
<pre id="result">
</pre>
</body>
</html>
