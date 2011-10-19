{assign var=form_participant value=$participant->get_form()}
  <fieldset class="participant" id="event_{$participant->event_id}_participant_{$participant->id}">
    <legend>
      {$form_participant->name()}
    </legend>
	<div class="clearfix">
	  {assign var=fields value=$form_participant->get_fields()}
	  {foreach from=$fields item=field}
		{assign var=field_name value=$field.name}
	  <div class="participant-info crm-section form-item">
	    <div class="label">
		{$form.$field_name.label}
	    </div>
	    <div class="edit-value content">
		{$form.$field_name.html}
	    </div>
	  </div>
	  {/foreach}
	</div>
    <!--if $form_participant->participant_index > 0-->
    <a class="link-delete" href="#" onclick="delete_participant({$participant->event_id}, {$participant->id})">Delete {$form_participant->name()}</a>
  </fieldset>
