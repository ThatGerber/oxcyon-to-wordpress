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

	/**
	 * @var $brand string Which brand is being worked with.
	 */
	public $brand;

	/**
	 * @var $category_value string "Like" string for database search
	 */
	public $category_value;

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
	 * PDO connection
	 * @param $dbh
	 */
	private $dbh;

	function __construct() {
	}

	/** Sets the PDO */
	public function set_dbh($dbh) {
		$this->dbh = $dbh;
	}

	/**
	 * Get Data from database
	 *
	 * @param $sql
	 * @param $value
	 * @return array
	 */
	public function getData( $sql, $value = null ) {
		// create array for search value
		// establish database connection
		$pdo = $this->dbh;
		// prepare database call
		$pdoObject  = $pdo->prepare( $sql );
		// check for errors
		if (!$pdoObject) {
			echo "\nPDO::errorInfo():\n";
			print_r($pdo->errorInfo());
		}
		// execute the database call
		$pdoObject->execute( $value );
		// return row data
		return $pdoObject->fetchAll( PDO::FETCH_ASSOC );
	}
}

/** Other Classes */

// Database abstraction layer
include( $import_folder . "/class_wp2ox_dal.php");

// Server queries - To fold into DAL
include( $import_folder . "/class_wp2ox_select_query.php");

// Author/Category creator
include( $import_folder . "/class_wp2ox_author.php");

// Tag Creator
include( $import_folder . "/class_wp2ox_tag.php");

//include( $import_folder . "/class_wp2ox_category.php");

