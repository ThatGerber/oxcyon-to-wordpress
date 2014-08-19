<?php
/**
 * Class wp2ox_data
 *
 * Holds data and references for transferring between Wordpress and Oxcyon.
 *
 * @category    PHP
 * @copyright   2014
 * @license     WTFPL
 * @version     0.4.0
 * @since       8/11/2014
 */
class wp2ox_data {

	/**
	 * @var array $options Options array
	 */
	private $__options = array();

	/**
	 * Get information from the options array
	 *
	 * Options array consists of information about the particular instance
	 */

	public function __construct( $preload = TRUE ) {
		if ( $preload === TRUE ) {
			$this->__options = get_option( 'wp2ox_settings' );
		}
	}

	public function __get( $property ) {
		if ( isset( $this->__options[ $property ] ) ) {

			return $this->__options[ $property ];
		} else {

			return FALSE;
		}
	}

	public function __set( $property, $value ) {

		$this->__data[$property] = $value;
	}



}


