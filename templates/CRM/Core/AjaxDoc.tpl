<html>
<title>REST API explorer</title>
<style>
{literal}
#result {background:lightgrey;}
#selector a {margin-right:10px;}
.required {font-weight:bold;}
{/literal}
</style>
<script>
restURL = '{crmURL p="civicrm/ajax/rest"}';
if (restURL.indexOf('?') == -1 )
  restURL = restURL + '?';
else 
  restURL = restURL + '&';
{literal}

function toggleField (name,label,type) {
  h = '<div><label>'+label+'</label><input name='+name+ ' id="'+name+ ' /></div>';
  if ( $('#extra #'+ name).length > 0) {
    $('#extra #'+ name).parent().remove();
  }
  $('#extra').append (h);

}

function buildForm (entity, action) {
  id = entity+ '_id';
  h = '<label>'+id+'</label><input id="'+id+ '" size="3" maxlength="20" />';
  if (action == 'delete') {
    $('#extra').html(h);
    return;
  }
  query = restURL+'json=1&version=3&entity='+entity+'&action=getFields';
  $.getJSON(query,function(data) {
      h='<i>Available fields:</i>';
      $.each(data, function(key, value) { 
        name =value.name;
        if (name == 'id') 
          name = entity+'_id';
        if (value.title == undefined) {
          value.title = value.name;
        }
        if (value.required == true) {
          required = " required";
        } else {
          required = "";
        }
        h= h + "<a id='"+name+"' class='type_"+ value.type +  required +"'>"+value.title+"</a>";
        //h= h + "<label>"+data[key].title+"</label>"+"<input id='"+data[key].name+"' />";
      });
      $('#selector').html(h).find ('a').click (function(){
        toggleField (this.id,this.innerHTML,this.class);
      });
      
  });
}

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
    extra ="";
    $('#extra input').each (function (i) {
      val = $(this).val();
      if (val) {
        extra = extra + "&" +this.id +"="+val;
      }
    });
    query = restURL+json+debug+'version='+version+'&entity='+entity+'&action='+action+extra;
    $('#query').val (query);
    if (action == 'delete' && $('#selector a').length == 0) {
      buildForm (entity, action); 
      return; 
    }
    if ( action =='create' && $('#selector a').length == 0) {
      buildForm (entity, action); 
      return; 
    }
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
  $('#entity').change (function() { $("#selector").empty();generateQuery();  });
  $('#action').change (function() { $("#selector").empty();generateQuery();  });
  $('#version').change (function() { generateQuery();  });
  $('#debug').change (function() { generateQuery();  });
  $('#json').change (function() { generateQuery();  });
  $('#explorer').submit(function() {runQuery($('#query').val()); return false; });

  $('#extra').live ('change',function () {
    generateQuery();
  });
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
  <option value="update">update</option>
</select>
<label>debug</label>
<input type="checkbox" id="debug" checked="checked">
<label>json</label>
<input type="checkbox" id="json" checked="checked">
<br>
<div id="selector"></div>
<div id="extra"></div>
<input size="90" id="query" value="{crmURL p="civicrm/ajax/rest" q="json=1&debug=on&entity=contact&action=get&sequential=1&return=display_name,email,phone"}"/>
<div id="link"></div>
<div id="smarty" title='smarty syntax (mostly works for get actions)'></div>
<div id="php" title='php syntax, crm_api needs a few more coding to work as advertised'></div>
<pre id="result">
</pre>
</body>
</html>
