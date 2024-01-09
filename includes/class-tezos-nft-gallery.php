<?php

class Tezos_NFT_Gallery {
	private $address;
	private $page_size;
	private $row_size;

	public function __construct( $address, $page_size, $row_size ) {
		$this->address   = $address;
		$this->page_size = $page_size;
		$this->row_size  = $row_size;
	}

	public function get_tokens( $type, $current_page = 1 ) {
		$offset = ( 1 === $current_page ? 0 : ( ( $current_page - 1 ) * $this->page_size ) );
		
		// TODO: Exclude non-art tokens.
		if ( 'created' === $type ) {
			$query = '{"query": "query My{ token( where: {creators: {creator_address: {_in: \"' . $this->address . '\"}}} order_by: {timestamp: desc} limit: ' . ( $this->page_size + 1 ) . ' offset: ' . $offset . ' ) { artifact_uri description display_uri extra metadata mime name supply symbol thumbnail_uri timestamp token_id pk fa_contract fa { collection_id collection_type contract } } }"}';
		} else {
			$query = '{"query": "query My{ token( where: {holders: {holder: {address: {_in: \"' . $this->address . '\"}}}, creators: {creator_address: {_nin: \"' . $this->address . '\"}}} order_by: {timestamp: desc} limit: ' . ( $this->page_size + 1 ) . ' offset: ' . $offset . ' ) { artifact_uri display_uri extra mime name symbol thumbnail_uri timestamp token_id pk fa_contract fa { collection_id collection_type contract } }}"}';
		}

		$current_page_tokens = $this->do_request( $query )->data->token ?? array();
		$is_last_page        = count( $current_page_tokens ) <= $this->page_size;
		array_pop( $current_page_tokens );

		return array(
			'is_last_page'        => $is_last_page,
			'current_page_tokens' => $current_page_tokens,
		);
	}

	private function do_request( $query ) {
		$endpoint = 'https://data.objkt.com/v3/graphql';

		$data = array(
			'body'    => $query,
			'headers' => array(
				'Content-Type' => 'application/json',
			),
		);

		$response = wp_remote_post( $endpoint, $data );

		if ( is_wp_error( $response ) ) {
			// TODO: Handle error.
		}

		return json_decode( $response['body'] );
	}

	private function get_formatted_url( $token_id, $token_symbol, $token_contract_address ) {
		$base_url = '';
		switch ( $token_symbol ) {
			case 'OBJKTCOM':
				$base_url = KNOWN_TOKEN_SYMBOLS[ $token_symbol ]['base_url'] . $token_contract_address . '/' . $token_id;
				break;
			default:
				if ( empty( KNOWN_TOKEN_SYMBOLS[ $token_symbol ] ) ) {
					return '';
				}

				$base_url = KNOWN_TOKEN_SYMBOLS[ $token_symbol ]['base_url'] . $token_id;
				break;
		}

		return $base_url;
	}

	public static function is_valid_tezos_address( $address ) {
		return true;
	}

	public static function is_valid_tezos_network( $network ) {
		return in_array( $network, NETWORKS, true );
	}

	private function format_image_url( $url ) {
		$gateways  = array(
			'cloudfare' => 'https://cloudflare-ipfs.com/ipfs/',
			'ipfs'      => 'https://ipfs.io/ipfs/',
		);
		$gateway   = $gateways[ get_option( 'tezos_nft_gallery_gateway', '' ) ] ?? $gateway['cloudfare'];
		$image_url = str_replace( 'ipfs://', '', $url );

		return $gateway . $image_url;
	}

	function render_tokens_gallery( $type ) {
		$current_page = (int) ( $_GET['gallery_page'] ?? 1 );
		$tokens_data  = $this->get_tokens( $type, $current_page );
		$raw_tokens   = $tokens_data['current_page_tokens'];
		$is_last_page = $tokens_data['is_last_page'];

		if ( ! $raw_tokens ) {
			// handle error.
		}

		$formatted_tokens = array();

		foreach ( $raw_tokens as $token ) {
			$token_data       = array();
			$token_symbol     = $token->symbol;
			$contract_address = $token->fa_contract;

			$token_data['id']   = $token->token_id;
			$token_data['name'] = $token->name ?? '';
			$token_data['url']  = $this->get_formatted_url( $token_data['id'], $token_symbol, $contract_address );

			if ( str_starts_with( $token->artifact_uri, 'data:image/png' ) ) {
				$token_data['image_url'] = $token->artifact_uri;
				$token_data['is_8bidou'] = true;
			} else {
				if ( ( $token->extra[0]->file_size ?? 0 ) > 20000000 ) {
					// too big, use thumbnail.
					$image_url = $token->thumbnail_uri ?? '';
				} else {
					$image_url = $token->display_uri ?? '';
				}
				$token_data['image_url'] = $this->format_image_url( $image_url );
			}

			$token_data['format'] = '';

			if ( str_contains( $token->mime ?? '', 'audio' ) ) {
				$token_data['format'] = 'audio';
			}

			if ( str_contains( $token->mime ?? '', 'video' ) ) {
				$token_data['format'] = 'video';
			}

			$formatted_tokens[] = $token_data;
		}

		ob_start();
		?>
		<div class="tezos-nft-tool-container container text-center">
			<div class="row">
			<?php
			foreach ( $formatted_tokens as $formatted_token ) {
				?>
				<div class="col-md-<?php echo esc_attr( '2' === $this->row_size ? 6 : 4 ); ?> col-sm-12 p-3">
					<figure class="figure">
					<a target="_blank" href="<?php echo $formatted_token['url']; ?>"><img src="<?php echo $formatted_token['image_url']; ?>" class="figure-img img-fluid rounded <?php echo esc_attr( isset( $formatted_token['is_8bidou'] ) ? 'eightbidou' : '' ); ?>" /></a>
					<?php
					switch ( $formatted_token['format'] ) {
						case 'audio':
							?>
								<span class="tezos-nft-tool-icon dashicons dashicons-format-audio"></span>
								<?php
							break;
						case 'video':
							?>
								<span class="tezos-nft-tool-icon dashicons dashicons-editor-video"></span>
								<?php
							break;
					}
					?>
					</figure>
					<figcaption class="figure-caption"><?php echo $formatted_token['name']; ?></figcaption>
				</div>
				<?php
			}
			?>
			</div>
			<?php $this->tezos_nft_gallery_render_pagination( $current_page, $is_last_page ); ?>
		</div>
		<?php
		return ob_get_clean();
	}

	function tezos_nft_gallery_render_pagination( $current_page, $is_last_page ) {
		$page_1_number = 1 === $current_page ? 1 : ( $current_page - 1 );
		$page_1_link   = 1 === $current_page ? '#' : '?gallery_page=' . ( $current_page - 1 );
		$page_2_number = 1 === $current_page ? 2 : $current_page;
		$page_2_link   = 1 === $current_page ? '?gallery_page=2' : '#';
		$page_3_number = 1 === $current_page ? 3 : ( $current_page + 1 );
		$page_3_link   = 1 === $current_page ? '?gallery_page=3' : '?gallery_page=' . ( $current_page + 1 );
		?>
		<nav>
			<ul class="pagination justify-content-center">
				<li class="page-item <?php echo esc_attr( 1 === $current_page ? 'disabled' : '' ); ?>">
					<a class="page-link" href="?gallery_page=<?php echo esc_attr( $current_page - 1 ); ?>" aria-label="Previous">
						<span aria-hidden="true">&laquo;</span>
					</a>
				</li>
				<li class="page-item <?php echo esc_attr( 1 === $current_page ? 'active' : '' ); ?>"><a class="page-link" href="<?php echo esc_attr( $page_1_link ); ?>"><?php echo esc_attr( $page_1_number ); ?></a></li>
				<li class="page-item <?php echo esc_attr( 1 === $current_page ? '' : 'active' ); ?>"><a class="page-link" href="<?php echo esc_attr( $page_2_link ); ?>"><?php echo esc_attr( $page_2_number ); ?></a></li>
				<?php if ( ! $is_last_page ) { ?>
				<li class="page-item"><a class="page-link" href="<?php echo esc_attr( $page_3_link ); ?>"><?php echo esc_attr( $page_3_number ); ?></a></li>
				<li class="page-item">
					<a class="page-link" href="?gallery_page=<?php echo esc_attr( $current_page + 1 ); ?>" aria-label="Next">
						<span aria-hidden="true">&raquo;</span>
					</a>
				</li>
				<?php } ?>
			</ul>
		</nav>
		<?php
	}
}
