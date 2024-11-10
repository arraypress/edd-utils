<?php
/**
 * Plugin Name: ArrayPress - Easy Digital Downloads Utility Library
 * Plugin URI: https://arraypress.com/
 * Description: Loads the Easy Digital Downloads Utility Library files.
 * Version: 1.0.0
 * Author: ArrayPress
 * Author URI: https://arraypress.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: edd-utils-loader
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define the plugin directory path.
define( 'EDD_UTILS_DIR', plugin_dir_path( __FILE__ ) . 'src/' );

use function ArrayPress\EDD\register_custom_customer_stats;
use function ArrayPress\EDD\register_custom_exporters;
use function ArrayPress\EDD\register_custom_variable_price_options;
use function ArrayPress\EDD\register_custom_download_settings;

// Function to load the utility files.
function edd_utils_load_files() {
	$files = array(
		'Traits/Cart/Core.php',
		'Traits/Cart/Discounts.php',
		'Traits/Cart/ProductType.php',
		'Traits/Cart/Products.php',
		'Traits/Cart/Terms.php',
		'Traits/Customer/Admin.php',
		'Traits/Customer/Comments.php',
		'Traits/Customer/Core.php',
		'Traits/Customer/Orders.php',
		'Traits/Customer/Products.php',
		'Traits/Customer/Reviews.php',
		'Traits/Customer/User.php',
		'Traits/Download/Analytics.php',
		'Traits/Download/Core.php',
		'Traits/Download/Licensing.php',
		'Traits/Download/Orders.php',
		'Traits/Download/Price.php',
		'Traits/Download/Recurring.php',
		'Traits/Download/Reviews.php',
		'Traits/Download/VariablePrices.php',
		'Traits/Downloads/Analytics.php',
		'Traits/Downloads/Core.php',
		'Traits/Files/Core.php',
		'Traits/Files/Fields.php',
		'Traits/Files/Key.php',
		'Traits/Files/Price.php',
		'Traits/Order/Adjustments.php',
		'Traits/Order/Admin.php',
		'Traits/Order/Analytics.php',
		'Traits/Order/Commissions.php',
		'Traits/Order/Conditions.php',
		'Traits/Order/Core.php',
		'Traits/Order/Discounts.php',
		'Traits/Order/Fields.php',
		'Traits/Order/Files.php',
		'Traits/Order/Licensing.php',
		'Traits/Order/Products.php',
		'Traits/Order/Recurring.php',
		'Traits/Order/Referral.php',
		'Traits/Order/Terms.php',
		'Traits/Orders/Analytics.php',
		'Traits/Orders/Core.php',
		'Traits/Orders/Distinct.php',
		'Traits/Orders/Earnings.php',
		'Traits/Orders/Referral.php',
		'Traits/Orders/Sales.php',

		'Adjustments/Adjustment.php',
		'Cart/Cart.php',
		'Common/Extensions.php',
		'Common/Format.php',
		'Common/Options.php',
		'Customers/Customer.php',
		'Customers/CustomerAddress.php',
		'Customers/CustomerEmailAddress.php',
		'Customers/Customers.php',
		'Customers/Search.php',
		'Date/Calc.php',
		'Date/Common.php',
		'Date/Compare.php',
		'Date/Format.php',
		'Date/Generate.php',
		'Date/Timezone.php',
		'Discounts/Discount.php',
		'Discounts/Discounts.php',
		'Discounts/Search.php',
		'Downloads/Download.php',
		'Downloads/Downloads.php',
		'Downloads/Files.php',
		'Downloads/Search.php',
		'Gateways/Gateway.php',
		'Gateways/Gateways.php',
		'Logs/APIRequestLog.php',
		'Logs/EmailLog.php',
		'Logs/FileDownloadLog.php',
		'Logs/Log.php',
		'Notes/Note.php',
		'Orders/Order.php',
		'Orders/OrderAddress.php',
		'Orders/OrderAdjustment.php',
		'Orders/OrderItem.php',
		'Orders/OrderTransaction.php',
		'Orders/Orders.php',
		'Register/Admin/Notices.php',
		'Register/Customer/Stats.php',
		'Register/Download/DownloadSettings.php',
		'Register/Download/SidebarMetaboxes.php',
		'Register/Download/SinglePriceOptions.php',
		'Register/Download/VariablePriceOptions.php',
		'Register/Export/BatchExporters.php',
		'Register/Export/Columns.php',
		'Register/Export/Metaboxes.php',
		'Register/Tools/Recount.php',
		'Register/Utils/Admin.php',
		'Register/Utils/Customer.php',
		'Register/Utils/Download.php',
		'Register/Utils/Export.php',
		'Register/Utils/Tools.php',

		'Utils/Customers.php',
		'Utils/Discounts.php',
		'Utils/Downloads.php',
	);

	foreach ( $files as $file ) {
		$file_path = EDD_UTILS_DIR . $file;
		if ( file_exists( $file_path ) ) {
			require_once $file_path;
		}
	}

}

// Hook to load the files.
add_action( 'plugins_loaded', 'edd_utils_load_files' );

use ArrayPress\EDD\Discounts\Discounts;

/**
 * Output gateway stats test data
 */
add_action( 'edd_edit_discount_form_top', function () {
	echo Discounts::get_average_savings();
	$discounts = Discounts::get_highest_savings( null, null, false, true );

	foreach( $discounts as $discount ) {
		echo $discount->code . '<br>';
	}
} );