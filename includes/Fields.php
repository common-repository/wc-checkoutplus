<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://pluginette.com
 * @since      1.0.0
 *
 * @package    CheckoutPlus
 * @subpackage CheckoutPlus/admin
 */

namespace CheckoutPlus;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    CheckoutPlus
 * @subpackage CheckoutPlus/admin
 * @author     David Towoju <hello@pluginette.com>
 */
class Fields {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	public $fields;

	/**
	 * Default fields.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $reserved_fields    Default fields.
	 */
	private static $reserved_fields = array(
		'billing_first_name',
		'billing_last_name',
		'billing_company',
		'billing_address_1',
		'billing_address_2',
		'billing_city',
		'billing_state',
		'billing_country',
		'billing_postcode',
		'billing_phone',
		'billing_email',
		'shipping_first_name',
		'shipping_last_name',
		'shipping_company',
		'shipping_address_1',
		'shipping_address_2',
		'shipping_city',
		'shipping_state',
		'shipping_country',
		'shipping_postcode',
		'customer_note',
		'order_comments',
	);

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {     }

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function register() {
		$this->fields = array(
			'billing'    => $this->get_fields( 'billing', true ),
			'shipping'   => $this->get_fields( 'shipping', true ),
			'additional' => $this->get_fields( 'additional', true ),
		);
	}

	/**
	 * Make fields ready for localization.
	 *
	 * @return void
	 */
	public function localize_fields() {
		 wp_localize_script( 'field-builder-app', 'chxJS', $this->get_local_data() );
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function get_local_data() {
		$data = array();

		foreach ( $this->fields as $key => $value ) {
			$data['fields'][ $key ] = $value;
			$data['list'][ $key ]   = array_keys( $value );
		}

		$data['countries'] = WC()->countries->countries;
		$data['states']    = WC()->countries->get_states( WC()->countries->get_base_country() );
		$data['nonce']     = wp_create_nonce( 'wcfb_save_data' );

		return $data;
	}


	/**
	 * Gets all fields.
	 *
	 * @access public
	 * @param mixed $key the type of fields to get.
	 * @return array
	 */
	public function get_fields( $key, $prepare = false ) {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		$fields = array_filter( get_option( 'wc_fields_' . $key, array() ) );
		// ray($fields);
		if ( empty( $fields ) || count( $fields ) === 0 ) {
			if ( 'billing' === $key || 'shipping' === $key ) {
				$fields = WC()->countries->get_address_fields( WC()->countries->get_base_country(), $key . '_' );
			} elseif ( 'additional' === $key ) {
				$fields = array(
					'order_comments' => array(
						'name'        => 'order_comments',
						'type'        => 'textarea',
						'class'       => array( 'notes' ),
						'label'       => __( 'Order Notes', 'wc-checkoutplus' ),
						'placeholder' => _x( 'Notes about your order, e.g. special notes for delivery.', 'placeholder', 'wc-checkoutplus' ),
					),
				);
			}
		}

		if ( $prepare ) {
			return $this->prepare_fields_for_display( $fields, $key );
		}

		return $fields;
	}

	/**
	 * Undocumented function
	 *
	 * @param array $fields fields array.
	 * @return array
	 */
	public function prepare_fields_for_display( $fields, $type = '' ) {
		$processed = array();
		// TODO: Maybe remove from every request
		$checkout_fields = WC()->countries->get_address_fields( WC()->countries->get_base_country(), $type . '_' );

		foreach ( $fields as $key => &$field ) {
			// ray($field);
			// Add name
			$field['name'] = $key;

			// Replace Label of Locale dependent fields
			// if ( ! $field['label'] && isset( $checkout_fields[ $key ] ) ) {
			if ( isset( $field['label'], $checkout_fields[ $key ]['label'] ) ) {
				if ( in_array(
					$field['name'],
					array(
						'billing_address_1',
						'billing_state',
						'billing_city',
						'billing_country',
						'billing_postcode',
						'shipping_country',
						'shipping_state',
						'shipping_city',
						'shipping_country',
						'shipping_postcode',
						'order_comments',
					),
					true
				) ) {
					$field['label'] = $checkout_fields[ $key ]['label'];
				}
			}
			// }

			// Add custom property to fields
			if ( in_array( $key, self::$reserved_fields, true ) ) {
				$field['custom'] = false;
			} else {
				$field['custom'] = true;
			}

			// Set type if empty
			if ( ! isset( $field['type'] ) || empty( $field['type'] ) ) {
				$field['type'] = 'text';
			}

			// Set enabled if empty
			if ( ! isset( $field['enabled'] ) ) {
				$field['enabled'] = true;
			}

			// Set default validation
			if ( isset( $field['required'] ) && true === $field['required'] ) {
				$field['validate'][] = 'required';
				$field['validate']   = array_unique( $field['validate'] );
			}

			// Set default validation
			if ( in_array( $field['type'], array( 'select', 'radio', 'checkbox' ), true ) ) {
				$options    = isset( $field['options'] ) ? $field['options'] : array();
				$newoptions = array();

				if ( empty( $options ) ) {
					// continue makes empty options not to display ... removing temporarily
					// and replacing with empty object
					// continue;
					$newoptions['data'][]  = new \StdClass();
					$newoptions['default'] = '';
				} else {
					foreach ( $options as $k => $v ) {
						$newoptions['data'][] = array(
							'name'  => $k,
							'value' => $v,
						);
					}
				}

				$options = $newoptions;

				// $newoptions['default'] = '';
				$field['options'] = $options;
			}

			// compatibility
			if ( 'multiselect' === $field['type'] ) {
				$field['type']    = 'select';
				$field['extra'][] = array( 'multiple' => true );
			}

			if ( 'heading' === $field['type'] ) {
				$field['type']    = 'html';
				$field['content'] = '<h3>' . $field['label'] . '</h3>';
			}

			// if ( 'checkbox' === $field['type'] ) {
			// dump( $field );
			// $field['type']    = 'html';
			// $field['content'] = '<h3>' . $field['label'] . '</h3>';
			// }

			if ( isset( $field['class'] ) ) {
				$field['class'] = is_array( $field['class'] ) ? $field['class'] : explode( ',', $field['class'] );
				$field['class'] = array_values( $field['class'] );
			}

			$processed[ $key ] = $field;
		}
		unset( $field );

		return $processed;
	}

	/**
	 * Prepare fields for saving to database
	 *
	 * @param array $fields fields array collection.
	 * @param array $list list array collections.
	 * @return array
	 */
	public static function prepare_fields_for_save( $fields, $list ) {
		$newfields = array();

		foreach ( $list as $key => $field_name ) {

			// get the field
			$field = $fields[ $field_name ];

			// replace field name if present in reserved field names
			if ( isset( $field['custom'] ) && true === $field['custom'] ) {
				if ( in_array( $field['name'], self::$reserved_fields, true ) ) {
					$field['name'] = $value . '_' . strtolower( wp_generate_password( 5, false ) );
				}
			}

			// todo: add select default as key of field array

			// add priority
			$field['priority'] = ( $key + 1 ) * 10;

			// convert css classes to array
			if ( isset( $field['class'] ) &&
			! empty( $field['class'] ) &&
			! is_array( $field['class'] ) ) {
				$field['class'] = array_map( 'trim', explode( ',', $field['class'] ) );
			}

			// add required if necessary
			if ( isset( $field['validate'] ) && is_array( $field['validate'] ) ) {
				if ( in_array( 'required', $field['validate'], true ) ) {
					$field['required'] = true;
				}
			} else {
				$field['validate'] = array();
			}

			// priority
			if ( isset( $field['priority'] ) ) {
				$field['priority'] = absint( $field['priority'] );
			}

			// options
			if ( ! isset( $field['options']['data'] ) || empty( $field['options']['data'] ) || ! is_array( $field['options']['data'] ) ) {
				$field['options'] = array();
			} else {
				// remove empty values of array and child arrays
				$field['options']['data'] = array_filter( array_map( 'array_filter', $field['options']['data'] ) );

				$newvalue = array();

				// flatten options and assign the values to newvalue
				foreach ( $field['options']['data'] as $val ) {
					$newvalue[ $val['name'] ] = $val['value'];
				}

				$field['options'] = $newvalue;
			}

			$field['enabled'] = isset( $field['enabled'] ) && ! empty( $field['enabled'] ) ? true : false;
			$field['custom']  = isset( $field['custom'] ) && ! empty( $field['custom'] ) ? true : false;

			$newfields[ $field['name'] ] = $field;
		}

		return $newfields;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function save() {
		// Only admin users can save this option
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Do a nonce security check, bail if fails
		if ( ! isset( $_POST['wcfb_form_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['wcfb_form_nonce'] ) );

		if ( ! wp_verify_nonce( $nonce, 'wcfb_save_data' ) ) {
			wp_die( 'Security check' );
		}

		// Get data and sanitize array
		$data = isset($_POST['wcfb_data']) ? json_decode( wp_kses_post( wp_unslash( $_POST['wcfb_data'] ) ), true ) : [];
		if ( ! $data ) { // return if data is null for example
			return;
		}

		// Bail if it's not array or empty array
		$fields = is_array( $data['fields'] ) && isset( $data['fields'] ) ? $data['fields'] : array();
		$list   = is_array( $data['list'] ) && isset( $data['list'] ) ? $data['list'] : array();

		// Bail if it's not array or empty array
		if ( empty( $fields ) || empty( $list ) ) {
			return;
		}

		$tabs = array( 'billing', 'shipping', 'additional' );
		foreach ( $tabs as $tab ) {
			if ( ! isset( $fields[ $tab ], $list[ $tab ] ) ) {
				return;
			}

			$cleaned_list   = clean( $list[ $tab ] );
			$cleaned_fields = clean( $fields[ $tab ] );

			update_option( 'wc_fields_' . $tab, $this->prepare_fields_for_save( $cleaned_fields, $cleaned_list ) );
		}
	}
}
