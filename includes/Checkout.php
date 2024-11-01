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
class Checkout {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The Fields class object.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $fields;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, Fields $fields ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->fields      = $fields;
	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, CHECKOUTPLUS_ASSETS . 'public/css/wc-checkoutplus.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {}

	/**
	 * prepare billing fields function.
	 *
	 * @param mixed $old
	 */
	public function prepare_billing_fields( $checkout_fields ) {
		// phpcs:ignore
		if ( is_admin() && isset( $_GET['page'] ) && 'checkoutplus_fields' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			return $checkout_fields;
		}

		$fields = get_option( 'wc_fields_billing' );
		return $this->prepare_fields_for_display( $fields, $checkout_fields );
	}

	/**
	 * prepare shipping fields function.
	 *
	 * @param mixed $old
	 */
	public function prepare_shipping_fields( $checkout_fields ) {
		// phpcs:ignore
		if ( is_admin() && isset( $_GET['page'] ) && 'checkoutplus_fields' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			return $checkout_fields;
		}

		$fields = get_option( 'wc_fields_shipping' );

		return $this->prepare_fields_for_display( $fields, $checkout_fields );
	}


	/**
	 * prepare_order_fields function.
	 *
	 * @param mixed $old
	 */
	public function prepare_order_fields( $checkout_fields ) {
		// phpcs:ignore
		if ( is_admin() && isset( $_GET['page'] ) && 'checkoutplus_fields' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			return $checkout_fields;
		}

		$additional_fields = get_option( 'wc_fields_additional' );

		if ( $additional_fields ) {
			$checkout_fields['order'] = $additional_fields + $checkout_fields['order'];

			if ( isset( $additional_fields ) && isset( $additional_fields['order_comments'] ) && ! $additional_fields['order_comments']['enabled'] ) {
				unset( $checkout_fields['order']['order_comments'] );

				if ( 1 === count( $additional_fields ) ) {
					do_action( 'wc_checkout_fields_disable_order_comments' );
				}
			}
		}

		return $checkout_fields;
	}


	/**
	 * Modify the array of billing and shipping fields.
	 *
	 * @param mixed $data New checkout fields from this plugin.
	 * @param mixed $old Existing checkout fields from WC.
	 */
	public function prepare_fields_for_display( $data, $old_fields ) {
		if ( empty( $data ) ) {
			// If we have made no modifications, return the original.
			return $old_fields;
		}

		$fields = $data;
		foreach ( $fields as $name => $values ) {
			if ( false === $values['enabled'] ) {
				unset( $fields[ $name ] );
			}

			// Replace locale field properties so they are unchanged.
			if ( ! in_array(
				$name,
				array(
					'billing_address_1',
					'billing_state',
					'billing_city',
					'billing_country',
					'billing_postcode',
					'shipping_country',
					'shipping_state',
					'shipping_city',
					'shipping_postcode',
					'order_comments',
				),
				true
			) ) {
				continue;
			}

			if ( ! isset( $fields[ $name ] ) ) {
				continue;
			}

			$fields[ $name ]          = $old_fields[ $name ];
			$fields[ $name ]['label'] = ! empty( $data[ $name ]['label'] ) ? $data[ $name ]['label'] : $old_fields[ $name ]['label'];

			// placeholder
			if ( ! empty( $data[ $name ]['placeholder'] ) ) {
				$fields[ $name ]['placeholder'] = $data[ $name ]['placeholder'];
			} elseif ( ! empty( $old_fields[ $name ]['placeholder'] ) ) {
				$fields[ $name ]['placeholder'] = $old_fields[ $name ]['placeholder'];
			} else {
				$fields[ $name ]['placeholder'] = '';
			}

			// css class
			$fields[ $name ]['class'] = $data[ $name ]['class'];

			if ( version_compare( WC_VERSION, '3.0.0', '<' ) ) {
				$fields[ $name ]['clear'] = $data[ $name ]['clear'];
			} else {
				$fields[ $name ]['priority'] = $data[ $name ]['priority'];
			}
		}

		return $fields;
	}


	/**
	 * Undocumented function
	 *
	 * @param string $field
	 * @param [type] $key
	 * @param [type] $args
	 * @param [type] $value
	 * @return void
	 */
	public function checkout_fields_config( $field = '', $key, $args, $value ) {

		if ( ! in_array( $args['type'], array( 'radio', 'checkbox', 'html' ), true ) ) {
			return $field;
		}

		if ( 'html' !== $args['type'] ) {
			if ( $args['required'] ) {
				$args['class'][] = 'validate-required';
				$required        = '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>';
			} else {
				$required = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>';
			}
		}

		if ( is_string( $args['label_class'] ) ) {
			$args['label_class'] = array( $args['label_class'] );
		}

		if ( is_null( $value ) ) {
			$value = $args['default'];
		}

		// Custom attribute handling.
		$custom_attributes         = array();
		$args['custom_attributes'] = array_filter( (array) $args['custom_attributes'], 'strlen' );

		if ( $args['maxlength'] ) {
			$args['custom_attributes']['maxlength'] = absint( $args['maxlength'] );
		}

		if ( ! empty( $args['autocomplete'] ) ) {
			$args['custom_attributes']['autocomplete'] = $args['autocomplete'];
		}

		if ( true === $args['autofocus'] ) {
			$args['custom_attributes']['autofocus'] = 'autofocus';
		}

		if ( $args['description'] ) {
			$args['custom_attributes']['aria-describedby'] = $args['id'] . '-description';
		}

		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		if ( ! empty( $args['validate'] ) ) {
			foreach ( $args['validate'] as $validate ) {
				$args['class'][] = 'validate-' . $validate;
			}
		}

		$field           = '';
		$label_id        = $args['id'];
		$sort            = $args['priority'] ? $args['priority'] : '';
		$field_container = '<p class="form-row %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</p>';

		// radio
		if ( 'radio' === $args['type'] ) {
			$label_id .= '_' . current( array_keys( $args['options'] ) );

			if ( ! empty( $args['options'] ) ) {
				foreach ( $args['options'] as $option_key => $option_text ) {
					$input  = '<input type="radio" class="input-radio ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '" ' . implode( ' ', $custom_attributes ) . ' id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) . ' /> ';
					$field .= '<label for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '" class="radio ' . implode( ' ', $args['label_class'] ) . '">' . $input . esc_html( $option_text ) . '</label>';
				}
			}
		}

		// checkbox
		if ( 'checkbox' === $args['type'] ) {
			$multiple = is_array( $args['options'] ) && ! empty( $args['options'] ) ? true : false;

			if ( $multiple ) {
			} else {
				$field = '<label class="checkbox ' . implode( ' ', $args['label_class'] ) . '" ' . implode( ' ', $custom_attributes ) . '>
        <input type="' . esc_attr( $args['type'] ) . '" class="input-checkbox ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="1" ' . checked( $value, 1, false ) . ' /> ' . $args['label'] . $required . '</label>';
			}
		}

		// html
		if ( 'html' === $args['type'] ) {
			$field_container = '<div class="form-row %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</div>';
			$field = wp_kses_post( $args['content'] );
		}

		if ( ! empty( $field ) ) {
			$field_html = '';

			if ( $args['label'] && 'checkbox' !== $args['type'] ) {
				$field_html .= '<label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . wp_kses_post( $args['label'] ) . $required . '</label>';
			}

			$field_html .= '<span class="woocommerce-input-wrapper">' . $field;

			if ( $args['description'] ) {
				$field_html .= '<span class="description" id="' . esc_attr( $args['id'] ) . '-description" aria-hidden="true">' . wp_kses_post( $args['description'] ) . '</span>';
			}

			$field_html .= '</span>';

			$container_class = esc_attr( implode( ' ', $args['class'] ) );
			$container_id    = esc_attr( $args['id'] ) . '_field';
			$field           = sprintf( $field_container, $container_class, $container_id, $field_html );
		}

		return $field;
	}

	/**
	 * Saves custom field data to the Order Meta table.
	 *
	 * @param mixed $id
	 * @param mixed $posted
	 * @return void
	 */
	public function save_data( $order_id, $posted ) {
		$types = array( 'billing', 'shipping', 'additional' );

		foreach ( $types as $type ) {
			$fields = $this->fields->get_fields( $type );

			foreach ( $fields as $name => $field ) {
				if ( empty( $posted[ $name ] ) ) {
					continue;
				}

				if ( ! empty( $field['custom'] ) ) {
					$value = wc_clean( $posted[ $name ] );

					if ( $value ) {
						update_post_meta( $order_id, $name, $value );
					}
				}
			}
		}
	}

	/**
	 * See if a fieldset should be skipped.
	 *
	 * @since 3.0.0
	 * @param string $fieldset_key Fieldset key.
	 * @param array  $data         Posted data.
	 * @return bool
	 */
	protected function maybe_skip_fieldset( $fieldset_key, $data ) {
		if ( 'shipping' === $fieldset_key && ( ! $data['ship_to_different_address'] || ! WC()->cart->needs_shipping_address() ) ) {
			return true;
		}

		if ( 'account' === $fieldset_key && ( is_user_logged_in() || ( ! apply_filters( 'woocommerce_checkout_registration_required', 'yes' !== get_option( 'woocommerce_enable_guest_checkout' ) ) && empty( $data['createaccount'] ) ) ) ) {
			return true;
		}

		return false;
	}

}
