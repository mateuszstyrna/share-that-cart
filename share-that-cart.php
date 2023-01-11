<?php
/*
Plugin Name: 	Share That Cart
Description: 	This plugin allows you to share your cart via link with others.
Version: 		1.3.1
Author:			Mateusz Styrna
Author URI:		https://mateusz-styrna.pl/
Plugin URI:		https://wordpress.org/plugins/share-that-cart/
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( !defined( 'SC_VERSION' ) ) {
    define( 'SC_VERSION', '1.0.1' );
}

add_action('woocommerce_after_cart_contents', 'sc_button');
add_action( 'wp_enqueue_scripts', 'sc_load_scripts' );
add_action('wp_loaded', 'sc_add_products');
register_activation_hook(__FILE__, 'sc_active');

function sc_active() {
	if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		deactivate_plugins( basename( __FILE__ ) );
		die(_e('Plugin NOT activated: Share That Cart requires the WooCommerce plugin to be installed and active.', 'share-that-cart'));
		return false;
	} else {
		return true;
	}
}

function sc_load_scripts() {
	if ( is_cart() ) {
		wp_enqueue_style( 'sc-css', plugins_url('/style.css', __FILE__), '', SC_VERSION, false );
		wp_enqueue_script( 'sc-js', plugins_url('/copy.js', __FILE__), '', SC_VERSION, true );
	}
}

function sc_button() {
	$items = WC()->cart->get_cart();
	$link = wc_get_cart_url();
	$ids = "";
	$quantities = "";
	$attr = "";

    foreach($items as $item => $values) { 
		$ids .= $values['product_id'] . ",";
		$quantities .= $values['quantity'] . ",";

		if ($values['variation_id']) {
			$attr .= $values['product_id'] . "," . $values['variation_id'] . ",";
		}
    }

    $link .= "?scp=" .substr($ids, 0, -1). "&scq=".substr($quantities, 0, -1) . "&sca=".substr($attr, 0, -1);
	?>
	<tr>
		<td colspan="6" class="actions">
			<div class="sc__content">
				<textarea class="sc__link" id="sc__link" ><?php echo $link; ?></textarea>
				<button id="sc__button" class="sc__button">
					<?php
					echo _e('Share That Cart', 'share-that-cart');
					?>
				</button>
			</div>
		</td>
	</tr>
	<div class="sc__popup" id="sc__popup">
		<?php
		echo _e('Done! You can now share link to this cart by simply pasting it. It is in your clipboard!', 'share-that-cart');
		?>
	</div>
	<?php
}

function sc_secure($arr) {
	$arrFinal = array();
	array_walk($arr, function($value,$key) use (&$arr, &$arrFinal){
		$value = intval($value);
		if ($value != 0) {
			array_push($arrFinal, $value);
		}
	});
	$arr = $arrFinal;
	return $arr;
}

function sc_add_products() {
	if (isset($_GET['scp']) && isset($_GET['scq'])) {
		$sc_products = sc_secure(explode(",", $_GET['scp']));
		var_dump($sc_products);
		$sc_qty = sc_secure(explode(",", $_GET['scq']));
		var_dump($sc_qty);
		$sc_attrs = sc_secure(explode(",", $_GET['sca']));
		var_dump($sc_attrs);

		$i = 0;
		while ($sc_products[$i]) {

			if (array_search($sc_products[$i],$sc_attrs) !== false) {
				WC()->cart->add_to_cart( $sc_products[$i], $sc_qty[$i], $sc_attrs[array_search( $sc_products[$i], $sc_attrs ) + 1] );
				\array_splice($sc_attrs, array_search($sc_products[$i],$sc_attrs), array_search($sc_products[$i],$sc_attrs)+1);
			}
			else {
				WC()->cart->add_to_cart( $sc_products[$i], $sc_qty[$i] );
			}
			$i++;
		}
		wp_redirect(wc_get_cart_url());
        exit;
	}
}

load_plugin_textdomain('share-that-cart', false, basename( dirname( __FILE__ ) ) . '/languages' );
?>