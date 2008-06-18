{if $action eq 1024}{include file="CRM/Contribute/Form/Contribution/ReceiptPreviewHeader.tpl"}
{/if}
{if $formValues.receipt_text }
{$formValues.receipt_text}
{else}{ts}Thanks for your support.{/ts}{/if}

{ts}Please print this receipt for your records.{/ts}


===========================================================
{ts}Contribution Information{/ts}

===========================================================
{ts}Contribution Type{/ts}: {$formValues.contributionType_name}
{ts}Total Amount{/ts}: {$formValues.total_amount|crmMoney}
{if $receive_date}
{ts}Received Date{/ts}: {$receive_date|truncate:10:''|crmDate}
{/if}
{if $formValues.paidBy}
{ts}Paid By{/ts}: {$formValues.paidBy}
{/if}
{if $formValues.trxn_id}
{ts}Transaction ID{/ts}: {$formValues.trxn_id}
{/if}

{if $billingBlock}
===========================================================
{ts}Billing Information{/ts}

===========================================================
{foreach from=$billingBlock item=value key=name}
 {$name}: {$value}
{/foreach}
{/if}

{if $showCustom}
===========================================================
{ts}Additional Information{/ts}

===========================================================
{foreach from=$customField item=value key=name}
 {$name}: {$value}
{/foreach}
{/if}
{if $formValues.honor_first_name}

===========================================================
{$formValues.honor_type}
===========================================================
{$formValues.honor_prefix} {$formValues.honor_first_name} {$formValues.honor_last_name}
{if $formValues.honor_email}
{ts}Honoree Email{/ts}: {$formValues.honor_email}
{/if}
{/if}

{if $formValues.product_name }
===========================================================
{ts}Premium Information{/ts}

===========================================================
{$formValues.product_name}
{if $formValues.product_option}
{ts}Option{/ts}: {$formValues.product_option}
{/if}
{if $formValues.product_sku}
{ts}SKU{/ts}   : {$formValues.product_sku}
{/if}
{ts}Sent{/ts}  : {$fulfilled_date|crmDate}

{/if}
