<?php
/**
 * Import Functions Page
 *
 * This will hold functions and classes related to the project import.
 *
 * @category    PHP
 * @copyright   2014
 * @license     WTFPL
 * @version     1.1.0
 * @since       2/18/2014
 */

/**
 * Class wp2ox
 *
 * Holds data and references for transferring between Wordpress and Oxcyon.
 */
class wp2ox {


	private $options;

	/**
	 * @var $brand string Which brand is being worked with.
	 */
	public $brand;

	/**
	 * @var $category_value string "Like" string for database search
	 */
	public $category_value;

	protected $reference_array;

	/**
	 * Author reference array.
	 *
	 * @var $authors array Key:Value of author IDs. author[New_id] = old_id
	 */
	public $authors;

	/**
	 * Category reference array.
	 *
	 * @var $categories array Key:Value of category IDs. category[new_id] = old_id
	 */
	public $categories;

	/**
	 * Tag reference array.
	 *
	 * @var $tags array Key:Value of tag. tag[title] = old_id
	 */
	public $tags;

	/**
	 * Number of posts imported
	 *
	 * @var string $tags number of posts imported
	 */
	public $postNumber;

	/**
	 * Import the user settings and set up the object.
	 */
	function __construct() {
		$this->set_variables( get_option( 'wp2ox_settings' ) );
	}

	protected function set_variables( $option_group ) {
		$this->options        = $option_group;
		$this->brand          = $option_group['brand'];
		$this->category_value = $option_group['category_value'];
		$this->dbusername     = $option_group['db_user'];
		$this->dbpassword     = $option_group['db_pass'];
		$this->database       = $option_group['db_name'];
	}

	/**
	 * Adds a Reference Array
	 *
	 * Creates a nested array of reference data. To be used to query later on when importing articles.
	 *
	 * @param $name string|int Name of array to store it in.
	 * @param $val1 string|int Key to store
	 * @param $val2 string|int Value to store
	 */
	public function add_reference($name, $val1, $val2) {

		$array = $this->reference_array[$name];
		$array[ $val1 ] = $val2;

	}

	/**
	 * Returns a reference array stored through add_reference
	 *
	 * @param $name string Name of array requested
	 *
	 * @return array
	 */
	public function get_reference_array($name) {

		return $this->reference_array[$name];
	}

	/**
	 * Prints the information. Wraps it in whatever you tell it to wrap in.
	 *
	 * @param $stringH string element tag, no brackets
	 * @param $string string String to wrap in brackets.
	 */
	static function reportText( $stringH, $string ) {

		echo '<' . $stringH . '>' . $string . '</' . $stringH . '>';
	}
}

/** Other Classes */

// Database abstraction layer
include( $import_folder . "/class_wp2ox_dal.php");

// Tidy
include( $import_folder . "/class_wp2ox_format.php");

// Author/Category creator
include( $import_folder . "/class_wp2ox_author.php");

// Tag Creator
include( $import_folder . "/class_wp2ox_tag.php");

// Category
include( $import_folder . "/class_wp2ox_category.php");

