<?php
namespace CheckoutPlus;

/**
 * Cleans the field value to make  sure that data is well sanitized
 *
 * @param string $key field key.
 * @param mixed  $value field value.
 * @param array  $field fields array collection.
 *
 * @return mixed
 */
function clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'CheckoutPlus\clean', $var );
	} else {
		if ( is_scalar( $var ) ) {
			$var = wp_kses_post( $var );
			$var = stripslashes( $var );
		}
		return $var;
	}
}

/**
 *
 */
function string_to_secret( string $string = null ) {
	if ( ! $string ) {
		return null;
	}
	$length        = strlen( $string );
	$visible_count = (int) round( $length / 4 );
	$hidden_count  = $length - ( $visible_count * 2 );
	return substr( $string, 0, $visible_count ) . str_repeat( '*', $hidden_count ) . substr( $string, ( $visible_count * -1 ), $visible_count );
}
