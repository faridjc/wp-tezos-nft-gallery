<?php
/**
 * Plugin Name: Gallery for Tezos NFTs
 * Plugin URI: https://cajabeatsart.com/wp-tezos-nft-gallery/
 * Description: Display Tezos NFTs on your WordPress website
 * Version: 1.0.0
 * Author: Farid Colmenarez
 * Author URI: https://cajabeatsart.com
 * License: GPLv3
 *
 * @package    WP_Tezos_NFT_Gallery
 */

/**
 * Exit if direct access
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Include admin page file
 */
require_once plugin_dir_path( __FILE__ ) . '/admin/wp-tezos-nft-gallery-admin-page.php';

/**
 * Include Tezos NFT Gallery class file
 */
require_once plugin_dir_path( __FILE__ ) . '/includes/class-tezos-nft-gallery.php';

/**
 * Constant for known token symbols
 */
define(
	'TEZOS_NFT_GALLERY_KNOWN_TOKEN_SYMBOLS',
	array(
		'OBJKT'    => array(
			'base_url' => 'https://teia.art/objkt/',
		),
		'OBJKTCOM' => array(
			'base_url' => 'https://www.objkt.com/asset/',
		),
		'ITEM'     => array(
			'base_url' => 'https://www.objkt.com/asset/versum_items/',
		),
		'GLIX'     => array(
			'base_url' => '', /** Glitch Forge */
		),
	)
);

/**
 * Constant for allowed mime types
 */
define(
	'TEZOS_NFT_GALLERY_ALLOWED_MIME_TYPES',
	array(
		'image/jpg',
		'image/jpeg',
		'image/png',
		'image/gif',
	)
);

/**
 * Created NFTs gallery shortcode
 */
add_shortcode(
	'tezos_nft_gallery_created',
	function() {
		$address   = get_option( 'tezos_nft_gallery_address' );
		$row_size  = get_option( 'tezos_nft_gallery_row_size', 2 );
		$page_size = get_option( 'tezos_nft_gallery_page_size', 12 );
		$gallery   = new Tezos_NFT_Gallery( $address, $page_size, $row_size );

		return $gallery->render_tokens_gallery( 'created' );
	}
);

/**
 * Owned NFTs gallery shortcode
 */
add_shortcode(
	'tezos_nft_gallery_owned',
	function() {
		$address   = get_option( 'tezos_nft_gallery_address' );
		$row_size  = get_option( 'tezos_nft_gallery_row_size', 2 );
		$page_size = get_option( 'tezos_nft_gallery_page_size', 12 );
		$gallery   = new Tezos_NFT_Gallery( $address, $page_size, $row_size );

		return $gallery->render_tokens_gallery( 'owned' );
	}
);


/**
 * Enqueue the styles and scripts
 *
 * @return void
 */
function tezos_nft_gallery_scripts() {
	wp_enqueue_script(
		'bootstrap',
		plugin_dir_url( __FILE__ ) . 'assets/js/bootstrap.bundle.min.js',
		array(),
		1,
		false
	);

	wp_enqueue_style(
		'bootstrap',
		plugin_dir_url( __FILE__ ) . 'assets/css/bootstrap.bundle.min.css',
		array(),
		1
	);

	wp_enqueue_style( 'dashicons' );

	wp_enqueue_style( 'wp-tezos-nft-gallery-style', plugin_dir_url( __FILE__ ) . 'public/css/wp-tezos-nft-gallery-style.css', array(), 1 );
}

add_action( 'wp_enqueue_scripts', 'tezos_nft_gallery_scripts' );

/**
 * Admin menu
 *
 * @return void
 */
function tezos_nft_gallery_admin_menu() {
	add_menu_page(
		'Tezos NFT Gallery',
		'Tezos NFT Gallery',
		'manage_options',
		'tezos-nft-gallery',
		'',
		'dashicons-art',
		32
	);

	add_submenu_page(
		'tezos-nft-gallery-admin',
		'Manage Gallery',
		'Tezos NFT Gallery',
		'manage_options',
		'tezos-nft-gallery',
		'tezos_nft_gallery_admin_menu_page',
	);
}

add_action( 'admin_menu', 'tezos_nft_gallery_admin_menu' );
