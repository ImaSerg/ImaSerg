<?php
/**
 * Layout "A" for the megamenu main-text section: keep heading + intro next to the photo,
 * move the H3 sub-sections into a full-width grid of native Elementor cards
 * (container > heading h3 + text-editor), with closing lines (срок, «Смотрите также», cross-links) under the grid.
 */

if ( ! function_exists( 'bz_layout_a' ) ) {

	function bz_la_id() {
		return substr( md5( uniqid( '', true ) . wp_rand() ), 0, 7 );
	}

	function bz_la_text( $html ) {
		return [
			'id'         => bz_la_id(),
			'elType'     => 'widget',
			'widgetType' => 'text-editor',
			'settings'   => [
				'editor'                 => $html,
				'typography_typography'  => 'custom',
				'typography_font_size'   => [ 'unit' => 'px', 'size' => 14.5, 'sizes' => [] ],
				'typography_line_height' => [ 'unit' => 'em', 'size' => 1.65, 'sizes' => [] ],
				'typography_font_family' => 'Montserrat, sans-serif',
				'text_color'             => '#c2d7d0',
			],
			'elements'   => [],
		];
	}

	function bz_la_heading( $title ) {
		return [
			'id'         => bz_la_id(),
			'elType'     => 'widget',
			'widgetType' => 'heading',
			'settings'   => [
				'title'                       => $title,
				'header_size'                 => 'h3',
				'typography_typography'       => 'custom',
				'typography_font_family'      => "'Golos Text', Montserrat, sans-serif",
				'typography_font_size'        => [ 'unit' => 'px', 'size' => 20, 'sizes' => [] ],
				'typography_font_size_mobile' => [ 'unit' => 'px', 'size' => 18, 'sizes' => [] ],
				'typography_font_weight'      => '700',
				'typography_line_height'      => [ 'unit' => 'em', 'size' => 1.25, 'sizes' => [] ],
				'title_color'                 => '#ffffff',
			],
			'elements'   => [],
		];
	}

	function bz_la_container( $settings, $children ) {
		return [
			'id'       => bz_la_id(),
			'elType'   => 'container',
			'isInner'  => true,
			'settings' => array_merge( [ 'container_type' => 'flex', 'content_width' => 'full' ], $settings ),
			'elements' => $children,
		];
	}

	// Split the rich main-text HTML into [ [title, html], ... ] cards and a footer html.
	function bz_la_split( $html ) {
		$doc = new DOMDocument();
		libxml_use_internal_errors( true );
		$doc->loadHTML( '<?xml encoding="utf-8"?><div id="r">' . $html . '</div>' );
		libxml_clear_errors();
		$root  = $doc->getElementById( 'r' );
		$nodes = [];
		foreach ( $root->childNodes as $n ) {
			if ( XML_ELEMENT_NODE === $n->nodeType ) {
				$nodes[] = $n;
			}
		}
		// Footer: trailing paragraphs that are closing lines rather than part of the last sub-section.
		$footer = [];
		$re     = '/^(Срок|Сроки|Изготовление —|Смотрите также|Если |Для узких|Для отдельных|Для упаковки|Для сервисов|Для производителей|Для сезонных|Для подарочных|Для чайных|Для лекарственных|Для таблеток|Страница «)/u';
		while ( count( $nodes ) > 1 ) {
			$last = end( $nodes );
			$prev = $nodes[ count( $nodes ) - 2 ];
			// Never take the only content of the last sub-section.
			if ( 'p' === $last->nodeName && 'h3' !== $prev->nodeName && preg_match( $re, trim( $last->textContent ) ) ) {
				array_unshift( $footer, array_pop( $nodes ) );
			} else {
				break;
			}
		}
		$cards = [];
		foreach ( $nodes as $n ) {
			if ( 'h3' === $n->nodeName ) {
				$cards[] = [ trim( $n->textContent ), '' ];
			} elseif ( $cards ) {
				$cards[ count( $cards ) - 1 ][1] .= $doc->saveHTML( $n );
			}
		}
		$f = '';
		foreach ( $footer as $n ) {
			$f .= $doc->saveHTML( $n );
		}
		return [ $cards, $f ];
	}

	/**
	 * $d: page _elementor_data (array). Returns [new $d, log] or [null, error].
	 */
	function bz_layout_a( array $d ) {
		// Locate the row holding heading + 2 text-editors (intro, rich text with <h3>) + image column.
		foreach ( $d as $si => $sec ) {
			foreach ( $sec['elements'] ?? [] as $ri => $row ) {
				$cols = $row['elements'] ?? [];
				if ( 2 !== count( $cols ) ) {
					continue;
				}
				$left = $cols[0];
				$tw   = [];
				foreach ( $left['elements'] ?? [] as $wi => $w ) {
					if ( 'text-editor' === ( $w['widgetType'] ?? '' ) ) {
						$tw[ $wi ] = $w;
					}
				}
				$rich = null;
				foreach ( $tw as $wi => $w ) {
					if ( false !== strpos( $w['settings']['editor'] ?? '', '<h3' ) ) {
						$rich = $wi;
					}
				}
				if ( null === $rich || 'image' !== ( $cols[1]['elements'][0]['widgetType'] ?? '' ) ) {
					continue;
				}
				list( $cards, $footer ) = bz_la_split( $left['elements'][ $rich ]['settings']['editor'] );
				if ( count( $cards ) < 2 ) {
					return [ null, 'too few sub-sections: ' . count( $cards ) ];
				}
				$n     = count( $cards );
				$items = [];
				foreach ( $cards as $c ) {
					$items[] = bz_la_container(
						[
							'flex_direction'       => 'column',
							'flex_gap'             => [ 'column' => '10', 'row' => '10', 'isLinked' => true, 'unit' => 'px', 'size' => 10 ],
							'padding'              => [ 'unit' => 'px', 'top' => '22', 'right' => '22', 'bottom' => '18', 'left' => '22', 'isLinked' => false ],
							'background_background' => 'classic',
							'background_color'     => '#17362e',
							'border_border'        => 'solid',
							'border_width'         => [ 'unit' => 'px', 'top' => '1', 'right' => '1', 'bottom' => '1', 'left' => '1', 'isLinked' => true ],
							'border_color'         => '#3c6b5e',
							'border_radius'        => [ 'unit' => 'px', 'top' => '14', 'right' => '14', 'bottom' => '14', 'left' => '14', 'isLinked' => true ],
						],
						[ bz_la_heading( $c[0] ), bz_la_text( $c[1] ) ]
					);
				}
				$grid = bz_la_container(
					[
						'container_type'           => 'grid',
						// 4 sub-sections read better as 2×2 than 3+1.
						'grid_columns_grid'        => [ 'unit' => 'fr', 'size' => 4 === $n ? 2 : 3, 'sizes' => [] ],
						'grid_columns_grid_tablet' => [ 'unit' => 'fr', 'size' => 2, 'sizes' => [] ],
						'grid_columns_grid_mobile' => [ 'unit' => 'fr', 'size' => 1, 'sizes' => [] ],
						'grid_rows_grid'           => [ 'unit' => 'custom', 'size' => 'auto', 'sizes' => [] ],
						'grid_rows_grid_tablet'    => [ 'unit' => 'custom', 'size' => 'auto', 'sizes' => [] ],
						'grid_rows_grid_mobile'    => [ 'unit' => 'custom', 'size' => 'auto', 'sizes' => [] ],
						'grid_gaps'                => [ 'column' => '18', 'row' => '18', 'isLinked' => true, 'unit' => 'px' ],
						'grid_auto_flow'           => 'row',
						'padding'                  => [ 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true ],
					],
					$items
				);
				$kids = [ $grid ];
				if ( '' !== $footer ) {
					$kids[] = bz_la_text( $footer );
				}
				$wrap = bz_la_container(
					[
						'flex_direction' => 'column',
						'flex_gap'       => [ 'column' => '22', 'row' => '22', 'isLinked' => true, 'unit' => 'px', 'size' => 22 ],
						'width'          => [ 'unit' => '%', 'size' => 100, 'sizes' => [] ],
						'_css_classes'   => 'bz-subsections',
						'padding'        => [ 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true ],
					],
					$kids
				);
				// Remove the rich widget from the left column, append the full-width block to the row.
				array_splice( $d[ $si ]['elements'][ $ri ]['elements'][0]['elements'], $rich, 1 );
				$d[ $si ]['elements'][ $ri ]['elements'][]                       = $wrap;
				$d[ $si ]['elements'][ $ri ]['settings']['flex_align_items']     = 'center';
				$d[ $si ]['elements'][ $ri ]['settings']['flex_wrap']            = 'wrap';
				return [ $d, [ 'section' => $si, 'cards' => $n, 'footer' => '' !== $footer ] ];
			}
		}
		return [ null, 'main text row not found (already converted?)' ];
	}
}
