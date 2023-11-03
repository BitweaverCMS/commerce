<?php

global $gApiHelp;

$gApiHelp['Orders'] = array( 
	'Place Order Properties' => array(
		'help' => 'To place and order, include delivery address. This will complete the order for all items in the cart.',
		'parameters' => array(

'delivery_name' => 'Full name of recipient',
'delivery_company' => 'Company name of recipient (optional)',
'delivery_street_address' => 'Primary Street Address of recipient',
'delivery_suburb' => 'Secondary street address such as Suite or Apt # of recipient (optional)',
'delivery_city' => 'City of recipient',
'delivery_postcode' => 'Postal or Zip code of recipient',
'delivery_state' => 'State or provence of recipient',
'delivery_country' => 'Country if recipient, ISO 2 character code, e.g. US or CA',
'delivery_telephone' => 'Telephone number of recipient (optional)',
'billing_name' => ' Full Name of billing contact, such as name on credit card',
'billing_street_address' => 'Primary billing street address of billing contact',
'billing_suburb' => 'Secondary street address of billing contact (optional)',
'billing_city' => 'City of billing contact',
'billing_postcode' => 'Postal or ZIP code of billing contact',
'billing_state' => 'Billing state or provence of billing contact',
'billing_country' => 'Country of billing contact, ISO 2 character code, e.g. US or CA',
'billing_telephone' => 'Telephone number of billing contact',
'shipping_method' => 'Shipping method code',
'shipping_method_code' => '',
'coupon_code' => 'Coupon or discount code (optional)',
'payment_owner' => 'Name on credit card',
'payment_number' => 'Credit Card number',
'payment_expires' => 'Expiration date in the form MMYY',
'cc_cvv' => 'Card security code (3 or 4 digits)',
'deadline_date' => 'If the order is needed by a certain date, enter it as YYYY-MM-DD, eg 2023-04-01',
		),
	),
);
