<?php
require_once( '../../../../../../../../../kernel/setup_inc.php' );
require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'bitcommerce_start_inc.php' );
 
global $gCommerceSystem;
 
/************************************************************************
* REQUIRED
* 
* Access Key ID and Secret Acess Key ID, obtained from:
* http://aws.amazon.com
***********************************************************************/
define('AWS_ACCESS_KEY_ID', $gCommerceSystem->getConfig('MODULE_FULFILLMENT_AMAZONMWS_AWS_ACCESS_KEY_ID') ); // '<Your Access Key ID>');
define('AWS_SECRET_ACCESS_KEY', $gCommerceSystem->getConfig('MODULE_FULFILLMENT_AMAZONMWS_SECRET_KEY' ) );  

/************************************************************************
* REQUIRED
* 
* All MWS requests must contain a User-Agent header. The application
* name and version defined below are used in creating this value.
***********************************************************************/
define('APPLICATION_NAME', 'bitcommerce');
define('APPLICATION_VERSION', '3.0.0');

/************************************************************************
* REQUIRED
* 
* All MWS requests must contain the seller's merchant ID and
* marketplace ID.
***********************************************************************/
define ('MERCHANT_ID', $gCommerceSystem->getConfig('MODULE_FULFILLMENT_AMAZONMWS_MERCHANT_ID') );
define ('MARKETPLACE_ID', $gCommerceSystem->getConfig('MODULE_FULFILLMENT_AMAZONMWS_MARKETPLACE_ID') );


/************************************************************************ 
* OPTIONAL ON SOME INSTALLATIONS
*
* Set include path to root of library, relative to Samples directory.
* Only needed when running library from local directory.
* If library is installed in PHP include path, this is not needed
***********************************************************************/   
set_include_path(get_include_path() . PATH_SEPARATOR . '../../.');    

/************************************************************************ 
* OPTIONAL ON SOME INSTALLATIONS  
* 
* Autoload function is reponsible for loading classes of the library on demand
* 
* NOTE: Only one __autoload function is allowed by PHP per each PHP installation,
* and this function may need to be replaced with individual require_once statements
* in case where other framework that define an __autoload already loaded.
* 
* However, since this library follow common naming convention for PHP classes it
* may be possible to simply re-use an autoload mechanism defined by other frameworks
* (provided library is installed in the PHP include path), and so classes may just 
* be loaded even when this function is removed
***********************************************************************/   
 function __autoload($className){
	$filePath = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
	$includePaths = explode(PATH_SEPARATOR, get_include_path());
	foreach($includePaths as $includePath){
		if(file_exists($includePath . DIRECTORY_SEPARATOR . $filePath)){
			require_once $filePath;
			return;
		}
	}
}


/************************************************************************
 * Instantiate Implementation of MarketplaceWebServiceOrders
 * 
 * AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY constants 
 * are defined in the .config.inc.php located in the same 
 * directory as this sample
 ***********************************************************************/
// United States:
$serviceUrl = "https://mws.amazonservices.com/Orders/2011-01-01";
// United Kingdom
//$serviceUrl = "https://mws.amazonservices.co.uk/Orders/2011-01-01";
// Germany
//$serviceUrl = "https://mws.amazonservices.de/Orders/2011-01-01";
// France
//$serviceUrl = "https://mws.amazonservices.fr/Orders/2011-01-01";
// Japan
//$serviceUrl = "https://mws.amazonservices.jp/Orders/2011-01-01";
// China
//$serviceUrl = "https://mws.amazonservices.com.cn/Orders/2011-01-01";
// Canada
//$serviceUrl = "https://mws.amazonservices.ca/Orders/2011-01-01";


