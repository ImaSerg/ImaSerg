<?php
/**
 * "MoneyHub" palette variant: copy a light-design page into a new draft and remap it to
 * dark green-navy + lime, with a dark hero card and condensed uppercase headings.
 * Everything stays native Elementor settings + page Custom CSS.
 */

if ( ! function_exists( 'bz_mh_build' ) ) {

	function bz_mh_map_str( $v, array $map ) {
		return str_ireplace( array_keys( $map ), array_values( $map ), $v );
	}

	function bz_mh_walk( array &$el, callable $fn, $skip = [] ) {
		if ( in_array( $el['id'] ?? '', $skip, true ) ) {
			return;
		}
		$fn( $el );
		if ( empty( $el['elements'] ) ) {
			return;
		}
		// Iterate the real array by reference (`?? []` would hand foreach a temporary copy).
		foreach ( $el['elements'] as &$c ) {
			bz_mh_walk( $c, $fn, $skip );
		}
		unset( $c );
	}

	function bz_mh_build( $src_id, $title, $hero_card_id, $photo_panel_id ) {
		$d  = json_decode( get_post_meta( $src_id, '_elementor_data', true ), true );
		$ps = get_post_meta( $src_id, '_elementor_page_settings', true );
		$sz = fn( $n, $u = 'px' ) => [ 'unit' => $u, 'size' => $n, 'sizes' => [] ];

		// 1. Dark hero card (everything except the photo panel), using the light-design source colours.
		$hero_map = [ '#1c2416' => '#ffffff', '#5b6152' => '#b9c6bf', '#9aa08f' => '#6f7f78', '#4f7a3a' => '#c8f031', '#3f6630' => '#b5e01f', '#e3e1d6' => 'rgba(255,255,255,0.12)', '#d9d9d0' => 'rgba(255,255,255,0.35)' ];
		foreach ( $d as &$sec ) {
			bz_mh_walk( $sec, function ( &$e ) use ( $hero_card_id, $photo_panel_id, $hero_map, $sz ) {
				if ( ( $e['id'] ?? '' ) !== $hero_card_id ) {
					return;
				}
				$e['settings'] = array_merge( $e['settings'], [
					'background_background' => 'gradient', 'background_color' => '#163027', 'background_color_stop' => $sz( 0, '%' ),
					'background_color_b' => '#0b1712', 'background_color_b_stop' => $sz( 100, '%' ), 'background_gradient_type' => 'linear', 'background_gradient_angle' => $sz( 145, 'deg' ),
				] );
				foreach ( $e['elements'] as &$child ) {
					bz_mh_walk( $child, function ( &$x ) use ( $hero_map ) {
						$s = &$x['settings'];
						$wt = $x['widgetType'] ?? '';
						if ( 'button' === $wt ) {
							$bg = strtolower( $s['background_color'] ?? '' );
							if ( '#4f7a3a' === $bg ) { // primary
								$s['background_color'] = '#c8f031'; $s['button_text_color'] = '#10221c'; $s['button_background_hover_color'] = '#b5e01f'; $s['hover_color'] = '#10221c';
							} elseif ( '#e8ecd9' === $bg ) { // badge
								$s['background_color'] = 'rgba(200,240,49,0.12)'; $s['button_text_color'] = '#c8f031'; $s['button_background_hover_color'] = 'rgba(200,240,49,0.12)'; $s['hover_color'] = '#c8f031';
							} else { // secondary
								$s['background_color'] = 'rgba(255,255,255,0)'; $s['button_text_color'] = '#ffffff'; $s['border_color'] = 'rgba(255,255,255,0.35)'; $s['button_background_hover_color'] = 'rgba(255,255,255,0.08)'; $s['hover_color'] = '#ffffff';
							}
							return;
						}
						if ( 'container' === ( $x['elType'] ?? '' ) && '#ffffff' === strtolower( $s['background_color'] ?? '' ) ) {
							$s['background_color'] = 'rgba(255,255,255,0.05)'; // stat cards
						}
						foreach ( $s as $k => &$v ) {
							if ( is_string( $v ) && 'background_color' !== $k ) {
								$v = bz_mh_map_str( $v, $hero_map );
							}
						}
						unset( $v, $s );
					}, [ $photo_panel_id ] );
				}
				unset( $child );
			} );
		}
		unset( $sec );

		// 2. Global palette remap (light design -> MoneyHub).
		$map = [
			'#f2f1ec' => '#f4f5f1', '#ecebe1' => '#eef3ea', '#e8ecd9' => '#edf2e4', '#eef2e2' => '#f1f5ea', '#f7f6f1' => '#f6f8f3', '#f8f7f1' => '#f7f9f4',
			'#4f7a3a' => '#10221c', '#3f6630' => '#1d3a30', '#2f4f24' => '#10221c', '#263f1c' => '#0b1712', '#dde46a' => '#c8f031', '#c5d35a' => '#b5e01f',
			'#f4f7d6' => '#f0fbc4', '#1c2416' => '#10221c', '#5b6152' => '#5f6b66', '#3a4230' => '#2b3833', '#e3e1d6' => '#e2e6e1', '#ebe9df' => '#e6eae4',
			'#e6e4d9' => '#e4e8e2', '#c9cdb8' => '#c5ccc7', '#cfd3bf' => '#cdd5cf', '#d9d9d0' => '#d8ddd8', '#9aa08f' => '#94a09a', '#eef2e6' => '#e9efec',
			'#d6dc8a' => '#d3ec6a', '#2f3a24' => '#10221c', 'rgba(79,122,58' => 'rgba(16,34,28', 'rgba(28,36,22' => 'rgba(16,34,28', 'rgba(221,228,106' => 'rgba(200,240,49',
		];
		$json = bz_mh_map_str( wp_json_encode( $d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), $map );
		$d    = json_decode( $json, true );
		$ps['custom_css'] = bz_mh_map_str( $ps['custom_css'] ?? '', $map );
		$ps['background_color'] = '#f4f5f1';

		// 3. Dark band ("Этапы") flat gradient fix + condensed uppercase headings.
		foreach ( $d as &$sec ) {
			if ( 'gradient' === ( $sec['settings']['background_background'] ?? '' ) && '#10221c' === ( $sec['settings']['background_color'] ?? '' ) ) {
				$sec['settings']['background_color']   = '#163027';
				$sec['settings']['background_color_b'] = '#0b1712';
			}
			bz_mh_walk( $sec, function ( &$e ) use ( $sz ) {
				if ( 'heading' !== ( $e['widgetType'] ?? '' ) ) {
					return;
				}
				$s    = &$e['settings'];
				$size = $s['typography_font_size']['size'] ?? 0;
				if ( $size >= 30 ) {
					$s['typography_font_family']    = 'Oswald';
					$s['typography_font_weight']    = '700';
					$s['typography_text_transform'] = 'uppercase';
					$s['typography_letter_spacing'] = $sz( 0 );
					$s['typography_line_height']    = $sz( 1.08, 'em' );
					$s['typography_font_size']      = $sz( $size >= 60 ? 68 : 44 );
				}
				unset( $s );
			} );
		}
		unset( $sec );

		// 4. Header/menu accents for this palette (page-scoped Custom CSS).
		$ps['custom_css'] .= "\n/* MoneyHub variant: dark menu pill, lime actions */\n"
			. "selector [data-id=\"adc6ae0\"]{background:#10221c!important;border-color:#10221c!important}\n"
			. "selector [data-catalog-tab] .elementor-widget-text-editor, selector [data-catalog-tab] .elementor-widget-text-editor *{color:#ffffff!important}\n"
			. "selector [data-catalog-menu] [data-catalog-tab]:hover, selector [data-catalog-menu] [data-catalog-tab].bz-tab-active{background-color:#c8f031!important;box-shadow:none!important}\n"
			. "selector [data-catalog-menu] [data-catalog-tab]:hover *, selector [data-catalog-menu] [data-catalog-tab].bz-tab-active *{color:#10221c!important;text-decoration-color:#10221c!important}\n"
			. "selector [data-id=\"bdc068d\"] .elementor-button{background:#c8f031!important;color:#10221c!important}\n"
			. "selector [data-id=\"bdc068d\"] .elementor-button *{color:#10221c!important}\n"
			. "selector [data-id=\"0f9355e\"] .elementor-icon{background:#c8f031!important;color:#10221c!important}\n"
			. "selector [data-id=\"0f9355e\"] .elementor-icon svg{fill:#10221c!important}\n";

		$id = wp_insert_post( wp_slash( [ 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => $title, 'post_content' => '' ] ) );
		foreach ( [ '_wp_page_template', 'site-sidebar-layout', 'site-post-title', 'ast-featured-img', 'ast-site-content-layout', 'site-content-layout' ] as $k ) {
			$v = get_post_meta( $src_id, $k, true );
			if ( '' !== $v ) {
				update_post_meta( $id, $k, $v );
			}
		}
		update_post_meta( $id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $id, '_elementor_version', ELEMENTOR_VERSION );
		update_post_meta( $id, '_elementor_page_settings', $ps );
		\Elementor\Plugin::$instance->documents->get( $id, false )->save( [ 'elements' => $d ] );
		update_post_meta( $id, '_elementor_page_settings', $ps );
		\Elementor\Core\Files\CSS\Post::create( $id )->update();
		return $id;
	}
}
