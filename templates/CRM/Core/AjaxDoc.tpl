<html>
<title>REST API explorer</title>
<script>
restURL = '{crmURL p="civicrm/ajax/rest"}';
{literal}
function generateQuery () {
    var version = $('#version').val();
    var entity = $('#entity').val();
    var action = $('#action').val();
    var debug = $('#debug').val();
    query="";
    if (entity == '') {query= "Choose an entity. "};
    if (action == '') {query=query + "Choose an action.";}
    if (entity == '' || action == '') {
      $('#query').val (query);
      return;
    }
    query = restURL+'?json=1&debug='+debug+'&version='+version+'&entity='+entity+'&action='+action;
    $('#query').val (query);
    runQuery (query);
}

function runQuery(query) {
    $.get(query,function(data) {
      $('#result').text(data);
    },'text');
    $("#link").html("<a href='"+query+"' title='open in a new tab' target='_blank'>link to the REST query</a>")
}

cj(function ($) {
  $('#entity').change (function() { generateQuery();  });
  $('#action').change (function() { generateQuery();  });
  $('#version').change (function() { generateQuery();  });
  $('#debug').change (function() { generateQuery();  });
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
  <option value="contact">Contact</option>
  <option value="relationship">Relationship</option>
  <option value="tag">Tag</option>
  <option value="group">Group</option>
</select>
<label>action</label>
<select id="action">
  <option value="" selected="selected">Choose...</option>
  <option value="get">get</option>
  <option value="create">Create</option>
  <option value="delete">Delete</option>
  <option value="getfields">get fields</option>
</select>
<label>debug</label>
<input type="checkbox" id="debug" checked="checked">
<br>
<input size="80" id="query" value="/civicrm/ajax/rest?json=1&debug=on&entity=contact&action=get&sequential=1"/>
<div id="link"></div>
<pre id="result">
</pre>
</body>
</html>
