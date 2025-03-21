<?php

global $gApiHelp;

$gApiHelp['Orders'] = array( 
	'Order Properties' => array(
		'help' => 'To place an order, include a checkout block. This will complete the order for all items in the cart.',
		'parameters' => array(
			'checkout[cart]' => 'List of cart items in the following format:<p><tt>{produt_id1}@{quantity}:{option_id1},{option_id2}...{option_idX};{produt_id2}@{quantity}:{option_id1},{option_id4}...{option_idY};{produt_idX}@{quantity}:{option_id1},{option_id5}...{option_idY}</tt></p><p>Example: <tt>335140@2:361;330464@1:101,81</tt></p>',
			'checkout[delivery_name]' => 'Full name of recipient',
			'checkout[delivery_company]' => 'Company name of recipient (optional)',
			'checkout[delivery_street_address]' => 'Primary Street Address of recipient',
			'checkout[delivery_suburb]' => 'Secondary street address such as Suite or Apt # of recipient (optional)',
			'checkout[delivery_city]' => 'City of recipient',
			'checkout[delivery_postcode]' => 'Postal or Zip code of recipient',
			'checkout[delivery_state]' => 'State or provence of recipient',
			'checkout[delivery_country]' => 'Country if recipient, ISO 2 character code, e.g. US or CA',
			'checkout[delivery_telephone]' => 'Telephone number of recipient (optional)',
			'checkout[billing_name]' => ' Full Name of billing contact, such as name on credit card',
			'checkout[billing_street_address]' => 'Primary billing street address of billing contact',
			'checkout[billing_suburb]' => 'Secondary street address of billing contact (optional)',
			'checkout[billing_city]' => 'City of billing contact',
			'checkout[billing_postcode]' => 'Postal or ZIP code of billing contact',
			'checkout[billing_state]' => 'Billing state or provence of billing contact',
			'checkout[billing_country]' => 'Country of billing contact, ISO 2 character code, e.g. US or CA',
			'checkout[billing_telephone]' => 'Telephone number of billing contact',
			'checkout[shipping_module_code]' => 'Shipper method code',
			'checkout[shipping_method_code]' => 'Shipping method code',
			'checkout[dc_redeem_code]' => 'Coupon or discount code (optional)',
			'checkout[payment_owner]' => 'Name on credit card',
			'checkout[payment_number]' => 'Credit Card number',
			'checkout[payment_expires]' => 'Expiration date in the form MMYY',
			'checkout[payment_cvv]' => 'Card security code (3 or 4 digits)',
			'checkout[deadline_date]' => 'If the order is needed by a certain date, enter it as YYYY-MM-DD, eg 2023-04-01',
			'checkout[comments]' => 'Any comment you want included with the order. This will be reviewed by production prior to processing the order',
		),
		'code' => "<h3>Order Checkout Example</h3>

Sample POST fields in the format field[name] => fieldValue. The field name should inclue the brackets, and look exactly as shown below.

<code>checkout[cart] => '335140@2:361;330464@1:101,81',
checkout[delivery_name] => 'Wile E Coyote',
checkout[delivery_company] => 'Super Genius, LTD',
checkout[delivery_street_address1] => '113 W Vance St',
checkout[delivery_street_address2] => 'Suite 1',
checkout[delivery_city] => 'Zebulon',
checkout[delivery_postcode] => '27597',
checkout[delivery_state] => 'North Carolina',
checkout[delivery_country_iso2] => 'US',
checkout[delivery_telephone] => '+1 555-123-4567',
checkout[billing_name] => 'Road R Runner',
checkout[billing_street_address1] => '123 Rte 66',
checkout[billing_city] => 'Meep Meep',
checkout[billing_postcode] => '87654-3210',
checkout[billing_state] => 'Arizona',
checkout[billing_country_iso2] => 'US',
checkout[billing_telephone] => '+1 555-987-6543',
checkout[shipping_quote] => 'fedexwebservices_FEDEXGROUND',
checkout[payment_method] => 'purchase_order',
checkout[payment_po_number] => '1912-2002',
checkout[payment_po_contact] => 'Chuck Jones',
checkout[payment_po_org] => 'Acme Corporation',
checkout[dc_redeem_code] => 'SAVE10',
checkout[ot_expedite] => '1',
checkout[deadline_date] => '2024-08-18',
checkout[comments] => 'This is a test order.'
</code>


		"
	),
);
