=== Gallery for Tezos NFTs ===
Contributors: faridjc
Tags: nft, blockchain, tezos, art, gallery, images
Requires at least: 6.0
Tested up to: 6.4.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display Tezos NFTs on your WordPress website

== Description ==

This plugin allows you to create a gallery of your Tezos NFTs -created and/or owned-, and embed it to any post or page using a shortcode. The gallery will automatically show the all the tokens corresponding to the wallet address configured via settings, ordered by latest minted.

Gallery for Tezos NFTs plugin uses objkt.com public API v3 to obtain data automatically, without any action required when new NFTs are added to the wallet address configured.

This plugin or its developers are not linked with the Tezos Fundation or any Tezos-related legal entity. All trademarks belong to their respective owners.

This plugin uses [Bootstrap](https://getbootstrap.com/). 

IMPORTANT: This will plugin will *NOT* ask you to connect your wallet or authorize any signature or transaction to the blockchain.

== Installation and usage ==

1. Upload the plugin contents to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the plugin settings page and configure your wallet address (no login or wallet connection is required).
4. Use the shortcode [tezos_nft_gallery_created] to add a gallery of created tokens.
5. Use the shortcode [tezos_nft_gallery_owned] to add a gallery of owned tokens.

== Screenshots ==

1. Gallery
2. Admin page

== Changelog ==
= v1.0.0 =
* Initial release
