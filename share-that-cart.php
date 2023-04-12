<?php
/*
Plugin Name: 	Share That Cart
Description: 	This plugin allows you to share your cart via link with others.
Version: 		1.4.0
Author:			Mateusz Styrna
Author URI:		https://mateusz-styrna.pl/
Plugin URI:		https://wordpress.org/plugins/share-that-cart/
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( !defined( 'SC_VERSION' ) ) {
    define( 'SC_VERSION', '1.2.0' );
}

add_action( 'woocommerce_after_cart_contents', 'sc_button' );
add_action( 'wp_enqueue_scripts', 'sc_load_scripts' );
add_action( 'wp_loaded', 'sc_add_products' );
register_activation_hook( __FILE__, 'sc_active' );

function sc_active() {
	if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		deactivate_plugins( basename( __FILE__ ) );
		die( _e( 'Plugin NOT activated: Share That Cart requires the WooCommerce plugin to be installed and active.', 'share-that-cart' ) );
		return false;
	} else {
		return true;
	}
}

function sc_load_scripts() {
	if ( is_cart() ) {
		wp_enqueue_style( 'sc-css', plugins_url( '/style.css', __FILE__), '', SC_VERSION, false );
		wp_enqueue_script( 'sc-js', plugins_url( '/copy.js', __FILE__), '', SC_VERSION, true );
	}
}

function sc_button() {
	$cart_items = WC()->cart->get_cart();
	$link = wc_get_cart_url();
	$items = [];

    foreach( $cart_items as $cart_item => $values ) {

    	if ( $values[ 'variation_id' ] )
    		$attr = $values[ 'variation_id' ];
    	else
    		$attr = false;

    	$item = ( object ) array( 'ID' => $values[ 'product_id' ], 'QTY' => $values[ 'quantity' ], 'ATTR' => $attr );

    	array_push( $items, $item );
    }

    $link .= "?scp=" . base64_encode( json_encode( $items ) );
	?>
	<tr>
		<td colspan="6" class="actions">
			<div class="sc__content">
				<textarea class="sc__link" id="sc__link" ><?php echo $link; ?></textarea>
				<button id="sc__button" class="sc__button">
					<?php
					echo _e( 'Share That Cart', 'share-that-cart' );
					?>
				</button>
			</div>
		</td>
	</tr>
	<div class="sc__popup" id="sc__popup">
		<?php
		echo _e( 'Done! You can now share link to this cart by simply pasting it. It is in your clipboard!', 'share-that-cart' );
		?>
	</div>
	<?php
}

function sc_add_products() {
	if ( !isset( $_GET[ 'scp' ] ) )
		return;

	$items = json_decode( base64_decode( $_GET[ 'scp' ] ) );

	foreach ( $items as $item ) {
		if ( $item->ATTR )
			WC()->cart->add_to_cart( intval( $item->ID ), intval( $item->QTY ), intval( $item->ATTR ) );
		else
			WC()->cart->add_to_cart(  intval( $item->ID ), intval( $item->QTY ) );
	}

	wp_redirect( wc_get_cart_url() );
    exit;
}

load_plugin_textdomain( 'share-that-cart', false, basename( dirname( __FILE__ ) ) . '/languages' );
?>