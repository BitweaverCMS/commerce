<?php

// braintree_api.php payment module admin display component

	$outputStartBlock = '';
	$outputBraintree = '';
	$outputRefund = '';
	$outputEndBlock = '';
	$output = '';

	$outputStartBlock .= '<td><table class="noprint">'."\n";
	$outputStartBlock .= '<tr style="background-color : #cccccc; border-style : dotted;">'."\n";
	$outputEndBlock .= '</tr>'."\n";
	$outputEndBlock .='</table></td>'."\n";

	// display all braintree status fields (in admin Orders page):
	$outputBraintree .= '<td valign="top"><table>'."\n";

	$outputBraintree .= '<tr><td class="main">'."\n";
	$outputBraintree .= MODULE_PAYMENT_BRAINTREE_ENTRY_FIRST_NAME."\n";
	$outputBraintree .= '</td><td class="main">'."\n";
	$outputBraintree .= $response['FIRSTNAME'] ."\n";
	$outputBraintree .= '</td></tr>'."\n";

	$outputBraintree .= '<tr><td class="main">'."\n";
	$outputBraintree .= MODULE_PAYMENT_BRAINTREE_ENTRY_LAST_NAME."\n";
	$outputBraintree .= '</td><td class="main">'."\n";
	$outputBraintree .= $response['LASTNAME'] ."\n";
	$outputBraintree .= '</td></tr>'."\n";

	$outputBraintree .= '<tr><td class="main">'."\n";
	$outputBraintree .= MODULE_PAYMENT_BRAINTREE_ENTRY_BUSINESS_NAME."\n";
	$outputBraintree .= '</td><td class="main">'."\n";
	$outputBraintree .= $response['BUSINESS'] ."\n";
	$outputBraintree .= '</td></tr>'."\n";

	$outputBraintree .= '<tr><td class="main">'."\n";
	$outputBraintree .= MODULE_PAYMENT_BRAINTREE_ENTRY_ADDRESS_NAME."\n";
	$outputBraintree .= '</td><td class="main">'."\n";
	$outputBraintree .= $response['NAME'] ."\n";
	$outputBraintree .= '</td></tr>'."\n";
	$outputBraintree .= '<tr><td class="main">'."\n";
	$outputBraintree .= MODULE_PAYMENT_BRAINTREE_ENTRY_ADDRESS_STREET."\n";
	$outputBraintree .= '</td><td class="main">'."\n";
	$outputBraintree .= $response['BILLTOSTREET'] . ' ' . $response['BILLTOSTREET2'] ."\n";
	$outputBraintree .= '</td></tr>'."\n";
	$outputBraintree .= '<tr><td class="main">'."\n";
	$outputBraintree .= MODULE_PAYMENT_BRAINTREE_ENTRY_ADDRESS_CITY."\n";
	$outputBraintree .= '</td><td class="main">'."\n";
	$outputBraintree .= $response['BILLTOCITY'] ."\n";
	$outputBraintree .= '</td></tr>'."\n";
	$outputBraintree .= '<tr><td class="main">'."\n";
	$outputBraintree .= MODULE_PAYMENT_BRAINTREE_ENTRY_ADDRESS_STATE."\n";
	$outputBraintree .= '</td><td class="main">'."\n";
	$outputBraintree .= $response['BILLTOSTATE'] . ' ' . $response['BILLTOZIP'] ."\n";
	$outputBraintree .= '</td></tr>'."\n";
	$outputBraintree .= '<tr><td class="main">'."\n";
	$outputBraintree .= MODULE_PAYMENT_BRAINTREE_ENTRY_ADDRESS_COUNTRY."\n";
	$outputBraintree .= '</td><td class="main">'."\n";
	$outputBraintree .= $response['BILLTOCOUNTRY'] ."\n";
	$outputBraintree .= '</td></tr>'."\n";

	$outputBraintree .= '</table></td>'."\n";

	$outputBraintree .= '<td valign="top"><table>'."\n";

	$outputBraintree .= '<tr><td class="main">'."\n";
	$outputBraintree .= MODULE_PAYMENT_BRAINTREE_ENTRY_EMAIL_ADDRESS."\n";
	$outputBraintree .= '</td><td class="main">'."\n";
	$outputBraintree .= $response['EMAIL'] ."\n";
	$outputBraintree .= '</td></tr>'."\n";

	$outputBraintree .= '<tr><td class="main">'."\n";
	$outputBraintree .= MODULE_PAYMENT_BRAINTREE_ENTRY_TXN_ID."\n";
	$outputBraintree .= '</td><td class="main">'."\n";
	$outputBraintree .= '<a href="https://' . (MODULE_PAYMENT_BRAINTREE_SERVER == "sandbox" ? "sandbox" : "www") . '.braintreegateway.com/merchants/' . MODULE_PAYMENT_BRAINTREE_MERCHANTID . '/transactions/' . $response['TRANSACTIONID'] . '" target="_blank">' . $response['TRANSACTIONID'] . '</a>' ."\n";
	$outputBraintree .= '</td></tr>'."\n";

	$outputBraintree .= '<tr><td class="main">'."\n";
	$outputBraintree .= MODULE_PAYMENT_BRAINTREE_ENTRY_PARENT_TXN_ID."\n";
	$outputBraintree .= '</td><td class="main">'."\n";
	$outputBraintree .= $response['PARENTTRANSACTIONID'] ."\n";
	$outputBraintree .= '</td></tr>'."\n";

	$outputBraintree .= '</table></td>'."\n";

	$outputBraintree .= '<td valign="top"><table>'."\n";

	$outputBraintree .= '<tr><td class="main">'."\n";
	$outputBraintree .= MODULE_PAYMENT_BRAINTREE_ENTRY_TXN_TYPE."\n";
	$outputBraintree .= '</td><td class="main">'."\n";
	$outputBraintree .= $response['TRANSACTIONTYPE'] ."\n";
	$outputBraintree .= '</td></tr>'."\n";

	$outputBraintree .= '<tr><td class="main">'."\n";
	$outputBraintree .= MODULE_PAYMENT_BRAINTREE_ENTRY_PAYMENT_TYPE."\n";
	$outputBraintree .= '</td><td class="main">'."\n";
	$outputBraintree .= $response['PAYMENTTYPE'] ."\n";
	$outputBraintree .= '</td></tr>'."\n";

	$outputBraintree .= '<tr><td class="main">'."\n";
	$outputBraintree .= MODULE_PAYMENT_BRAINTREE_ENTRY_PAYMENT_STATUS."\n";
	$outputBraintree .= '</td><td class="main">'."\n";
	$outputBraintree .= $response['PAYMENTSTATUS'] ."\n";
	$outputBraintree .= '</td></tr>'."\n";

	$outputBraintree .= '<tr><td class="main">'."\n";
	$outputBraintree .= MODULE_PAYMENT_BRAINTREE_ENTRY_PAYMENT_DATE."\n";
	$outputBraintree .= '</td><td class="main">'."\n";
	$outputBraintree .= $response['ORDERTIME'] ."\n";
	$outputBraintree .= '</td></tr>'."\n";

	$outputBraintree .= '</table></td>'."\n";

	$outputBraintree .= '<td valign="top"><table>'."\n";

	$outputBraintree .= '<tr><td class="main">'."\n";
	$outputBraintree .= MODULE_PAYMENT_BRAINTREE_ENTRY_CURRENCY."\n";
	$outputBraintree .= '</td><td class="main">'."\n";
	$outputBraintree .= $ipn->fields['mc_currency'] . ' ' . $response['CURRENCY'] ."\n";
	$outputBraintree .= '</td></tr>'."\n";

	$outputBraintree .= '<tr><td class="main">'."\n";
	$outputBraintree .= MODULE_PAYMENT_BRAINTREE_ENTRY_GROSS_AMOUNT."\n";
	$outputBraintree .= '</td><td class="main">'."\n";
	$outputBraintree .= $response['AMT'] ."\n";
	$outputBraintree .= '</td></tr>'."\n";

	$outputBraintree .= '<tr><td class="main">'."\n";
	$outputBraintree .= MODULE_PAYMENT_BRAINTREE_ENTRY_EXCHANGE_RATE."\n";
	$outputBraintree .= '</td><td class="main">'."\n";
	$outputBraintree .= $response['EXCHANGERATE'] ."\n";
	$outputBraintree .= '</td></tr>'."\n";

	$outputBraintree .= '</table></td>'."\n";

	if (method_exists($this, '_doRefund')) {

		try {
	$settled_result = Braintree_Transaction::find($response['TRANSACTIONID']);

		$outputRefund .= '<td><table class="noprint">'."\n";
		$outputRefund .= '<tr style="background-color : #eeeeee; border-style : dotted;">'."\n";
		$outputRefund .= '<td class="main">' . MODULE_PAYMENT_BRAINTREE_ENTRY_REFUND_TITLE . '<br />'. "\n";
		$outputRefund .= zen_draw_form('pprefund', FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=doRefund', 'post', '', true) . zen_hide_session_id();
		$outputRefund .= MODULE_PAYMENT_BRAINTREE_ENTRY_REFUND_PAYFLOW_TEXT;

			// full refund
			$outputRefund .= MODULE_PAYMENT_BRAINTREE_ENTRY_REFUND_FULL;
			$outputRefund .= '<br /><input type="submit" name="fullrefund" value="' . MODULE_PAYMENT_BRAINTREE_ENTRY_REFUND_BUTTON_TEXT_FULL . '" title="' . MODULE_PAYMENT_BRAINTREE_ENTRY_REFUND_BUTTON_TEXT_FULL . '" />' . ' ' . MODULE_PAYMENT_BRAINTREE_TEXT_REFUND_FULL_CONFIRM_CHECK . zen_draw_checkbox_field('reffullconfirm', '', false) . '<br />';
			$outputRefund .= MODULE_PAYMENT_BRAINTREE_ENTRY_REFUND_TEXT_FULL_OR;

		 	if($settled_result && ($settled_result->status == "settled" || $settled_result->status == "settling")) {
		//partial refund - input field
		$outputRefund .= MODULE_PAYMENT_BRAINTREE_ENTRY_REFUND_PARTIAL_TEXT . ' ' . zen_draw_input_field('refamt', 'enter amount', 'length="8"');
		$outputRefund .= '<input type="submit" name="partialrefund" value="' . MODULE_PAYMENT_BRAINTREE_ENTRY_REFUND_BUTTON_TEXT_PARTIAL . '" title="' . MODULE_PAYMENT_BRAINTREE_ENTRY_REFUND_BUTTON_TEXT_PARTIAL . '" /><br />';
			}

		//comment field
		$outputRefund .= '<br />' . MODULE_PAYMENT_BRAINTREE_ENTRY_REFUND_TEXT_COMMENTS . '<br />' . zen_draw_textarea_field('refnote', 'soft', '50', '3', MODULE_PAYMENT_BRAINTREE_ENTRY_REFUND_DEFAULT_MESSAGE);
		//message text
		$outputRefund .= '<br />' . MODULE_PAYMENT_BRAINTREE_ENTRY_REFUND_SUFFIX;
		$outputRefund .= '</form>';
		$outputRefund .='</td></tr></table></td>'."\n";

		} catch(Exception $e) {
			// Error is already reported so we don't need to report it again
		}

	}

	// prepare output based on suitable content components
	$output = '<!-- BOF: pp admin transaction processing tools -->';
	$output .= $outputStartBlock;

//debug
//$output .= '<pre>' . print_r($response, true) . '</pre>';

	$output .= $outputBraintree;

	if (defined('MODULE_PAYMENT_BRAINTREE_STATUS')) {
		$output .= $outputEndBlock;
		$output .= '</tr><tr>' . "\n";
		$output .= $outputStartBlock;
		$output .= $outputStartBlock;
		if (method_exists($this, '_doRefund')) $output .= $outputRefund;
	}

	$output .= $outputEndBlock;
	$output .= $outputEndBlock;
	$output .= '<!-- EOF: pp admin transaction processing tools -->';
