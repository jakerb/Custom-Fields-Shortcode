<?php
/*
Plugin Name: Custom Fields Shortcode
Plugin URI: https://jakerb.github.io/Custom-Fields-Shortcode/
Description: Pull in custom fields or ACF into your posts or Elementor templates easily.
Version: 1.0.3
Author: Jake Bown
Author URI: https://jakebown.com
Text Domain: cfs
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if(!class_exists('CFS_Core')) {

	/**
	 * The Core CFS plugin class.
	 */
	class CFS_Core {

		var $version = '1.0.3';
		var $shortcode_tag = 'get_custom_field';
		var $shortcode_filter = 'cfs_get_post_meta';
		
		function __construct() {

			$this->define('CFS_VERSION', $this->version);
			$this->define('CFS_SHORTCODE_NAME', $this->shortcode_tag);
			$this->define('CFS_SHORTCODE_FILTER', $this->shortcode_filter);
			$this->define('CFS_PLUGIN_DIR', __DIR__);
			$this->define('CFS_DEBUG', (defined('CFS_DEBUG') ? true : false));

		}

		function register_filters($tag = CFS_SHORTCODE_FILTER) {


			/* Debugging */
			add_filter( $tag, function($data) {

				if(empty($data->value) && CFS_DEBUG) {
					$data->value = __('CFS Debug: Field is empty!', 'cfs');
				}

				return $data;

			});


			/* Attribute : `text_replace' (string) */
			add_filter( $tag, function($data) {

				if(isset($data->attr['text_replace']) && is_string($data->value)) {
					if(strpos($data->attr['text_replace'], ',') !== false) {

						$split = explode(',', $data->attr['text_replace']);
						if(count($split) == 2) {
							$find = trim($split[0]);
							$replace = trim($split[1]);

							$data->value = str_replace($find, $replace, $data->value);
						}
					}
				}

				return $data;
			});

			/* Attribute : `text_format` (string) */
			add_filter( $tag, function($data) {

				if(isset($data->attr['text_format']) && is_string($data->value)) {

					switch (strtolower($data->attr['text_format'])) {
						case 'lowercase':
							$data->value = strtolower($data->value);
							break;

						case 'uppercase':
							$data->value = strtoupper($data->value);
							break;

						case 'base64':
							$data->value = base64_encode($data->value);
							break;

						case 'md5':
							$data->value = md5($data->value);
							break;

						case 'encode': 
							$data->value = urlencode($data->value);
					}

				}

				return $data;

				if(empty($data) && CFS_DEBUG) {
					$data = __('CFS Debug: Field is empty!', 'cfs');
				}

				return $data;

			});

			/* Attribute : `date_format` (Date string) */
			add_filter( $tag, function($data) {

				if(!empty($data->value) && isset($data->attr['date_format'])) {

					/*
					 * This is an invalid type.
					 */
					if(is_object($data->value) || is_array($data->value)) {
						return $data;
					}

					$strtime = strtotime($data->value);
					$format = $data->attr['date_format'];
					if($strtime) {
						$data->value = date($format, $strtime);
					}

				}	

				return $data;
			});

			/* Attribute : `text_contains' (string) */
			add_filter( $tag, function($data) {

				if(isset($data->attr['text_contains']) && is_string($data->value)) {
					if(!strpos($data->value, $data->attr['text_contains']) !== false) {
						$data->value = '';
					}
				}

				return $data;
			});

			/* Attribute : `escape' (string) */
			add_filter( $tag, function($data) {

				if(isset($data->attr['escape']) && is_string($data->value)) {
					$data->value = strip_tags($data->value);
				}

				return $data;
			});

		}

		function register_shortcode($tag = CFS_SHORTCODE_NAME) {

			if(function_exists('add_shortcode')) {

				/*
				 * Register the default filters.
				 */
				$this->register_filters();

				add_shortcode( $tag, function( $attr ) {

					global $post;

					/*
					 * Get the primary attributes required.
					 */
					$key 		= isset($attr['key']) ? sanitize_text_field($attr['key']) : false;
					$post_id 	= isset($attr['id']) ? abs(intval($attr['id'])) : false;
					$filter 	= isset($attr['filter']) ? sanitize_text_field($attr['filter']) : false;
					$single  	= isset($attr['single']) && !boolval($attr['single']) ? false : true;

					/*
					 * Set the scope to the current post if not set.
					 */
					if(!$post_id && is_object($post) && isset($post->ID)) {
						$post_id = $post->ID;
					}

					/*
					 * A field name has not been specified and the Post ID is 0.
					 */
					if(!$key || !$post_id) {
						return;
					}

					/*
					 * We are referencing the post object rather than meta data.
					 */
					if(strpos($key, ':') !== false) {

						$key = str_replace(':', '', $key);
						$post = $post->ID == $post_id ? $post : get_post($post_id);
						if(isset($post->{$key})) {
							$post_meta = $post->{$key};
						}

					} else {
						$post_meta = get_post_meta( $post_id, $key, $single );
					}

					

					/* 
					 * The meta has been set.
					 */
					if(!empty($post_meta)) {

						$post_obj = (object) array(
							'ID' 	=> $post_id,
							'key'	=> $key,
							'value' => $post_meta,
							'attr'	=> $attr
						);
						

						if($filter && has_filter($filter)) {
							$post_obj = apply_filters( $filter, $post_obj);
						}

						if(has_filter(CFS_SHORTCODE_FILTER)) {
							$post_obj = apply_filters(CFS_SHORTCODE_FILTER, $post_obj);
						}
					}

					return is_object($post_obj) && isset($post_obj->value) ? $post_obj->value : $post_meta;

				});

			}

		}

		function define( $name, $value = true ) {
			if( !defined($name) ) {
				define( $name, $value );
			}
		}
	}

	if(function_exists('add_action')) {
		add_action('wp', function() {
			(new CFS_Core())->register_shortcode();
		});
	}

}