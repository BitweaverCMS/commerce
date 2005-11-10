{if $productOptions}
      <table border="0" width="90%" cellspacing="0" cellpadding="2">
{if $zv_display_select_option > 0}
        <tr>
          <td colspan="2" class="main" align="left">{$smarty.const.TEXT_PRODUCT_OPTIONS}</td>
        </tr>
{/if}
{foreach from=$productOptions key=optionsId item=opts}
	{if $opts.comment}
        <tr>
          <td colspan="2" class="ProductInfoComments" align="left" valign="bottom">{$options_comment[ix]}</td>
        </tr>
	{/if}
        <tr>
          <td class="main" align="left" valign="top">{$opts.name}:</td>
          <td class="main" align="left" valign="top" width="75%">{$opts.menu}</td>
        </tr>
{if $ops.comment && $opts.comment_position == '1'}
        <tr>
          <td colspan="2" class="ProductInfoComments" align="left" valign="top">{$opts.comment}</td>
        </tr>
{/if}

{if $opts.attributes_image}
        <tr>
        	<td colspan="2"><div class="products-attributes-images">{$opts.attributes_image}</td>
        </tr>
{/if}
{/foreach}
{*
<?php
  if ($show_onetime_charges_description == 'true') {
?>
        <tr>
          <td colspan="2" class="main" align="left"><?php echo TEXT_ONETIME_CHARGE_SYMBOL . TEXT_ONETIME_CHARGE_DESCRIPTION; ?></td>
        </tr>
<?php } ?>

<?php
  if ($show_attributes_qty_prices_description == 'true') {
?>
        <tr>
          <td colspan="2" class="main" align="left"><?php echo zen_image(DIR_WS_TEMPLATE_ICONS . 'icon_status_green.gif', TEXT_ATTRIBUTES_QTY_PRICE_HELP_LINK, 10, 10) . '&nbsp;' . '<a href="javascript:popupWindowPrice(\'' . zen_href_link(FILENAME_POPUP_ATTRIBUTES_QTY_PRICES, 'products_id=' . $_GET['products_id'] . '&products_tax_class_id=' . $products_tax_class_id) . '\')">' . TEXT_ATTRIBUTES_QTY_PRICE_HELP_LINK . '</a>'; ?></td>
        </tr>
<?php } ?>
*}
      </table>
{/if}