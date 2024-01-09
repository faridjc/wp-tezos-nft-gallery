<?php

function tezos_nft_gallery_admin_menu_page(){
	if (
		isset( $_POST['address'] ) && isset( $_POST['gateway'] )  && isset( $_POST['page_size'] )  && isset( $_POST['row_size'] ) && isset( $_POST['tezos_nft_gallery_nonce'] ) &&
		wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tezos_nft_gallery_nonce'] ) ), 'tezos_nft_gallery_save_settings' )
	) {
		$address = sanitize_text_field( wp_unslash( $_POST['address'] ) );
		$gateway = sanitize_text_field( wp_unslash( $_POST['gateway'] ) );
		$page_size = sanitize_text_field( wp_unslash( $_POST['page_size'] ) );
		$row_size = sanitize_text_field( wp_unslash( $_POST['row_size'] ) );
		
		if( ! Tezos_NFT_Gallery::is_valid_tezos_address($address) ){
			// TODO: error
		}
		
		update_option( 'tezos_nft_gallery_address', $address );
		update_option( 'tezos_nft_gallery_gateway', $gateway );
		update_option( 'tezos_nft_gallery_row_size', $row_size );
		update_option( 'tezos_nft_gallery_page_size', $page_size );
		
	}
	
	$address = get_option( 'tezos_nft_gallery_address', '' );
	$gateway = get_option( 'tezos_nft_gallery_gateway', '' );
	$page_size = get_option( 'tezos_nft_gallery_page_size', '' );
	$row_size = get_option( 'tezos_nft_gallery_row_size', '' );
?>
<div class="wrap">
	<h1>Tezos NFT Gallery</h1>
	<div class="card">
		<h3>How to use</h3>
		<ul>
			<li>
				<p>To create a gallery of created tokens, add this shortcode to any post or page:</p>
				<pre>[tezos_nft_gallery_created]</pre>
			</li>
			<li>
				<p>To create a gallery of owned tokens, add this shortcode to any post or page:</p>
				<pre>[tezos_nft_gallery_owned]</pre>
			</li>
		</ul>
	</div>
	<form method="post" action="" id="tezos_nft_gallery_settings_form">
		<?php wp_nonce_field( 'tezos_nft_gallery_save_settings', 'tezos_nft_gallery_nonce' ); ?>
		<table class="form-table">
			<tr>
				<th>
					<label for="gateway">
						Gateway
					</label>
				</th>
				<td>
					<label class="">
						<select name="gateway" id="gateway">
							<option <?php selected( $gateway, 'cloudfare' );?> value="cloudfare">Cloudfare</option>
							<option <?php selected( $gateway, 'ipfs' );?> value="ipfs">IPFS</option>
						</select>
					</label>
				</td>
			</tr>
			<tr>
				<th>
					<label for="address">
						Wallet address
					</label>
				</th>
				<td>
					<label class="">
						<input type="text" size=50 name="address" id="address" value="<?php echo esc_attr($address);  ?>"/>
					</label>
				</td>
			</tr>
			<tr>
				<th>
					<label for="page_size">
						Page size
					</label>
				</th>
				<td>
					<label class="">
						<select name="page_size" id="page_size">
							<option <?php selected( $page_size, '4' );?> value="4">4</option>
							<option <?php selected( $page_size, '6' );?> value="6">6</option>
							<option <?php selected( $page_size, '8' );?> value="8">8</option>
							<option <?php selected( $page_size, '9' );?> value="9">9</option>
							<option <?php selected( $page_size, '12' );?> value="12">12</option>
						</select>
					</label>
				</td>
			</tr>
			<tr>
				<th>
					<label for="row_size">
						Row size
					</label>
				</th>
				<td>
					<label class="">
						<select name="row_size" id="row_size">
							<option <?php selected( $row_size, '2' );?> value="2">2</option>
							<option <?php selected( $row_size, '3' );?> value="3">3</option>
						</select>
					</label>
				</td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
</div>
<?php
}
