/*
* Copyright (C) 2009-2010 Xavier Dutoit
* Licensed to CiviCRM under the Academic Free License version 3.0.
*
*/

/*
On the template page that includes this js, you have to define a global variable to set the url of the server to be used for the rest
<script type="text/javascript">
civicrm_resourceURL="{$config->userFrameworkResourceURL}";
</script>

eg. CiviREST (contact/search,array(...))
CiviREST(entitytag,add)

It really should be a class (or one class per entity, inherit from a common civicrmEntity ?
and it really really really should be a post for destructive actions (changes at the server level)

it also should use closure so we can properly interface the result of the call to the called object

    this.loadData = function() {
        var obj = this;
        cj.getJSON( url, function( data ) {
            obj.gotData( data );  // instead of this.gotData( data );
        });
    }; 
*/

function civiREST (entity,action,params,close) {
  params ['fnName']="civicrm/"+entity+"/"+action;
  params ['json'] = 1;
  cj('#restmsg').removeClass('msgok').removeClass('msgnok').html("");
  cj.getJSON(civicrm_ajaxURL,params,function(result){
  if (result.is_error == 1) {
    cj('#restmsg').addClass('msgnok').html(result.error_message);
    return false;
  }
  if( !close ){
	  close = "Hide";
  }
  var successMsg = 'Saved &nbsp; <a href="javascript:hideStatus();">'+ close +'</a>'; 
  cj('#restmsg').addClass('msgok').html( successMsg ).show();
  return true;
  });
}
