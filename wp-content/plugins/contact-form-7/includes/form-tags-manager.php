<?php

function wpcf7_add_form_tag( $tag, $func, $features = '' ) {
	$manager = WPCF7_FormTagsManager::get_instance();

	return $manager->add( $tag, $func, $features );
}

function wpcf7_remove_form_tag( $tag ) {
	$manager = WPCF7_FormTagsManager::get_instance();

	return $manager->remove( $tag );
}

function wpcf7_replace_all_form_tags( $content ) {
	$manager = WPCF7_FormTagsManager::get_instance();

	return $manager->replace_all( $content );
}

function wpcf7_scan_form_tags( $cond = null ) {
	$contact_form = WPCF7_ContactForm::get_current();

	if ( $contact_form ) {
		return $contact_form->scan_form_tags( $cond );
	}

	return array();
}

function wpcf7_form_tag_supports( $tag, $feature ) {
	$manager = WPCF7_FormTagsManager::get_instance();

	return $manager->tag_type_supports( $tag, $feature );
}

class WPCF7_FormTagsManager {

	private static $instance;

	private $tags = array();
	private $scanned_tags = null; // Tags scanned at the last time of scan()

	private function __construct() {}

	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function get_scanned_tags() {
		return $this->scanned_tags;
	}

	public function add( $tag, $func, $features = '' ) {
		if ( ! is_callable( $func ) ) {
			return;
		}

		if ( true === $features ) { // for back-compat
			$features = array( 'name-attr' => true );
		}

		$features = wp_parse_args( $features, array() );

		$tags = array_filter( array_unique( (array) $tag ) );

		foreach ( $tags as $tag ) {
			$tag = $this->sanitize_tag_type( $tag );

			if ( ! $this->tag_type_exists( $tag ) ) {
				$this->tags[$tag] = array(
					'function' => $func,
					'features' => $features );
			}
		}
	}

	public function tag_type_exists( $tag ) {
		return isset( $this->tags[$tag] );
	}

	public function tag_type_supports( $tag, $feature ) {
		if ( isset( $this->tags[$tag]['features'] ) ) {
			return ! empty( $this->tags[$tag]['features'][$feature] );
		}

		return false;
	}

	private function sanitize_tag_type( $tag ) {
		$tag = preg_replace( '/[^a-zA-Z0-9_*]+/', '_', $tag );
		$tag = rtrim( $tag, '_' );
		$tag = strtolower( $tag );
		return $tag;
	}

	public function remove( $tag ) {
		unset( $this->tags[$tag] );
	}

	public function normalize( $content ) {
		if ( empty( $this->tags ) ) {
			return $content;
		}

		$content = preg_replace_callback(
			'/' . $this->tag_regex() . '/s',
			array( $this, 'normalize_callback' ),
			$content );

		return $content;
	}

	private function normalize_callback( $m ) {
		// allow [[foo]] syntax for escaping a tag
		if ( $m[1] == '[' && $m[6] == ']' ) {
			return $m[0];
		}

		$tag = $m[2];

		$attr = trim( preg_replace( '/[\r\n\t ]+/', ' ', $m[3] ) );
		$attr = strtr( $attr, array( '<' => '&lt;', '>' => '&gt;' ) );

		$content = trim( $m[5] );
		$content = str_replace( "\n", '<WPPreserveNewline />', $content );

		$result = $m[1] . '[' . $tag
			. ( $attr ? ' ' . $attr : '' )
			. ( $m[4] ? ' ' . $m[4] : '' )
			. ']'
			. ( $content ? $content . '[/' . $tag . ']' : '' )
			. $m[6];

		return $result;
	}

	public function replace_all( $content ) {
		return $this->scan( $content, true );
	}

	public function scan( $content, $replace = false ) {
		$this->scanned_tags = array();

		if ( empty( $this->tags ) ) {
			if ( $replace ) {
				return $content;
			} else {
				return $this->scanned_tags;
			}
		}

		if ( $replace ) {
			$content = preg_replace_callback(
				'/' . $this->tag_regex() . '/s',
				array( $this, 'replace_callback' ),
				$content );

			return $content;
		} else {
			preg_replace_callback(
				'/' . $this->tag_regex() . '/s',
				array( $this, 'scan_callback' ),
				$content );

			return $this->scanned_tags;
		}
	}

	public function filter( $content, $cond ) {
		if ( is_array( $content ) ) {
			$tags = $content;
		} elseif ( is_string( $content ) ) {
			$tags = $this->scan( $content );
		} else {
			$tags = $this->scanned_tags;
		}

		if ( empty( $tags ) ) {
			return array();
		}

		if ( ! is_array( $cond ) || empty( $cond ) ) {
			return $tags;
		}

		for ( $i = 0, $size = count( $tags ); $i < $size; $i++ ) {

			if ( isset( $cond['type'] ) ) {
				if ( is_string( $cond['type'] ) && ! empty( $cond['type'] ) ) {
					if ( $tags[$i]['type'] != $cond['type'] ) {
						unset( $tags[$i] );
						continue;
					}
				} elseif ( is_array( $cond['type'] ) ) {
					if ( ! in_array( $tags[$i]['type'], $cond['type'] ) ) {
						unset( $tags[$i] );
						continue;
					}
				}
			}

			if ( isset( $cond['name'] ) ) {
				if ( is_string( $cond['name'] ) && ! empty( $cond['name'] ) ) {
					if ( $tags[$i]['name'] != $cond['name'] ) {
						unset ( $tags[$i] );
						continue;
					}
				} elseif ( is_array( $cond['name'] ) ) {
					if ( ! in_array( $tags[$i]['name'], $cond['name'] ) ) {
						unset( $tags[$i] );
						continue;
					}
				}
			}
		}

		return array_values( $tags );
	}

	private function tag_regex() {
		$tagnames = array_keys( $this->tags );
		$tagregexp = join( '|', array_map( 'preg_quote', $tagnames ) );

		return '(\[?)'
			. '\[(' . $tagregexp . ')(?:[\r\n\t ](.*?))?(?:[\r\n\t ](\/))?\]'
			. '(?:([^[]*?)\[\/\2\])?'
			. '(\]?)';
	}

	private function replace_callback( $m ) {
		return $this->scan_callback( $m, true );
	}

	private function scan_callback( $m, $replace = false ) {
		// allow [[foo]] syntax for escaping a tag
		if ( $m[1] == '[' && $m[6] == ']' ) {
			return substr( $m[0], 1, -1 );
		}

		$tag = $m[2];
		$attr = $this->parse_atts( $m[3] );

		$scanned_tag = array(
			'type' => $tag,
			'basetype' => trim( $tag, '*' ),
			'name' => '',
			'options' => array(),
			'raw_values' => array(),
			'values' => array(),
			'pipes' => null,
			'labels' => array(),
			'attr' => '',
			'content' => '' );

		if ( is_array( $attr ) ) {
			if ( is_array( $attr['options'] ) ) {
				if ( $this->tag_type_supports( $tag, 'name-attr' )
				&& ! empty( $attr['options'] ) ) {
					$scanned_tag['name'] = array_shift( $attr['options'] );

					if ( ! wpcf7_is_name( $scanned_tag['name'] ) ) {
						return $m[0]; // Invalid name is used. Ignore this tag.
					}
				}

				$scanned_tag['options'] = (array) $attr['options'];
			}

			$scanned_tag['raw_values'] = (array) $attr['values'];

			if ( WPCF7_USE_PIPE ) {
				$pipes = new WPCF7_Pipes( $scanned_tag['raw_values'] );
				$scanned_tag['values'] = $pipes->collect_befores();
				$scanned_tag['pipes'] = $pipes;
			} else {
				$scanned_tag['values'] = $scanned_tag['raw_values'];
			}

			$scanned_tag['labels'] = $scanned_tag['values'];

		} else {
			$scanned_tag['attr'] = $attr;
		}

		$scanned_tag['values'] = array_map( 'trim', $scanned_tag['values'] );
		$scanned_tag['labels'] = array_map( 'trim', $scanned_tag['labels'] );

		$content = trim( $m[5] );
		$content = preg_replace( "/<br[\r\n\t ]*\/?>$/m", '', $content );
		$scanned_tag['content'] = $content;

		$scanned_tag = apply_filters( 'wpcf7_form_tag', $scanned_tag, $replace );

		$this->scanned_tags[] = $scanned_tag;

		if ( $replace ) {
			$func = $this->tags[$tag]['function'];
			return $m[1] . call_user_func( $func, $scanned_tag ) . $m[6];
		} else {
			return $m[0];
		}
	}

	private function parse_atts( $text ) {
		$atts = array( 'options' => array(), 'values' => array() );
		$text = preg_replace( "/[\x{00a0}\x{200b}]+/u", " ", $text );
		$text = stripcslashes( trim( $text ) );

		$pattern = '%^([-+*=0-9a-zA-Z:.!?#$&@_/|\%\r\n\t ]*?)((?:[\r\n\t ]*"[^"]*"|[\r\n\t ]*\'[^\']*\')*)$%';

		if ( preg_match( $pattern, $text, $match ) ) {
			if ( ! empty( $match[1] ) ) {
				$atts['options'] = preg_split( '/[\r\n\t ]+/', trim( $match[1] ) );
			}

			if ( ! empty( $match[2] ) ) {
				preg_match_all( '/"[^"]*"|\'[^\']*\'/', $match[2], $matched_values );
				$atts['values'] = wpcf7_strip_quote_deep( $matched_values[0] );
			}
		} else {
			$atts = $text;
		}

		return $atts;
	}
}
