{l s='Ожидание перенаправления' mod='wayforpay'}

<form id="wayforpay_payment" method="post" action="{$url}">
    {foreach from=$fields  key=key item=field}
        {if $field|is_array}
            {foreach from=$field  key=k item=v}
                <br />{$key}[]:<input type="text" name="{$key}[]" value="{$v}" />
            {/foreach}
        {else}
            <br />{$key}:<input type="text" name="{$key}" value="{$field}" />
        {/if}
    {/foreach}

	<input type="submit" value="{l s='Оплатить' mod='wayforpay'}">
</form>

<script type="text/javascript">
	//$('#wayforpay_payment').submit();
</script>