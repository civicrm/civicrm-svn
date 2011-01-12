<?xml version="1.0" encoding="utf-8"?>
<access component="com_civicrm">
	<section name="component">
{foreach from=$permissions item=title key=name}
		<action name="civicrm.{$name}" title="{$title}" description="" />
{/foreach}
	</section>
</access>
