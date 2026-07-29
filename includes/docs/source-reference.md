# Commerce source reference

> Generated from the current checkout and then intended for human review.
> Paths are relative to the package root.

## Inventory summary

| Artifact | Count |
|---|---:|
| PHP files | 895 |
| Smarty templates | 137 |
| JavaScript files | 71 |
| CSS files | 12 |

## Bootstrap and schema artifacts

- `admin/schema_inc.php`
- `includes/bit_setup_inc.php`

## First-party classes and interfaces

- `admin/includes/classes/box.php:37` — `  class box extends tableBlock {`
- `admin/includes/classes/language.php:23` — `  class language {`
- `admin/includes/classes/logger.php:24` — `  class logger {`
- `admin/includes/classes/message_stack.php:32` — `  class messageStack {`
- `admin/includes/classes/mime.php:31` — `  class mime {`
- `admin/includes/classes/object_info.php:23` — `  class objectInfo {`
- `admin/includes/classes/payment_module_info.php:24` — `  class paymentModuleInfo {`
- `admin/includes/classes/phplot.php:26` — `class PHPlot{`
- `admin/includes/classes/split_page_results.php:23` — `  class splitPageResults {`
- `admin/includes/classes/table_block.php:23` — `  class tableBlock {`
- `admin/includes/classes/upload.php:23` — `  class upload {`
- `admin/includes/fckeditor.php:26` — `class FCKeditor`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Client.php:30` — `class MarketplaceWebServiceOrders_Client implements MarketplaceWebServiceOrders_Interface`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Exception.php:25` — `class MarketplaceWebServiceOrders_Exception extends Exception`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Interface.php:23` — `interface  MarketplaceWebServiceOrders_Interface `
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Mock.php:28` — `class  MarketplaceWebServiceOrders_Mock implements MarketplaceWebServiceOrders_Interface`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model.php:22` — `abstract class MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/Address.php:46` — `class MarketplaceWebServiceOrders_Model_Address extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/Error.php:39` — `class MarketplaceWebServiceOrders_Model_Error extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/ErrorResponse.php:37` — `class MarketplaceWebServiceOrders_Model_ErrorResponse extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/FulfillmentChannelList.php:35` — `class MarketplaceWebServiceOrders_Model_FulfillmentChannelList extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/GetOrderRequest.php:37` — `class MarketplaceWebServiceOrders_Model_GetOrderRequest extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/GetOrderResponse.php:37` — `class MarketplaceWebServiceOrders_Model_GetOrderResponse extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/GetOrderResult.php:36` — `class MarketplaceWebServiceOrders_Model_GetOrderResult extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/GetServiceStatusRequest.php:36` — `class MarketplaceWebServiceOrders_Model_GetServiceStatusRequest extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/GetServiceStatusResponse.php:37` — `class MarketplaceWebServiceOrders_Model_GetServiceStatusResponse extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/GetServiceStatusResult.php:38` — `class MarketplaceWebServiceOrders_Model_GetServiceStatusResult extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/ListOrderItemsByNextTokenRequest.php:37` — `class MarketplaceWebServiceOrders_Model_ListOrderItemsByNextTokenRequest extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/ListOrderItemsByNextTokenResponse.php:37` — `class MarketplaceWebServiceOrders_Model_ListOrderItemsByNextTokenResponse extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/ListOrderItemsByNextTokenResult.php:38` — `class MarketplaceWebServiceOrders_Model_ListOrderItemsByNextTokenResult extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/ListOrderItemsRequest.php:37` — `class MarketplaceWebServiceOrders_Model_ListOrderItemsRequest extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/ListOrderItemsResponse.php:37` — `class MarketplaceWebServiceOrders_Model_ListOrderItemsResponse extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/ListOrderItemsResult.php:38` — `class MarketplaceWebServiceOrders_Model_ListOrderItemsResult extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/ListOrdersByNextTokenRequest.php:37` — `class MarketplaceWebServiceOrders_Model_ListOrdersByNextTokenRequest extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/ListOrdersByNextTokenResponse.php:37` — `class MarketplaceWebServiceOrders_Model_ListOrdersByNextTokenResponse extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/ListOrdersByNextTokenResult.php:39` — `class MarketplaceWebServiceOrders_Model_ListOrdersByNextTokenResult extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/ListOrdersRequest.php:46` — `class MarketplaceWebServiceOrders_Model_ListOrdersRequest extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/ListOrdersResponse.php:37` — `class MarketplaceWebServiceOrders_Model_ListOrdersResponse extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/ListOrdersResult.php:39` — `class MarketplaceWebServiceOrders_Model_ListOrdersResult extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/MarketplaceIdList.php:36` — `class MarketplaceWebServiceOrders_Model_MarketplaceIdList extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/MaxResults.php:36` — `class MarketplaceWebServiceOrders_Model_MaxResults extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/Message.php:37` — `class MarketplaceWebServiceOrders_Model_Message extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/MessageList.php:36` — `class MarketplaceWebServiceOrders_Model_MessageList extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/Money.php:37` — `class MarketplaceWebServiceOrders_Model_Money extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/Order.php:47` — `class MarketplaceWebServiceOrders_Model_Order extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/OrderIdList.php:36` — `class MarketplaceWebServiceOrders_Model_OrderIdList extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/OrderItem.php:50` — `class MarketplaceWebServiceOrders_Model_OrderItem extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/OrderItemList.php:36` — `class MarketplaceWebServiceOrders_Model_OrderItemList extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/OrderList.php:36` — `class MarketplaceWebServiceOrders_Model_OrderList extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/OrderStatusList.php:36` — `class MarketplaceWebServiceOrders_Model_OrderStatusList extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/PromotionIdList.php:35` — `class MarketplaceWebServiceOrders_Model_PromotionIdList extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/amazonmws/src/MarketplaceWebServiceOrders/Model/ResponseMetadata.php:36` — `class MarketplaceWebServiceOrders_Model_ResponseMetadata extends MarketplaceWebServiceOrders_Model`
- `admin/includes/modules/newsletters/newsletter.php:23` — `  class newsletter {`
- `admin/includes/modules/newsletters/product_notification.php:23` — `  class product_notification {`
- `admin/schema_inc.php:891` — `  class C(32),`
- `includes/classes/CommerceBase.php:12` — `abstract class CommerceBase extends BitBase {`
- `includes/classes/CommerceCategory.php:18` — `class CommerceCategory extends BitBase {`
- `includes/classes/CommerceCommission.php:20` — `class CommerceProductCommission extends CommerceCommissionBase {`
- `includes/classes/CommerceCommissionBase.php:17` — `class CommerceCommissionBase extends BitSingleton {`
- `includes/classes/CommerceCustomer.php:16` — `class CommerceCustomer extends CommerceBase {`
- `includes/classes/CommerceOrder.php:26` — `class order extends CommerceOrder {`
- `includes/classes/CommerceOrder.php:38` — `class CommerceOrder extends CommerceOrderBase {`
- `includes/classes/CommerceOrderBase.php:12` — `abstract class CommerceOrderBase extends BitBase {`
- `includes/classes/CommerceOrderManager.php:17` — `class CommerceOrderManager extends BitSingleton {`
- `includes/classes/CommercePaymentManager.php:14` — `class CommercePaymentManager extends BitBase {`
- `includes/classes/CommercePluginBase.php:14` — `abstract class CommercePluginBase extends CommerceBase {`
- `includes/classes/CommercePluginFulfillmentBase.php:14` — `abstract class CommercePluginFulfillmentBase extends CommercePluginBase {`
- `includes/classes/CommercePluginOrderTotalBase.php:14` — `abstract class CommercePluginOrderTotalBase extends CommercePluginBase {`
- `includes/classes/CommercePluginPaymentBase.php:13` — `abstract class CommercePluginPaymentBase extends CommercePluginBase {`
- `includes/classes/CommercePluginPaymentCardBase.php:13` — `abstract class CommercePluginPaymentCardBase extends CommercePluginPaymentBase {`
- `includes/classes/CommercePluginShippingBase.php:14` — `abstract class CommercePluginShippingBase extends CommercePluginBase {`
- `includes/classes/CommercePluginShippingRateTableBase.php:13` — `class CommercePluginShippingRateTableBase extends CommercePluginShippingBase {`
- `includes/classes/CommerceProduct.php:29` — `class CommerceProduct extends LibertyMime {`
- `includes/classes/CommerceProductManager.php:16` — `class CommerceProductManager extends BitBase {`
- `includes/classes/CommerceReview.php:15` — `class CommerceReview extends CommerceBase {`
- `includes/classes/CommerceShipping.php:23` — `class CommerceShipping extends BitSingleton {`
- `includes/classes/CommerceShoppingCart.php:17` — `class CommerceShoppingCart extends CommerceOrderBase {`
- `includes/classes/CommerceStatistics.php:16` — `class CommerceStatistics extends BitSingleton {`
- `includes/classes/CommerceSystem.php:15` — `class CommerceSystem extends BitSingleton {`
- `includes/classes/CommerceTemporaryCart.php:15` — `class CommerceTemporaryCart extends CommerceShoppingCart {`
- `includes/classes/CommerceVoucher.php:15` — `class CommerceVoucher extends CommerceBase {`
- `includes/classes/boxes.php:111` — `  class infoBoxHeading extends tableBox {`
- `includes/classes/boxes.php:151` — `  class contentBox extends tableBox {`
- `includes/classes/boxes.php:167` — `  class contentBoxHeading extends tableBox {`
- `includes/classes/boxes.php:184` — `  class errorBox extends tableBox {`
- `includes/classes/boxes.php:191` — `  class productListingBox extends tableBox {`
- `includes/classes/boxes.php:23` — `  class tableBox {`
- `includes/classes/boxes.php:85` — `  class infoBox extends tableBox {`
- `includes/classes/breadcrumb.php:23` — `class breadcrumb {`
- `includes/classes/cache.php:23` — `class cache {`
- `includes/classes/category_tree.php:23` — `class category_tree {`
- `includes/classes/currencies.php:29` — `class currencies extends BitBase {`
- `includes/classes/email.php:31` — `  class email {`
- `includes/classes/http_client.php:26` — `  class httpClient {`
- `includes/classes/language.php:23` — `  class language {`
- `includes/classes/message_stack.php:23` — `  class messageStack extends tableBox {`
- `includes/classes/mime.php:28` — `  class mime {`
- `includes/classes/navigation_history.php:23` — `  class navigationHistory {`
- `includes/classes/products.php:23` — `  class products {`
- `includes/classes/sniffer.php:27` — `  class sniffer {`
- `includes/classes/split_page_results.php:23` — `  class splitPageResults {`
- `includes/classes/upload.php:24` — `  class upload {`
- `includes/classes/xmldocument.php:215` — `class XMLParser `
- `includes/classes/xmldocument.php:29` — `class XMLDocument {`
- `includes/classes/xmldocument.php:75` — `class Node `
- `includes/modules/fulfillment/demo/demo.php:9` — `class demo extends CommercePluginFulfillmentBase { `
- `includes/modules/order_total/ot_cod_fee.php:13` — `class ot_cod_fee extends CommercePluginOrderTotalBase {`
- `includes/modules/order_total/ot_coupon.php:14` — `class ot_coupon extends CommercePluginOrderTotalBase  {`
- `includes/modules/order_total/ot_expedite.php:13` — `class ot_expedite extends CommercePluginOrderTotalBase {`
- `includes/modules/order_total/ot_group_pricing.php:13` — `class ot_group_pricing extends CommercePluginOrderTotalBase {`
- `includes/modules/order_total/ot_gv.php:13` — `class ot_gv extends CommercePluginOrderTotalBase {`
- `includes/modules/order_total/ot_loworderfee.php:13` — `class ot_loworderfee extends CommercePluginOrderTotalBase {`
- `includes/modules/order_total/ot_shipping.php:13` — `class ot_shipping extends CommercePluginOrderTotalBase {`
- `includes/modules/order_total/ot_subtotal.php:13` — `class ot_subtotal extends CommercePluginOrderTotalBase {`
- `includes/modules/order_total/ot_tax.php:13` — `class ot_tax extends CommercePluginOrderTotalBase {`
- `includes/modules/order_total/ot_total.php:13` — `class ot_total extends CommercePluginOrderTotalBase {`
- `includes/modules/payment/amazonmws.php:14` — `class amazonmws extends CommercePluginPaymentBase { `
- `includes/modules/payment/authorizenet.php:25` — `class authorizenet extends CommercePluginPaymentCardBase {`
- `includes/modules/payment/authorizenet_aim.php:29` — `class authorizenet_aim extends CommercePluginPaymentCardBase {`
- `includes/modules/payment/braintree_api/braintree_api.php:16` — `class braintree_api extends CommercePluginPaymentCardBase {`
- `includes/modules/payment/braintree_api/lib/Braintree.php:13` — `class Braintree {`
- `includes/modules/payment/braintree_api/lib/Braintree/AccountUpdaterDailyReport.php:13` — `class AccountUpdaterDailyReport extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/AchMandate.php:13` — `class AchMandate extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/AddOn.php:4` — `class AddOn extends Modification`
- `includes/modules/payment/braintree_api/lib/Braintree/AddOnGateway.php:4` — `class AddOnGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/Address.php:29` — `class Address extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/AddressGateway.php:17` — `class AddressGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/AmexExpressCheckoutCard.php:29` — `class AmexExpressCheckoutCard extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/AndroidPayCard.php:33` — `class AndroidPayCard extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/ApplePayCard.php:29` — `class ApplePayCard extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/ApplePayGateway.php:11` — `class ApplePayGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/ApplePayOptions.php:14` — `class ApplePayOptions extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/AuthorizationAdjustment.php:15` — `class AuthorizationAdjustment extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/Base.php:14` — `abstract class Base implements JsonSerializable`
- `includes/modules/payment/braintree_api/lib/Braintree/BinData.php:4` — `class BinData extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/ClientToken.php:4` — `class ClientToken`
- `includes/modules/payment/braintree_api/lib/Braintree/ClientTokenGateway.php:6` — `class ClientTokenGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/CoinbaseAccount.php:26` — `class CoinbaseAccount extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/Collection.php:22` — `class Collection implements Countable, IteratorAggregate, ArrayAccess`
- `includes/modules/payment/braintree_api/lib/Braintree/Configuration.php:12` — `class Configuration`
- `includes/modules/payment/braintree_api/lib/Braintree/ConnectedMerchantPayPalStatusChanged.php:13` — `class ConnectedMerchantPayPalStatusChanged extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/ConnectedMerchantStatusTransitioned.php:13` — `class ConnectedMerchantStatusTransitioned extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/CredentialsParser.php:12` — `class CredentialsParser`
- `includes/modules/payment/braintree_api/lib/Braintree/CreditCard.php:31` — `class CreditCard extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/CreditCardGateway.php:18` — `class CreditCardGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/CreditCardVerification.php:4` — `class CreditCardVerification extends Result\CreditCardVerification`
- `includes/modules/payment/braintree_api/lib/Braintree/CreditCardVerificationGateway.php:4` — `class CreditCardVerificationGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/CreditCardVerificationSearch.php:4` — `class CreditCardVerificationSearch`
- `includes/modules/payment/braintree_api/lib/Braintree/Customer.php:38` — `class Customer extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/CustomerGateway.php:17` — `class CustomerGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/CustomerSearch.php:4` — `class CustomerSearch`
- `includes/modules/payment/braintree_api/lib/Braintree/Descriptor.php:4` — `class Descriptor extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Digest.php:8` — `class Digest`
- `includes/modules/payment/braintree_api/lib/Braintree/Disbursement.php:4` — `class Disbursement extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/DisbursementDetails.php:18` — `class DisbursementDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Discount.php:4` — `class Discount extends Modification`
- `includes/modules/payment/braintree_api/lib/Braintree/DiscountGateway.php:4` — `class DiscountGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/Dispute.php:18` — `class Dispute extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/Dispute/EvidenceDetails.php:20` — `class EvidenceDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Dispute/StatusHistoryDetails.php:16` — `class StatusHistoryDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Dispute/TransactionDetails.php:21` — `class TransactionDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/DisputeGateway.php:13` — `class DisputeGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/DisputeSearch.php:4` — `class DisputeSearch`
- `includes/modules/payment/braintree_api/lib/Braintree/DocumentUpload.php:17` — `class DocumentUpload extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/DocumentUploadGateway.php:13` — `class DocumentUploadGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/EndsWithNode.php:4` — `class EndsWithNode`
- `includes/modules/payment/braintree_api/lib/Braintree/EqualityNode.php:4` — `class EqualityNode extends IsNode`
- `includes/modules/payment/braintree_api/lib/Braintree/Error/Codes.php:18` — `class Codes`
- `includes/modules/payment/braintree_api/lib/Braintree/Error/ErrorCollection.php:19` — `class ErrorCollection implements \Countable`
- `includes/modules/payment/braintree_api/lib/Braintree/Error/Validation.php:21` — `class Validation`
- `includes/modules/payment/braintree_api/lib/Braintree/Error/ValidationErrorCollection.php:19` — `class ValidationErrorCollection extends Collection`
- `includes/modules/payment/braintree_api/lib/Braintree/EuropeBankAccount.php:24` — `class EuropeBankAccount extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/Exception.php:10` — `class Exception extends \Exception`
- `includes/modules/payment/braintree_api/lib/Braintree/Exception/Authentication.php:13` — `class Authentication extends Exception`
- `includes/modules/payment/braintree_api/lib/Braintree/Exception/Authorization.php:15` — `class Authorization extends Exception`
- `includes/modules/payment/braintree_api/lib/Braintree/Exception/Configuration.php:13` — `class Configuration extends Exception`
- `includes/modules/payment/braintree_api/lib/Braintree/Exception/Connection.php:13` — `class Connection extends Exception`
- `includes/modules/payment/braintree_api/lib/Braintree/Exception/DownForMaintenance.php:12` — `class DownForMaintenance extends Exception`
- `includes/modules/payment/braintree_api/lib/Braintree/Exception/ForgedQueryString.php:16` — `class ForgedQueryString extends Exception`
- `includes/modules/payment/braintree_api/lib/Braintree/Exception/InvalidChallenge.php:6` — `class InvalidChallenge extends Exception`
- `includes/modules/payment/braintree_api/lib/Braintree/Exception/InvalidSignature.php:6` — `class InvalidSignature extends Exception`
- `includes/modules/payment/braintree_api/lib/Braintree/Exception/NotFound.php:12` — `class NotFound extends Exception`
- `includes/modules/payment/braintree_api/lib/Braintree/Exception/SSLCaFileNotFound.php:12` — `class SSLCaFileNotFound extends Exception`
- `includes/modules/payment/braintree_api/lib/Braintree/Exception/SSLCertificate.php:12` — `class SSLCertificate extends Exception`
- `includes/modules/payment/braintree_api/lib/Braintree/Exception/ServerError.php:12` — `class ServerError extends Exception`
- `includes/modules/payment/braintree_api/lib/Braintree/Exception/TestOperationPerformedInProduction.php:12` — `class TestOperationPerformedInProduction extends Exception`
- `includes/modules/payment/braintree_api/lib/Braintree/Exception/Timeout.php:12` — `class Timeout extends Exception`
- `includes/modules/payment/braintree_api/lib/Braintree/Exception/TooManyRequests.php:12` — `class TooManyRequests extends Exception`
- `includes/modules/payment/braintree_api/lib/Braintree/Exception/Unexpected.php:13` — `class Unexpected extends Exception`
- `includes/modules/payment/braintree_api/lib/Braintree/Exception/UpgradeRequired.php:12` — `class UpgradeRequired extends Exception`
- `includes/modules/payment/braintree_api/lib/Braintree/Exception/ValidationsFailed.php:12` — `class ValidationsFailed extends Exception`
- `includes/modules/payment/braintree_api/lib/Braintree/FacilitatedDetails.php:4` — `class FacilitatedDetails extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/FacilitatorDetails.php:4` — `class FacilitatorDetails extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/Gateway.php:10` — `class Gateway`
- `includes/modules/payment/braintree_api/lib/Braintree/GrantedPaymentInstrumentUpdate.php:26` — `class GrantedPaymentInstrumentUpdate extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/Http.php:10` — `class Http`
- `includes/modules/payment/braintree_api/lib/Braintree/IbanBankAccount.php:16` — `class IbanBankAccount extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/IdealPayment.php:29` — `class IdealPayment extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/IdealPaymentGateway.php:22` — `class IdealPaymentGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/Instance.php:9` — `abstract class Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/IsNode.php:4` — `class IsNode`
- `includes/modules/payment/braintree_api/lib/Braintree/KeyValueNode.php:4` — `class KeyValueNode`
- `includes/modules/payment/braintree_api/lib/Braintree/MasterpassCard.php:44` — `class MasterpassCard extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/Merchant.php:4` — `class Merchant extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/MerchantAccount.php:4` — `class MerchantAccount extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/MerchantAccount/AddressDetails.php:6` — `class AddressDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/MerchantAccount/BusinessDetails.php:6` — `class BusinessDetails extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/MerchantAccount/FundingDetails.php:6` — `class FundingDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/MerchantAccount/IndividualDetails.php:6` — `class IndividualDetails extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/MerchantAccountGateway.php:4` — `class MerchantAccountGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/MerchantGateway.php:4` — `class MerchantGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/Modification.php:4` — `class Modification extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/MultipleValueNode.php:6` — `class MultipleValueNode`
- `includes/modules/payment/braintree_api/lib/Braintree/MultipleValueOrTextNode.php:4` — `class MultipleValueOrTextNode extends MultipleValueNode`
- `includes/modules/payment/braintree_api/lib/Braintree/OAuthAccessRevocation.php:11` — `class OAuthAccessRevocation extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/OAuthCredentials.php:10` — `class OAuthCredentials extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/OAuthGateway.php:11` — `class OAuthGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/OAuthResult.php:10` — `class OAuthResult extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/PaginatedCollection.php:24` — `class PaginatedCollection implements Iterator`
- `includes/modules/payment/braintree_api/lib/Braintree/PaginatedResult.php:4` — `class PaginatedResult`
- `includes/modules/payment/braintree_api/lib/Braintree/PartialMatchNode.php:4` — `class PartialMatchNode extends EqualityNode`
- `includes/modules/payment/braintree_api/lib/Braintree/PartnerMerchant.php:18` — `class PartnerMerchant extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/PayPalAccount.php:25` — `class PayPalAccount extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/PayPalAccountGateway.php:22` — `class PayPalAccountGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/PaymentInstrumentType.php:4` — `class PaymentInstrumentType`
- `includes/modules/payment/braintree_api/lib/Braintree/PaymentMethod.php:20` — `class PaymentMethod extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/PaymentMethodGateway.php:22` — `class PaymentMethodGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/PaymentMethodNonce.php:20` — `class PaymentMethodNonce extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/PaymentMethodNonceGateway.php:20` — `class PaymentMethodNonceGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/Plan.php:4` — `class Plan extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/PlanGateway.php:4` — `class PlanGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/RangeNode.php:4` — `class RangeNode`
- `includes/modules/payment/braintree_api/lib/Braintree/ResourceCollection.php:24` — `class ResourceCollection implements Iterator`
- `includes/modules/payment/braintree_api/lib/Braintree/Result/CreditCardVerification.php:24` — `class CreditCardVerification`
- `includes/modules/payment/braintree_api/lib/Braintree/Result/Error.php:36` — `class Error extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/Result/Successful.php:30` — `class Successful extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Result/UsBankAccountVerification.php:25` — `class UsBankAccountVerification`
- `includes/modules/payment/braintree_api/lib/Braintree/RiskData.php:4` — `class RiskData extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/SettlementBatchSummary.php:4` — `class SettlementBatchSummary extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/SettlementBatchSummaryGateway.php:4` — `class SettlementBatchSummaryGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/SignatureService.php:4` — `class SignatureService`
- `includes/modules/payment/braintree_api/lib/Braintree/Subscription.php:15` — `class Subscription extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/Subscription/StatusDetails.php:21` — `class StatusDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/SubscriptionGateway.php:17` — `class SubscriptionGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/SubscriptionSearch.php:4` — `class SubscriptionSearch`
- `includes/modules/payment/braintree_api/lib/Braintree/Test/CreditCardNumbers.php:14` — `class CreditCardNumbers`
- `includes/modules/payment/braintree_api/lib/Braintree/Test/MerchantAccount.php:10` — `class MerchantAccount`
- `includes/modules/payment/braintree_api/lib/Braintree/Test/Nonces.php:20` — `class Nonces`
- `includes/modules/payment/braintree_api/lib/Braintree/Test/Transaction.php:12` — `class Transaction`
- `includes/modules/payment/braintree_api/lib/Braintree/Test/TransactionAmounts.php:13` — `class TransactionAmounts`
- `includes/modules/payment/braintree_api/lib/Braintree/Test/VenmoSdk.php:10` — `class VenmoSdk`
- `includes/modules/payment/braintree_api/lib/Braintree/TestingGateway.php:4` — `class TestingGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/TextNode.php:4` — `class TextNode extends PartialMatchNode`
- `includes/modules/payment/braintree_api/lib/Braintree/ThreeDSecureInfo.php:4` — `class ThreeDSecureInfo extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/Transaction.php:177` — `class Transaction extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/Transaction/AddressDetails.php:23` — `class AddressDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Transaction/AmexExpressCheckoutCardDetails.php:31` — `class AmexExpressCheckoutCardDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Transaction/AndroidPayCardDetails.php:33` — `class AndroidPayCardDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Transaction/ApplePayCardDetails.php:27` — `class ApplePayCardDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Transaction/CoinbaseDetails.php:26` — `class CoinbaseDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Transaction/CreditCardDetails.php:24` — `class CreditCardDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Transaction/CustomerDetails.php:22` — `class CustomerDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Transaction/EuropeBankAccountDetails.php:21` — `class EuropeBankAccountDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Transaction/IdealPaymentDetails.php:19` — `class IdealPaymentDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Transaction/MasterpassCardDetails.php:36` — `class MasterpassCardDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Transaction/PayPalDetails.php:29` — `class PayPalDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Transaction/StatusDetails.php:18` — `class StatusDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Transaction/SubscriptionDetails.php:16` — `class SubscriptionDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Transaction/UsBankAccountDetails.php:23` — `class UsBankAccountDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Transaction/VenmoAccountDetails.php:26` — `class VenmoAccountDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/Transaction/VisaCheckoutCardDetails.php:37` — `class VisaCheckoutCardDetails extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/TransactionGateway.php:19` — `class TransactionGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/TransactionLineItem.php:31` — `class TransactionLineItem extends Instance`
- `includes/modules/payment/braintree_api/lib/Braintree/TransactionLineItemGateway.php:14` — `class TransactionLineItemGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/TransactionSearch.php:4` — `class TransactionSearch`
- `includes/modules/payment/braintree_api/lib/Braintree/TransparentRedirect.php:38` — `class TransparentRedirect`
- `includes/modules/payment/braintree_api/lib/Braintree/TransparentRedirectGateway.php:15` — `class TransparentRedirectGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/UnknownPaymentMethod.php:23` — `class UnknownPaymentMethod extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/UsBankAccount.php:33` — `class UsBankAccount extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/UsBankAccountGateway.php:22` — `class UsBankAccountGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/UsBankAccountVerification.php:21` — `class UsBankAccountVerification extends Result\UsBankAccountVerification`
- `includes/modules/payment/braintree_api/lib/Braintree/UsBankAccountVerificationGateway.php:22` — `class UsBankAccountVerificationGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/UsBankAccountVerificationSearch.php:4` — `class UsBankAccountVerificationSearch`
- `includes/modules/payment/braintree_api/lib/Braintree/Util.php:12` — `class Util`
- `includes/modules/payment/braintree_api/lib/Braintree/VenmoAccount.php:25` — `class VenmoAccount extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/Version.php:8` — `class Version`
- `includes/modules/payment/braintree_api/lib/Braintree/VisaCheckoutCard.php:45` — `class VisaCheckoutCard extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/WebhookNotification.php:4` — `class WebhookNotification extends Base`
- `includes/modules/payment/braintree_api/lib/Braintree/WebhookNotificationGateway.php:4` — `class WebhookNotificationGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/WebhookTesting.php:4` — `class WebhookTesting`
- `includes/modules/payment/braintree_api/lib/Braintree/WebhookTestingGateway.php:4` — `class WebhookTestingGateway`
- `includes/modules/payment/braintree_api/lib/Braintree/Xml.php:9` — `class Xml`
- `includes/modules/payment/braintree_api/lib/Braintree/Xml/Generator.php:17` — `class Generator`
- `includes/modules/payment/braintree_api/lib/Braintree/Xml/Parser.php:14` — `class Parser`
- `includes/modules/payment/cc.php:25` — `class cc extends CommercePluginPaymentCardBase {`
- `includes/modules/payment/cod.php:20` — `class cod extends CommercePluginPaymentBase {`
- `includes/modules/payment/moneyorder.php:25` — `class moneyorder extends CommercePluginPaymentBase {`
- `includes/modules/payment/payflowpro.php:16` — `class payflowpro extends CommercePluginPaymentCardBase {`
- `includes/modules/payment/paypal/paypal.php:31` — `class paypal extends CommercePluginPaymentBase {`
- `includes/modules/payment/purchase_order.php:20` — `class purchase_order extends CommercePluginPaymentBase {`
- `includes/modules/shipping/auspost/auspost.php:35` — `class auspost extends CommercePluginShippingBase {`
- `includes/modules/shipping/canadapost/canadapost.php:31` — `class canadapost extends CommercePluginShippingBase {`
- `includes/modules/shipping/fedexrest.php:20` — `class fedexrest extends CommercePluginShippingBase {`
- `includes/modules/shipping/fixed.php:13` — `class fixed extends CommercePluginShippingRateTableBase {`
- `includes/modules/shipping/flat.php:13` — `class flat extends CommercePluginShippingBase {`
- `includes/modules/shipping/freeshipper.php:13` — `class freeshipper extends CommercePluginShippingBase {`
- `includes/modules/shipping/item.php:13` — `class item extends CommercePluginShippingBase {`
- `includes/modules/shipping/purolator/nusoap/class.nusoap_base.php:85` — `class nusoap_base {`
- `includes/modules/shipping/purolator/nusoap/class.soap_fault.php:14` — `class nusoap_fault extends nusoap_base {`
- `includes/modules/shipping/purolator/nusoap/class.soap_fault.php:86` — `class soap_fault extends nusoap_fault {`
- `includes/modules/shipping/purolator/nusoap/class.soap_parser.php:15` — `class nusoap_parser extends nusoap_base {`
- `includes/modules/shipping/purolator/nusoap/class.soap_parser.php:639` — `class soap_parser extends nusoap_parser {`
- `includes/modules/shipping/purolator/nusoap/class.soap_server.php:1123` — `class soap_server extends nusoap_server {`
- `includes/modules/shipping/purolator/nusoap/class.soap_server.php:16` — `class nusoap_server extends nusoap_base {`
- `includes/modules/shipping/purolator/nusoap/class.soap_transport_http.php:15` — `class soap_transport_http extends nusoap_base {`
- `includes/modules/shipping/purolator/nusoap/class.soap_val.php:17` — `class soapval extends nusoap_base {`
- `includes/modules/shipping/purolator/nusoap/class.soapclient.php:26` — `class nusoap_client extends nusoap_base  {`
- `includes/modules/shipping/purolator/nusoap/class.soapclient.php:988` — `	class soapclient extends nusoap_client {`
- `includes/modules/shipping/purolator/nusoap/class.wsdl.php:15` — `class wsdl extends nusoap_base {`
- `includes/modules/shipping/purolator/nusoap/class.wsdlcache.php:18` — `class nusoap_wsdlcache {`
- `includes/modules/shipping/purolator/nusoap/class.wsdlcache.php:207` — `class wsdlcache extends nusoap_wsdlcache {`
- `includes/modules/shipping/purolator/nusoap/class.xmlschema.php:15` — `class nusoap_xmlschema extends nusoap_base  {`
- `includes/modules/shipping/purolator/nusoap/class.xmlschema.php:969` — `class XMLSchema extends nusoap_xmlschema {`
- `includes/modules/shipping/purolator/nusoap/nusoap.php:1007` — `class nusoap_fault extends nusoap_base {`
- `includes/modules/shipping/purolator/nusoap/nusoap.php:1079` — `class soap_fault extends nusoap_fault {`
- `includes/modules/shipping/purolator/nusoap/nusoap.php:1095` — `class nusoap_xmlschema extends nusoap_base  {`
- `includes/modules/shipping/purolator/nusoap/nusoap.php:2049` — `class XMLSchema extends nusoap_xmlschema {`
- `includes/modules/shipping/purolator/nusoap/nusoap.php:2067` — `class soapval extends nusoap_base {`
- `includes/modules/shipping/purolator/nusoap/nusoap.php:2169` — `class soap_transport_http extends nusoap_base {`
- `includes/modules/shipping/purolator/nusoap/nusoap.php:3474` — `class nusoap_server extends nusoap_base {`
- `includes/modules/shipping/purolator/nusoap/nusoap.php:4581` — `class soap_server extends nusoap_server {`
- `includes/modules/shipping/purolator/nusoap/nusoap.php:4597` — `class wsdl extends nusoap_base {`
- `includes/modules/shipping/purolator/nusoap/nusoap.php:6532` — `class nusoap_parser extends nusoap_base {`
- `includes/modules/shipping/purolator/nusoap/nusoap.php:7156` — `class soap_parser extends nusoap_parser {`
- `includes/modules/shipping/purolator/nusoap/nusoap.php:7183` — `class nusoap_client extends nusoap_base  {`
- `includes/modules/shipping/purolator/nusoap/nusoap.php:8145` — `	class soapclient extends nusoap_client {`
- `includes/modules/shipping/purolator/nusoap/nusoap.php:85` — `class nusoap_base {`
- `includes/modules/shipping/purolator/nusoap/nusoapmime.php:271` — `	class soapclientmime extends nusoap_client_mime {`
- `includes/modules/shipping/purolator/nusoap/nusoapmime.php:284` — `class nusoap_server_mime extends nusoap_server {`
- `includes/modules/shipping/purolator/nusoap/nusoapmime.php:498` — `class nusoapservermime extends nusoap_server_mime {`
- `includes/modules/shipping/purolator/nusoap/nusoapmime.php:54` — `class nusoap_client_mime extends nusoap_client {`
- `includes/modules/shipping/purolator/purolator.php:23` — `class purolator extends CommercePluginShippingBase {`
- `includes/modules/shipping/storepickup.php:13` — `class storepickup extends CommercePluginShippingRateTableBase {`
- `includes/modules/shipping/supersaver.php:13` — `class supersaver extends CommercePluginShippingBase {`
- `includes/modules/shipping/table.php:13` — `class table extends CommercePluginShippingBase {`
- `includes/modules/shipping/upsoauth/UpsOAuthApi.php:11` — `class UpsOAuthApi extends CommerceBase`
- `includes/modules/shipping/upsoauth/upsoauth.php:17` — `class upsoauth extends CommercePluginShippingBase`
- `includes/modules/shipping/usps.php:29` — `class usps extends CommercePluginShippingBase`
- `includes/modules/shipping/zones.php:108` — `class zones extends CommercePluginShippingBase {`

## Web-facing PHP controllers

- `admin/alt_nav.php`
- `admin/api_help_inc.php`
- `admin/backups/index.php`
- `admin/banner_manager.php`
- `admin/banner_statistics.php`
- `admin/categories.php`
- `admin/commissions.php`
- `admin/configuration.php`
- `admin/countries.php`
- `admin/coupon_admin.php`
- `admin/coupon_restrict.php`
- `admin/currencies.php`
- `admin/currencies_cron.php`
- `admin/customers.php`
- `admin/define_pages_editor.php`
- `admin/document_general.php`
- `admin/document_product.php`
- `admin/email_welcome.php`
- `admin/export_orders.php`
- `admin/export_users.php`
- `admin/featured.php`
- `admin/geo_zones.php`
- `admin/group_pricing.php`
- `admin/gv_mail.php`
- `admin/gv_queue.php`
- `admin/gv_sent.php`
- `admin/index.php`
- `admin/interests.php`
- `admin/invoice.php`
- `admin/languages.php`
- `admin/layout_controller.php`
- `admin/list_orders.php`
- `admin/mail.php`
- `admin/manufacturers.php`
- `admin/media_manager.php`
- `admin/media_types.php`
- `admin/modules.php`
- `admin/newsletters.php`
- `admin/orders.php`
- `admin/orders_list_inc.php`
- `admin/orders_status.php`
- `admin/paypal.php`
- `admin/popup_image.php`
- `admin/product.php`
- `admin/product_free_shipping.php`
- `admin/product_history.php`
- `admin/product_music.php`
- `admin/product_types.php`
- `admin/products_expected.php`
- `admin/products_options.php`
- `admin/products_price_manager.php`
- `admin/products_to_categories.php`
- `admin/pump_bitcommerce_inc.php`
- `admin/revenue.php`
- `admin/revenue_inc.php`
- `admin/reviews.php`
- `admin/salemaker.php`
- `admin/salemaker_info.php`
- `admin/salemaker_popup.php`
- `admin/sales_and_income.php`
- `admin/schema_inc.php`
- `admin/shipping_change.php`
- `admin/shipstation.php`
- `admin/specials.php`
- `admin/stats_customers.php`
- `admin/stats_customers_referrals.php`
- `admin/stats_products_lowstock.php`
- `admin/stats_products_purchased.php`
- `admin/stats_products_types.php`
- `admin/stats_products_viewed.php`
- `admin/suppliers.php`
- `admin/tax_classes.php`
- `admin/tax_rates.php`
- `admin/template_select.php`
- `admin/zones.php`
- `index.php`
- `ipn_main_handler.php`
- `modules/mod_address_edit.php`
- `modules/mod_admin_header_navigation.php`
- `modules/mod_banner_box.php`
- `modules/mod_banner_box2.php`
- `modules/mod_banner_box_all.php`
- `modules/mod_best_sellers.php`
- `modules/mod_categories.php`
- `modules/mod_commerce_bar.php`
- `modules/mod_currencies.php`
- `modules/mod_document_categories.php`
- `modules/mod_featured.php`
- `modules/mod_languages.php`
- `modules/mod_manufacturer_info.php`
- `modules/mod_manufacturers.php`
- `modules/mod_product_notifications.php`
- `modules/mod_record_companies.php`
- `modules/mod_reviews.php`
- `modules/mod_shopping_cart.php`
- `modules/mod_specials.php`
- `modules/mod_tell_a_friend.php`
- `modules/mod_whats_new.php`
- `modules/mod_whos_online.php`
- `pages/account/account.php`
- `pages/account_edit/account_edit.php`
- `pages/account_edit/header_php.php`
- `pages/account_edit/jscript_form_check.php`
- `pages/account_history/account_history.php`
- `pages/account_history/header_php.php`
- `pages/account_history_info/account_history_info.php`
- `pages/account_newsletters/account_newsletters.php`
- `pages/account_newsletters/header_php.php`
- `pages/account_newsletters/jscript_main.php`
- `pages/account_notifications/account_notifications.php`
- `pages/account_notifications/header_php.php`
- `pages/account_notifications/jscript_main.php`
- `pages/account_password/jscript_form_check.php`
- `pages/address_book/address_book.php`
- `pages/address_book/states.php`
- `pages/advanced_search/advanced_search.php`
- `pages/advanced_search/header_php.php`
- `pages/advanced_search/jscript_main.php`
- `pages/advanced_search_result/advanced_search_result.php`
- `pages/advanced_search_result/header_php.php`
- `pages/batch_order/batch_order.php`
- `pages/checkout_confirmation/checkout_confirmation.php`
- `pages/checkout_payment/checkout_payment.php`
- `pages/checkout_process/checkout_process.php`
- `pages/checkout_proof/checkout_proof.php`
- `pages/checkout_shipping/checkout_shipping.php`
- `pages/checkout_success/checkout_success.php`
- `pages/commissions/commissions.php`
- `pages/conditions/conditions.php`
- `pages/conditions/header_php.php`
- `pages/contact_us/contact_us.php`
- `pages/contact_us/header_php.php`
- `pages/cookie_usage/cookie_usage.php`
- `pages/cookie_usage/header_php.php`
- `pages/create_account_success/create_account_success.php`
- `pages/create_account_success/header_php.php`
- `pages/customers_authorization/header_php.php`
- `pages/document_general_info/document_general_info_display.php`
- `pages/document_general_info/header_php.php`
- `pages/document_general_info/jscript_main.php`
- `pages/document_general_info/main_template_vars.php`
- `pages/document_general_info/main_template_vars_attributes.php`
- `pages/document_general_info/main_template_vars_images.php`
- `pages/document_general_info/main_template_vars_images_additional.php`
- `pages/document_product_info/document_product_info_display.php`
- `pages/document_product_info/header_php.php`
- `pages/document_product_info/jscript_main.php`
- `pages/document_product_info/main_template_vars.php`
- `pages/document_product_info/main_template_vars_attributes.php`
- `pages/document_product_info/main_template_vars_images.php`
- `pages/document_product_info/main_template_vars_images_additional.php`
- `pages/down_for_maintenance/down_for_maintenance.php`
- `pages/down_for_maintenance/header_php.php`
- `pages/download/header_php.php`
- `pages/download_time_out/download_time_out.php`
- `pages/download_time_out/header_php.php`
- `pages/dropship/dropship.php`
- `pages/featured_products/featured_products.php`
- `pages/featured_products/header_php.php`
- `pages/gv_faq/gv_faq.php`
- `pages/gv_redeem/gv_redeem.php`
- `pages/gv_send/gv_send.php`
- `pages/index/header_php.php`
- `pages/index/index.php`
- `pages/info_paypal/header_php.php`
- `pages/info_paypal/info_paypal.php`
- `pages/info_paypal/jscript_main.php`
- `pages/info_shopping_cart/header_php.php`
- `pages/info_shopping_cart/info_shopping_cart.php`
- `pages/install/header_php.php`
- `pages/invoices/invoices.php`
- `pages/logoff/header_php.php`
- `pages/logoff/logoff.php`
- `pages/member_products/header_php.php`
- `pages/member_products/member_products.php`
- `pages/popup_attributes_qty_prices/header_php.php`
- `pages/popup_attributes_qty_prices/jscript_main.php`
- `pages/popup_attributes_qty_prices/popup_attributes_qty_prices.php`
- `pages/popup_coupon_help/header_php.php`
- `pages/popup_coupon_help/jscript_main.php`
- `pages/popup_coupon_help/popup_coupon_help.php`
- `pages/popup_cvv_help/header_php.php`
- `pages/popup_cvv_help/jscript_main.php`
- `pages/popup_cvv_help/popup_cvv_help.php`
- `pages/popup_image/header_php.php`
- `pages/popup_image/jscript_main.php`
- `pages/popup_image/popup_image.php`
- `pages/popup_image_additional/header_php.php`
- `pages/popup_image_additional/jscript_main.php`
- `pages/popup_image_additional/popup_image_additional.php`
- `pages/popup_search_help/header_php.php`
- `pages/popup_search_help/jscript_main.php`
- `pages/popup_search_help/popup_search_help.php`
- `pages/privacy/header_php.php`
- `pages/privacy/privacy.php`
- `pages/product_free_shipping_info/header_php.php`
- `pages/product_free_shipping_info/jscript_main.php`
- `pages/product_free_shipping_info/main_template_vars.php`
- `pages/product_free_shipping_info/main_template_vars_attributes.php`
- `pages/product_free_shipping_info/main_template_vars_images.php`
- `pages/product_free_shipping_info/main_template_vars_images_additional.php`
- `pages/product_free_shipping_info/product_free_shipping_info_display.php`
- `pages/product_info/product_info.php`
- `pages/product_reviews/header_php.php`
- `pages/product_reviews/jscript_main.php`
- `pages/product_reviews/late_header_php.php`
- `pages/product_reviews/main_template_vars_images.php`
- `pages/product_reviews/product_reviews.php`
- `pages/product_reviews_info/header_php.php`
- `pages/product_reviews_info/jscript_main.php`
- `pages/product_reviews_info/main_template_vars_images.php`
- `pages/product_reviews_info/product_reviews_info.php`
- `pages/product_reviews_write/header_php.php`
- `pages/product_reviews_write/jscript_main.php`
- `pages/product_reviews_write/main_template_vars_images.php`
- `pages/product_reviews_write/product_reviews_write.php`
- `pages/products_all/header_php.php`
- `pages/products_all/products_all.php`
- `pages/products_new/header_php.php`
- `pages/products_new/products_new.php`
- `pages/redirect/header_php.php`
- `pages/reviews/header_php.php`
- `pages/reviews/reviews.php`
- `pages/shippinginfo/header_php.php`
- `pages/shippinginfo/shippinginfo.php`
- `pages/shopping_cart/shopping_cart.php`
- `pages/specials/header_php.php`
- `pages/specials/specials.php`
- `pages/ssl_check/header_php.php`
- `pages/ssl_check/ssl_check.php`
- `pages/time_out/header_php.php`
- `pages/time_out/time_out.php`
- `pages/unsubscribe/header_php.php`
- `pages/unsubscribe/unsubscribe.php`
- `pages/user_products/header_php.php`
- `pages/user_products/user_products.php`
- `pages/whitelabel/whitelabel.php`
- `shipping_estimator.php`
- `sitemap.php`

## Declared schema tables

No table declaration was mechanically identified. Inspect schema files and runtime SQL before concluding that the package is stateless.

## Plugin and module directories

- `admin/includes/languages/en/modules/`
- `admin/includes/modules/`
- `htmlarea/plugins/`
- `includes/languages/en/modules/`
- `includes/modules/`
- `modules/`

## Templates

- `admin/includes/modules/amazonmws/amazonmws_list_orders.tpl`
- `modules/mod_address_edit.tpl`
- `modules/mod_admin_header_navigation.tpl`
- `modules/mod_banner_box.tpl`
- `modules/mod_banner_box2.tpl`
- `modules/mod_banner_box_all.tpl`
- `modules/mod_best_sellers.tpl`
- `modules/mod_categories.tpl`
- `modules/mod_commerce_bar.tpl`
- `modules/mod_commerce_information.tpl`
- `modules/mod_currencies.tpl`
- `modules/mod_document_categories.tpl`
- `modules/mod_featured.tpl`
- `modules/mod_information.tpl`
- `modules/mod_languages.tpl`
- `modules/mod_manufacturer_info.tpl`
- `modules/mod_manufacturers.tpl`
- `modules/mod_product_notifications.tpl`
- `modules/mod_record_companies.tpl`
- `modules/mod_reviews.tpl`
- `modules/mod_search.tpl`
- `modules/mod_shopping_cart.tpl`
- `modules/mod_specials.tpl`
- `modules/mod_tell_a_friend.tpl`
- `modules/mod_whats_new.tpl`
- `modules/mod_whos_online.tpl`
- `templates/account_history_info_inc.tpl`
- `templates/address_display_inc.tpl`
- `templates/address_edit_inc.tpl`
- `templates/address_list_inc.tpl`
- `templates/admin_bitcommerce.tpl`
- `templates/admin_categories.tpl`
- `templates/admin_commission_payment_inc.tpl`
- `templates/admin_commissions.tpl`
- `templates/admin_commissions_list_inc.tpl`
- `templates/admin_coupon_edit.tpl`
- `templates/admin_coupon_list.tpl`
- `templates/admin_coupon_report.tpl`
- `templates/admin_coupon_restrict.tpl`
- `templates/admin_customer_edit.tpl`
- `templates/admin_customer_list.tpl`
- `templates/admin_export_orders.tpl`
- `templates/admin_export_users.tpl`
- `templates/admin_gv_mail.tpl`
- `templates/admin_gv_sent.tpl`
- `templates/admin_header_inc.tpl`
- `templates/admin_header_menu_inc.tpl`
- `templates/admin_interests.tpl`
- `templates/admin_interests_customer_inc.tpl`
- `templates/admin_list_orders.tpl`
- `templates/admin_list_orders_inc.tpl`
- `templates/admin_order.tpl`
- `templates/admin_order_header_inc.tpl`
- `templates/admin_order_status_history_inc.tpl`
- `templates/admin_product_category_listing_inc.tpl`
- `templates/admin_product_history.tpl`
- `templates/admin_products_options.tpl`
- `templates/admin_products_options_edit_inc.tpl`
- `templates/admin_products_options_map_inc.tpl`
- `templates/admin_products_options_values_edit_inc.tpl`
- `templates/admin_revenue.tpl`
- `templates/admin_revenue_inc.tpl`
- `templates/admin_revenue_interest.tpl`
- `templates/admin_revenue_matrix.tpl`
- `templates/admin_revenue_referer.tpl`
- `templates/admin_revenue_timeframe.tpl`
- `templates/admin_reviews_edit.tpl`
- `templates/admin_reviews_list.tpl`
- `templates/admin_reviews_list_inc.tpl`
- `templates/admin_sales_and_income.tpl`
- `templates/admin_shipping_change_ajax.tpl`
- `templates/admin_stats_customers.tpl`
- `templates/admin_stats_customers_abandoned_inc.tpl`
- `templates/admin_stats_products_types.tpl`
- `templates/admin_stats_sales_by_option_inc.tpl`
- `templates/admin_stats_sales_by_type_inc.tpl`
- `templates/admin_uninterested_customers.tpl`
- `templates/bitcommerce_mini_search.tpl`
- `templates/breadcrumbs_inc.tpl`
- `templates/center_list_products.tpl`
- `templates/center_orders_history.tpl`
- `templates/checkout_javascript.tpl`
- `templates/commerce_nav.tpl`
- `templates/commerce_pagination.tpl`
- `templates/commissions.tpl`
- `templates/commissions_list_inc.tpl`
- `templates/commissions_payment_options_inc.tpl`
- `templates/default_index.tpl`
- `templates/footer_inc.tpl`
- `templates/gv_faq.tpl`
- `templates/gv_purchase_email_html.tpl`
- `templates/gv_purchase_email_text.tpl`
- `templates/gv_redeem.tpl`
- `templates/gv_send.tpl`
- `templates/gv_send_email_html.tpl`
- `templates/gv_send_email_text.tpl`
- `templates/html_head_inc.tpl`
- `templates/list_box_content_inc.tpl`
- `templates/list_products.tpl`
- `templates/list_products_inc.tpl`
- `templates/menu_bitcommerce.tpl`
- `templates/menu_bitcommerce_admin.tpl`
- `templates/order_address_edit.tpl`
- `templates/order_invoice.tpl`
- `templates/order_invoice_contents_inc.tpl`
- `templates/order_payment_edit.tpl`
- `templates/order_success.tpl`
- `templates/page_account.tpl`
- `templates/page_address_book.tpl`
- `templates/page_batch_order.tpl`
- `templates/page_checkout_confirmation.tpl`
- `templates/page_checkout_deadline_inc.tpl`
- `templates/page_checkout_header_inc.tpl`
- `templates/page_checkout_message_bot_inc.tpl`
- `templates/page_checkout_message_top_inc.tpl`
- `templates/page_checkout_payment.tpl`
- `templates/page_checkout_proof.tpl`
- `templates/page_checkout_shipping.tpl`
- `templates/page_checkout_success.tpl`
- `templates/page_checkout_success_inc.tpl`
- `templates/page_checkout_tracking_inc.tpl`
- `templates/page_dropship.tpl`
- `templates/page_invoices.tpl`
- `templates/page_product_info.tpl`
- `templates/page_shopping_cart.tpl`
- `templates/page_whitelabel.tpl`
- `templates/popup_shipping_estimator.tpl`
- `templates/product_not_available.tpl`
- `templates/product_options_inc.tpl`
- `templates/register_customer.tpl`
- `templates/shipping_estimator_inc.tpl`
- `templates/shipping_quotes_inc.tpl`
- `templates/shopping_cart_contents_inc.tpl`
- `templates/sphinx_bitcommerce_results.tpl`
- `templates/user_products.tpl`
- `templates/user_register_inc.tpl`
- `templates/view_bitcommerce.tpl`

## Reading cautions

- Presence in this inventory does not make a file a supported public API.
- Bundled third-party libraries must be distinguished from package-owned code.
- Base schema files do not prove the migration state of a deployed database.
- Controllers may rely on include files, globals, services, and template callbacks not visible from their filename alone.
