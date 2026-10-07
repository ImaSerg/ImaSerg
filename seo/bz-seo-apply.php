<?php
/**
 * Applies megamenu SEO texts (seo_pages.json) to a template page's _elementor_data.
 * Surgical: only text settings of existing widgets change; extra text widgets in
 * the hero / main-text sections are removed. Returns [new elements, change log].
 */

if ( ! function_exists( 'bz_seo_apply' ) ) {

	function bz_seo_h3( $t ) {
		return '<h3 style="font-family:\'Golos Text\',Montserrat,sans-serif;font-size:22px;font-weight:700;color:#ffffff;line-height:1.25;margin:22px 0 10px">' . esc_html( $t ) . '</h3>';
	}

	function bz_seo_render( $blocks ) {
		$html = '';
		foreach ( $blocks as $b ) {
			if ( 'h' === $b[0] ) {
				$html .= bz_seo_h3( $b[1] );
			} elseif ( 'p' === $b[0] ) {
				$html .= '<p>' . esc_html( $b[1] ) . '</p>';
			} else {
				$html .= '<ul style="padding-left:20px;margin:0 0 14px">';
				foreach ( $b[1] as $li ) {
					$html .= '<li>' . esc_html( $li ) . '</li>';
				}
				$html .= '</ul>';
			}
		}
		return $html;
	}

	// Wrap the first plain-text occurrence of each «anchor» in a link (each link used once per page).
	function bz_seo_link( $html, &$links, $self ) {
		foreach ( $links as $k => $l ) {
			list( $anchor, $url ) = $l;
			if ( trim( $url, '/' ) === $self ) {
				unset( $links[ $k ] );
				continue;
			}
			$re = '/(?<![\p{L}\d])(' . preg_quote( esc_html( $anchor ), '/' ) . ')(?![\p{L}\d])(?![^<]*<\/(?:a|h3)>)(?![^<]*>)/iu';
			$new = preg_replace( $re, '<a href="' . esc_url( home_url( $url ) ) . '" style="color:#6fd0ac;text-decoration:underline">$1</a>', $html, 1, $n );
			if ( $n ) {
				$html = $new;
				unset( $links[ $k ] );
			}
		}
		return $html;
	}

	function bz_seo_sentences( $text, $groups ) {
		$s = preg_split( '/(?<=[.!?])\s+(?=[\p{Lu}«])/u', trim( $text ) );
		$per = (int) ceil( count( $s ) / $groups );
		$out = [];
		for ( $i = 0; $i < $groups; $i++ ) {
			$chunk = array_slice( $s, $i * $per, $per );
			if ( $chunk ) {
				$out[] = implode( ' ', $chunk );
			}
		}
		return $out;
	}

	// Collect direct-ish widget list (depth-first) of a section.
	function bz_seo_widgets( $el ) {
		$o = [];
		foreach ( $el['elements'] ?? [] as $c ) {
			if ( 'widget' === ( $c['elType'] ?? '' ) ) {
				$o[] = $c;
			} else {
				$o = array_merge( $o, bz_seo_widgets( $c ) );
			}
		}
		return $o;
	}

	function bz_seo_patch( &$els, $set, $remove ) {
		$els = array_values( array_filter( $els, function ( $e ) use ( $remove ) {
			return ! in_array( $e['id'], $remove, true );
		} ) );
		foreach ( $els as &$e ) {
			if ( isset( $set[ $e['id'] ] ) ) {
				$e['settings'] = array_merge( $e['settings'], $set[ $e['id'] ] );
			}
			if ( ! empty( $e['elements'] ) ) {
				bz_seo_patch( $e['elements'], $set, $remove );
			}
		}
		unset( $e );
	}

	function bz_seo_apply( array $d, array $p ) {
		$set    = [];
		$remove = [];
		$log    = [];
		$links  = $p['links'];
		$self   = $p['slug'];

		// 1. Hero (section 1): H1 + intro split into up to 2 paragraphs.
		$hw = bz_seo_widgets( $d[1] );
		$ht = array_values( array_filter( $hw, fn( $w ) => 'text-editor' === $w['widgetType'] ) );
		$hh = array_values( array_filter( $hw, fn( $w ) => 'heading' === $w['widgetType'] ) );
		if ( ! $hh || ! $ht ) {
			return [ null, [ 'error' => 'hero not found' ] ];
		}
		$set[ $hh[0]['id'] ] = [ 'title' => $p['h1'] ];
		$paras = bz_seo_sentences( $p['intro'], min( 2, count( $ht ) ) );
		foreach ( $ht as $i => $w ) {
			if ( isset( $paras[ $i ] ) ) {
				$set[ $w['id'] ] = [ 'editor' => bz_seo_link( '<p>' . esc_html( $paras[ $i ] ) . '</p>', $links, $self ) ];
			} else {
				$remove[] = $w['id'];
			}
		}
		$log['hero'] = count( $paras ) . ' paragraphs';

		// 2. Main text section: heading + >=2 text-editors + image, no accordion/carousel/form/icons.
		$main = null;
		foreach ( $d as $i => $sec ) {
			if ( $i < 2 ) {
				continue;
			}
			$types = array_map( fn( $w ) => $w['widgetType'], bz_seo_widgets( $sec ) );
			$c     = array_count_values( $types );
			if ( ( $c['heading'] ?? 0 ) === 1 && ( $c['text-editor'] ?? 0 ) >= 2 && ( $c['image'] ?? 0 ) >= 1 && count( $c ) === 3 ) {
				$main = $i;
				break;
			}
		}
		if ( null === $main ) {
			return [ null, [ 'error' => 'main text section not found' ] ];
		}
		$mt    = array_values( array_filter( bz_seo_widgets( $d[ $main ] ), fn( $w ) => 'text-editor' === $w['widgetType'] ) );
		$first = 0;
		while ( isset( $p['main'][ $first ] ) && 'h' !== $p['main'][ $first ][0] ) {
			$first++;
		}
		$set[ $mt[0]['id'] ] = [ 'editor' => bz_seo_link( bz_seo_render( array_slice( $p['main'], 0, $first ) ), $links, $self ) ];
		$rest = bz_seo_link( bz_seo_render( array_slice( $p['main'], $first ) ), $links, $self );
		// Anchors not found verbatim in the text go into a "see also" line, so every link from the brief lands on the page.
		if ( $links ) {
			$a = [];
			foreach ( $links as $l ) {
				$a[] = '<a href="' . esc_url( home_url( $l[1] ) ) . '" style="color:#6fd0ac;text-decoration:underline">' . esc_html( mb_strtoupper( mb_substr( $l[0], 0, 1 ) ) . mb_substr( trim( preg_replace( '/\s*\([^)]*\)/u', '', $l[0] ) ), 1 ) ) . '</a>';
			}
			$rest .= '<p>Смотрите также: ' . implode( ', ', $a ) . '.</p>';
			$log['see_also'] = count( $a );
			$links = [];
		}
		$set[ $mt[1]['id'] ] = [ 'editor' => $rest ];
		foreach ( array_slice( $mt, 2 ) as $w ) {
			$remove[] = $w['id'];
		}
		$log['main_section'] = $main;

		// 3. FAQ accordion.
		$faq_found = false;
		foreach ( $d as $sec ) {
			foreach ( bz_seo_widgets( $sec ) as $w ) {
				if ( 'accordion' === $w['widgetType'] ) {
					$tabs = [];
					foreach ( $p['faq'] as $k => $f ) {
						$tabs[] = [ '_id' => substr( md5( $self . $k . $f[0] ), 0, 7 ), 'tab_title' => $f[0], 'tab_content' => $f[1] ];
					}
					$set[ $w['id'] ] = [ 'tabs' => $tabs ];
					$faq_found       = true;
				}
			}
		}
		if ( ! $faq_found ) {
			return [ null, [ 'error' => 'faq not found' ] ];
		}

		// 4. Final CTA (last section): first text-editor.
		$cw = array_values( array_filter( bz_seo_widgets( end( $d ) ), fn( $w ) => 'text-editor' === $w['widgetType'] ) );
		if ( $cw ) {
			$set[ $cw[0]['id'] ] = [ 'editor' => esc_html( $p['cta'] ) ];
		}

		// 5. Production-time chip, if the page text states "X до Y рабочих дней" / "X–Y рабочих дней".
		if ( preg_match( '/(\d+)\s*(?:до|–|-)\s*(\d+)\s*рабочих\s+дн/u', $p['intro'] . ' ' . wp_json_encode( $p['main'], JSON_UNESCAPED_UNICODE ), $m ) ) {
			foreach ( $d as $sec ) {
				foreach ( bz_seo_widgets( $sec ) as $w ) {
					if ( 'text-editor' === $w['widgetType'] && preg_match( '/^\s*Срок (производства|изготовления)/u', wp_strip_all_tags( $w['settings']['editor'] ?? '' ) ) ) {
						$set[ $w['id'] ] = [ 'editor' => 'Срок изготовления<br><b style="color:#ffffff;">от ' . $m[1] . ' до ' . $m[2] . ' рабочих дней</b>' ];
						$log['srok'] = $m[1] . '-' . $m[2];
					}
				}
			}
		}

		$log['links_unplaced'] = array_values( array_map( fn( $l ) => $l[0], $links ) );
		bz_seo_patch( $d, $set, $remove );
		return [ $d, [ 'set' => $set, 'remove' => $remove, 'log' => $log ] ];
	}
}
