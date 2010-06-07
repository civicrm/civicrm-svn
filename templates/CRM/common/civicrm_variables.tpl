{* This contains the variables that various jQuery functions need, for instance to define the ajax url to call *}
<script type="text/javascript">
civicrm = new Object;

// doesn't seem to be set
//  relativeURL : "{$config->civiRelativeURL}",
//  absoluteURL : "{$config->civiAbsoluteURL}"
civicrm.config = {ldelim}
  FrameworkBaseURL     : "{$config->userFrameworkBaseURL}",
  FrameworkResourceURL: "{$config->userFrameworkResourceURL}",
  RestURL = "{crmURL p='civicrm/ajax/rest' q='json=1'}"
{rdelim};
</script>
