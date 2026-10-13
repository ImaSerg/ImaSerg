<?php
/**
 * Light-theme draft of a megamenu page: native Elementor hero (badge, H1 with accent,
 * lead, two buttons, three stat cards, photo panel with three numbered callouts)
 * + the source page's remaining sections recoloured to the light palette.
 */

if ( ! function_exists( 'bz_ld_build' ) ) {

	define( 'BZ_LD', [
		'bg'      => '#f2f1ec',
		'card'    => '#ffffff',
		'ink'     => '#1c2416',
		'body'    => '#5b6152',
		'accent'  => '#4f7a3a',
		'accentH' => '#3f6630',
		'line'    => '#e3e1d6',
		'soft'    => '#e8ecd9',
		'panel'   => '#ecebe1',
		'lime'    => '#dde46a',
		'h'       => "'Golos Text', Montserrat, sans-serif",
	] );

	function bz_ld_id() {
		return substr( md5( uniqid( '', true ) . wp_rand() ), 0, 7 );
	}

	function bz_ld_px( $t, $r = null, $b = null, $l = null ) {
		$r = $r ?? $t;
		$b = $b ?? $t;
		$l = $l ?? $r;
		return [ 'unit' => 'px', 'top' => (string) $t, 'right' => (string) $r, 'bottom' => (string) $b, 'left' => (string) $l, 'isLinked' => ( $t === $r && $r === $b && $b === $l ) ];
	}

	function bz_ld_size( $n, $u = 'px' ) {
		return [ 'unit' => $u, 'size' => $n, 'sizes' => [] ];
	}

	function bz_ld_box( $s, $kids, $inner = true ) {
		return [ 'id' => bz_ld_id(), 'elType' => 'container', 'isInner' => $inner, 'settings' => array_merge( [ 'container_type' => 'flex', 'content_width' => 'full', 'padding' => bz_ld_px( 0 ) ], $s ), 'elements' => $kids ];
	}

	function bz_ld_w( $type, $s ) {
		return [ 'id' => bz_ld_id(), 'elType' => 'widget', 'widgetType' => $type, 'settings' => $s, 'elements' => [] ];
	}

	function bz_ld_text( $html, $size = 16, $color = null, $extra = [] ) {
		return bz_ld_w( 'text-editor', array_merge( [
			'editor'                 => $html,
			'typography_typography'  => 'custom',
			'typography_font_family' => BZ_LD['h'],
			'typography_font_size'   => bz_ld_size( $size ),
			'typography_line_height' => bz_ld_size( 1.55, 'em' ),
			'text_color'             => $color ?? BZ_LD['body'],
		], $extra ) );
	}

	function bz_ld_heading( $title, $tag, $size, $extra = [] ) {
		return bz_ld_w( 'heading', array_merge( [
			'title'                  => $title,
			'header_size'            => $tag,
			'typography_typography'  => 'custom',
			'typography_font_family' => BZ_LD['h'],
			'typography_font_size'   => bz_ld_size( $size ),
			'typography_font_weight' => '800',
			'typography_line_height' => bz_ld_size( 1.1, 'em' ),
			'title_color'            => BZ_LD['ink'],
		], $extra ) );
	}

	function bz_ld_button( $text, $link, $primary, $attrs = '' ) {
		$s = [
			'text'                   => $text,
			'link'                   => [ 'url' => $link, 'is_external' => '', 'nofollow' => '' ],
			'typography_typography'  => 'custom',
			'typography_font_family' => BZ_LD['h'],
			'typography_font_size'   => bz_ld_size( 16 ),
			'typography_font_weight' => '700',
			'border_radius'          => bz_ld_px( 999 ),
			'text_padding'           => bz_ld_px( 17, 28 ),
			'background_background'  => 'classic',
		];
		if ( $primary ) {
			$s += [ 'background_color' => BZ_LD['accent'], 'button_text_color' => '#ffffff', 'button_background_hover_color' => BZ_LD['accentH'], 'hover_color' => '#ffffff' ];
		} else {
			$s += [ 'background_color' => '#ffffff', 'button_text_color' => BZ_LD['ink'], 'border_border' => 'solid', 'border_width' => bz_ld_px( 1 ), 'border_color' => '#d9d9d0', 'button_background_hover_color' => BZ_LD['soft'], 'hover_color' => BZ_LD['ink'] ];
		}
		if ( $attrs ) {
			$s['_attributes'] = $attrs;
		}
		return bz_ld_w( 'button', $s );
	}

	function bz_ld_stat( $big, $small ) {
		return bz_ld_box( [
			'flex_direction'        => 'column',
			'flex_gap'              => [ 'column' => '2', 'row' => '2', 'isLinked' => true, 'unit' => 'px', 'size' => 2 ],
			'flex_grow'             => 1,
			'_flex_size'            => 'grow',
			'width'                 => bz_ld_size( 31, '%' ),
			'width_mobile'          => bz_ld_size( 100, '%' ),
			'padding'               => bz_ld_px( 16, 18 ),
			'border_border'         => 'solid',
			'border_width'          => bz_ld_px( 1 ),
			'border_color'          => BZ_LD['line'],
			'border_radius'         => bz_ld_px( 14 ),
			'background_background' => 'classic',
			'background_color'      => '#ffffff',
		], [
			bz_ld_heading( $big, 'div', 24, [ 'title_color' => BZ_LD['accent'] ] ),
			bz_ld_text( $small, 13.5, BZ_LD['body'], [ 'typography_line_height' => bz_ld_size( 1.3, 'em' ) ] ),
		] );
	}

	function bz_ld_callout( $num, $title, $sub, $top, $lime = false ) {
		return bz_ld_box( [
			'flex_direction'        => 'row',
			'flex_align_items'      => 'center',
			'flex_gap'              => [ 'column' => '12', 'row' => '12', 'isLinked' => true, 'unit' => 'px', 'size' => 12 ],
			'width'                 => bz_ld_size( 268 ),
			'padding'               => bz_ld_px( 12, 18, 12, 12 ),
			'background_background' => 'classic',
			'background_color'      => $lime ? BZ_LD['lime'] : '#ffffff',
			'border_radius'         => bz_ld_px( 18 ),
			'box_shadow_box_shadow_type' => 'yes',
			'box_shadow_box_shadow' => [ 'horizontal' => 0, 'vertical' => 10, 'blur' => 30, 'spread' => 0, 'color' => 'rgba(28,36,22,0.10)' ],
			'position'              => 'absolute',
			'_position'             => 'absolute',
			'_offset_orientation_h' => 'start',
			'_offset_x'             => bz_ld_size( -28 ),
			'_offset_orientation_v' => 'start',
			'_offset_y'             => bz_ld_size( $top ),
			'z_index'               => 2,
			'_z_index'              => 2,
			'hide_mobile'           => 'hidden-mobile',
		], [
			bz_ld_box( [
				'width'                 => bz_ld_size( 38 ),
				'min_height'            => bz_ld_size( 38 ),
				'flex_justify_content'  => 'center',
				'flex_align_items'      => 'center',
				'background_background' => 'classic',
				'background_color'      => $lime ? '#ffffff' : BZ_LD['soft'],
				'border_radius'         => bz_ld_px( 10 ),
				'flex_shrink'           => 0,
				'_flex_size'            => 'none',
			], [ bz_ld_text( $num, 13, BZ_LD['ink'], [ 'typography_font_weight' => '800', 'align' => 'center', 'typography_line_height' => bz_ld_size( 1, 'em' ) ] ) ] ),
			bz_ld_box( [ 'flex_direction' => 'column', 'flex_gap' => [ 'column' => '1', 'row' => '1', 'isLinked' => true, 'unit' => 'px', 'size' => 1 ] ], [
				bz_ld_heading( $title, 'div', 15.5 ),
				bz_ld_text( $sub, 12.5, BZ_LD['body'], [ 'typography_line_height' => bz_ld_size( 1.25, 'em' ) ] ),
			] ),
		] );
	}

	/**
	 * $h: [crumbs_html, badge, h1_plain, h1_accent, lead, stats=[[big,small]x3], callouts=[[title,sub]x3], image_id, image_alt]
	 */
	function bz_ld_hero( array $h ) {
		$left = bz_ld_box( [
			'flex_direction' => 'column',
			'flex_gap'       => [ 'column' => '24', 'row' => '24', 'isLinked' => true, 'unit' => 'px', 'size' => 24 ],
			'width'          => bz_ld_size( 50, '%' ),
			'width_tablet'   => bz_ld_size( 100, '%' ),
			'width_mobile'   => bz_ld_size( 100, '%' ),
		], [
			// Badge: a link-less Button widget hugs its text and stays editable as a normal button.
			bz_ld_w( 'button', [
				'text'                      => $h['badge'],
				'link'                      => [ 'url' => '', 'is_external' => '', 'nofollow' => '' ],
				'selected_icon'             => [ 'value' => 'fas fa-cube', 'library' => 'fa-solid' ],
				'icon_indent'               => bz_ld_size( 8 ),
				'typography_typography'     => 'custom',
				'typography_font_family'    => BZ_LD['h'],
				'typography_font_size'      => bz_ld_size( 13 ),
				'typography_font_weight'    => '800',
				'typography_text_transform' => 'uppercase',
				'typography_letter_spacing' => bz_ld_size( 1.2 ),
				'button_text_color'         => BZ_LD['ink'],
				'hover_color'               => BZ_LD['ink'],
				'background_background'     => 'classic',
				'background_color'          => BZ_LD['soft'],
				'button_background_hover_color' => BZ_LD['soft'],
				'border_radius'             => bz_ld_px( 999 ),
				'text_padding'              => bz_ld_px( 8, 16, 8, 14 ),
				'_css_classes'              => 'bz-badge',
			] ),
			bz_ld_heading( esc_html( $h['h1_plain'] ) . ' <span style="color:' . BZ_LD['accent'] . '">' . esc_html( $h['h1_accent'] ) . '</span>', 'h1', 64, [
				'typography_font_size_tablet' => bz_ld_size( 48 ),
				'typography_font_size_mobile' => bz_ld_size( 38 ),
				'typography_line_height'      => bz_ld_size( 1.02, 'em' ),
				'typography_letter_spacing'   => bz_ld_size( -1.5 ),
			] ),
			bz_ld_text( '<p>' . esc_html( $h['lead'] ) . '</p>', 18, BZ_LD['body'], [ 'typography_line_height' => bz_ld_size( 1.6, 'em' ), 'typography_font_size_mobile' => bz_ld_size( 16 ) ] ),
			bz_ld_box( [ 'flex_direction' => 'row', 'flex_wrap' => 'wrap', 'flex_gap' => [ 'column' => '12', 'row' => '12', 'isLinked' => true, 'unit' => 'px', 'size' => 12 ] ], [
				bz_ld_button( 'Рассчитать стоимость тиража →', '#', true, 'data-bz-open-popup|1' ),
				bz_ld_button( 'Консультация', 'https://wa.me/79057167623', false ),
			] ),
			bz_ld_box( [ 'flex_direction' => 'row', 'flex_wrap' => 'wrap', 'flex_gap' => [ 'column' => '12', 'row' => '12', 'isLinked' => true, 'unit' => 'px', 'size' => 12 ] ],
				array_map( fn( $s ) => bz_ld_stat( $s[0], $s[1] ), $h['stats'] ) ),
		] );

		$tops  = [ 40, 160, 280 ];
		$calls = [];
		foreach ( $h['callouts'] as $i => $c ) {
			$calls[] = bz_ld_callout( sprintf( '%02d', $i + 1 ), $c[0], $c[1], $tops[ $i ], 2 === $i );
		}
		$right = bz_ld_box( [
			'flex_direction'        => 'column',
			'flex_justify_content'  => 'center',
			'flex_align_items'      => 'flex-end',
			'width'                 => bz_ld_size( 47, '%' ),
			'width_tablet'          => bz_ld_size( 100, '%' ),
			'min_height_tablet'     => bz_ld_size( 420 ),
			'padding_tablet'        => bz_ld_px( 30, 30, 30, 250 ),
			'overflow'              => 'visible', // callouts overhang the left edge; page CSS also forces overflow:visible
			'width_mobile'          => bz_ld_size( 100, '%' ),
			'min_height'            => bz_ld_size( 480 ),
			'min_height_mobile'     => bz_ld_size( 260 ),
			'padding'               => bz_ld_px( 30, 30, 30, 120 ),
			'padding_mobile'        => bz_ld_px( 20 ),
			'background_background' => 'classic',
			'background_color'      => BZ_LD['panel'],
			'border_radius'         => bz_ld_px( 28 ),
		], array_merge( $calls, [
			bz_ld_w( 'image', [
				'image'               => [ 'id' => $h['image_id'], 'url' => wp_get_attachment_url( $h['image_id'] ), 'alt' => $h['image_alt'], 'source' => 'library' ],
				'image_size'          => 'large',
				'width'               => bz_ld_size( 100, '%' ),
				'_css_classes'        => 'bz-hero-photo',
			] ),
		] ) );

		$card = bz_ld_box( [
			'flex_direction'        => 'column',
			'flex_gap'              => [ 'column' => '28', 'row' => '28', 'isLinked' => true, 'unit' => 'px', 'size' => 28 ],
			'padding'               => bz_ld_px( 36, 40, 44, 40 ),
			'padding_mobile'        => bz_ld_px( 24, 18, 28, 18 ),
			'background_background' => 'classic',
			'background_color'      => BZ_LD['card'],
			'border_radius'         => bz_ld_px( 32 ),
			'border_radius_mobile'  => bz_ld_px( 22 ),
		], [
			bz_ld_text( $h['crumbs'], 14, BZ_LD['ink'] ),
			bz_ld_box( [
				'flex_direction'        => 'row',
				'flex_direction_tablet' => 'column',
				'flex_align_items_tablet' => 'stretch',
				'flex_direction_mobile' => 'column',
				'flex_justify_content'  => 'space-between',
				'flex_align_items'      => 'center',
				'flex_gap'              => [ 'column' => '40', 'row' => '28', 'isLinked' => false, 'unit' => 'px' ],
			], [ $left, $right ] ),
		] );

		return [
			'id'       => bz_ld_id(),
			'elType'   => 'container',
			'isInner'  => false,
			'settings' => [
				'container_type' => 'flex',
				'content_width'  => 'boxed',
				'boxed_width'    => bz_ld_size( 1440 ),
				'padding'        => bz_ld_px( 24, 20, 8, 20 ),
				'padding_mobile' => bz_ld_px( 12, 10, 4, 10 ),
			],
			'elements' => [ $card ],
		];
	}

	// Recolour an existing (dark) section tree to the light palette.
	function bz_ld_recolor( $el ) {
		$map = [
			'#2e6053' => BZ_LD['bg'], '#275349' => BZ_LD['bg'], '#17362e' => BZ_LD['card'], '#c2d7d0' => BZ_LD['body'],
			'#6fd0ac' => BZ_LD['accent'], '#e9f3ef' => BZ_LD['ink'], '#487a6c' => BZ_LD['line'], '#3c6b5e' => BZ_LD['line'],
			'#34675a' => BZ_LD['soft'], '#4f8b78' => '#c9cdb8', '#38957c' => BZ_LD['accent'], 'rgba(255,255,255,0.22)' => '#d9d9d0',
		];
		$fix = function ( &$arr ) use ( &$fix, $map ) {
			foreach ( $arr as $k => &$v ) {
				if ( is_array( $v ) ) {
					$fix( $v );
					continue;
				}
				if ( ! is_string( $v ) ) {
					continue;
				}
				$lv = strtolower( $v );
				if ( 'editor' === $k || 'tab_content' === $k || 'title' === $k ) {
					$v = str_ireplace( [ 'color:#ffffff', 'color: #ffffff', 'color:#fff;' ], [ 'color:' . BZ_LD['ink'], 'color:' . BZ_LD['ink'], 'color:' . BZ_LD['ink'] . ';' ], $v );
					$v = str_ireplace( array_keys( $map ), array_values( $map ), $v );
				} elseif ( isset( $map[ $lv ] ) ) {
					$v = $map[ $lv ];
				} elseif ( ( '#ffffff' === $lv || '#fff' === $lv ) && false === strpos( (string) $k, 'background' ) ) {
					$v = BZ_LD['ink'];
				} elseif ( '#05100c' === $lv && 'button_text_color' === $k ) {
					$v = '#ffffff';
				}
			}
			unset( $v );
		};
		$fix( $el );
		// Buttons: dark text on the now-dark-green accent → white.
		$walk = function ( &$e ) use ( &$walk ) {
			if ( 'button' === ( $e['widgetType'] ?? '' ) && BZ_LD['accent'] === ( $e['settings']['background_color'] ?? '' ) ) {
				$e['settings']['button_text_color'] = '#ffffff';
			}
			foreach ( $e['elements'] ?? [] as &$c ) {
				$walk( $c );
			}
			unset( $c );
		};
		$walk( $el );
		return $el;
	}

	/** Build the draft from $src_id; returns new post id. */
	function bz_ld_build( $src_id, $title, array $hero ) {
		$d   = json_decode( get_post_meta( $src_id, '_elementor_data', true ), true );
		$out = [ bz_ld_hero( $hero ) ];
		foreach ( array_slice( $d, 2 ) as $sec ) { // drop old breadcrumbs bar + old hero
			$out[] = bz_ld_recolor( $sec );
		}
		$id = wp_insert_post( wp_slash( [ 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => $title, 'post_content' => '' ] ) );
		foreach ( [ '_wp_page_template', 'site-sidebar-layout', 'site-post-title', 'ast-featured-img', 'ast-site-content-layout', 'site-content-layout', 'ast-main-header-display', 'theme-transparent-header-meta' ] as $k ) {
			$v = get_post_meta( $src_id, $k, true );
			if ( '' !== $v ) {
				update_post_meta( $id, $k, $v );
			}
		}
		update_post_meta( $id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $id, '_elementor_version', ELEMENTOR_VERSION );
		update_post_meta( $id, '_elementor_page_settings', [ 'hide_title' => 'yes', 'background_background' => 'classic', 'background_color' => BZ_LD['bg'] ] );
		\Elementor\Plugin::$instance->documents->get( $id, false )->save( [ 'elements' => $out ] );
		return $id;
	}
}
