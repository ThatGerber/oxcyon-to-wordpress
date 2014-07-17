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

class wp2ox_dal extends wp2ox {

	/** Search Val */
	public $searchVal;

	/** @var array Contains WordPress option */
	private $options;

	/**
	 * @var string $dbusername Username to access the database
	 */
	private $dbusername;

	/**
	 * @var string $dbpassword Password for that user.
	 */
	private $dbpassword;

	/**
	 * @var string $database Name of the database
	 */
	private $database;

	/**
	 * @var $pdo object PHP Database Object. Contains the database connection
	 * used in the queries
	 */
	private $pdo;

	/** @var $stmt object Prepared statement */
	private $stmt;

	/** Import the settings */
	public function __construct() {

		$this->set_import_variables( get_option( 'wp2ox_settings' ) );

	}

	private function set_import_variables($option_group) {
		$this->options        = $option_group;
		$this->dbusername     = $option_group['db_user'];
		$this->dbpassword     = $option_group['db_pass'];
		$this->database       = $option_group['db_name'];
		$this->searchVal      = $option_group['category_value'];


	}

	/**
	 * Returns an associative array of searched data.
	 *
	 * @return mixed
	 */
	public function results_array() {

		return $this->stmt->fetchAll( PDO::FETCH_ASSOC );
	}

	/**
	 * Sets query results into a variable usable by certain functions.
	 *
	 * Executes SQL with a search val, if relevant, and sets the values to be used and pulled into associative array
	 * or other method.
	 *
	 * @param string      $query     Type of query to call.
	 * @param string|null $searchVal String to search for
	 *
	 * @return bool
	 */
	public function queryResults( $query, $searchVal = null ) {

		if ( $searchVal !== null && $this->searchVal !== null ) {
			$this->searchVal = $searchVal;
		}

		$sql = $this->sql_statement( $query );

		if ( $this->pdo = $this->connect() ) {

			$this->stmt = $this->pdo->prepare( $sql );

			if ( $this->searchVal ) {
				$this->stmt->bindParam(':value', $this->searchVal);
			}

			$this->stmt->execute();

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Returns true if the data was updated in the database.
	 */
	public function updated() {
		if ( $this->stmt->rowCount() >= 1) {

			return true;
		} else {

			return false;
		}
	}

	/**
	 * @param string $name Name of SQL Statement to run.
	 *
	 * @return bool|string Returns an SQL statement if it was a valid statement to query, or false if it was stupid.
 	 */
	private function sql_statement( $name ) {

		$array = array(
			'Articles'   => 'Select * FROM ' . $this->options["articles_table"],
			'Authors'    => 'SELECT * FROM ' . $this->options["author_table"],
			'Categories' => 'SELECT ModuleSID, Title FROM ' . $this->options["taxonomy_table"] . ' WHERE Parent LIKE :value',
			'Tags'       => 'SELECT ModuleSID, Title FROM ' . $this->options["taxonomy_table"],
		);

		if ( array_key_exists( $name, $array ) ) {

			return $array["$name"];
		}

		return FALSE;

	}

	/**
	 * Creates a PDO connect to database
	 *
	 * @return PDO|string Returns an error if the connection fails, or PDO on success.
	 */
	private function connect() {

		try {
			$new_pdo = new PDO(
				"mysql:host=localhost;", $this->dbusername, $this->dbpassword);
		} catch ( Exception $oxc_e ) {

			return $oxc_e->getMessage();
		}

		$database_sql = 'USE ' . $this->database;
		$new_pdo->exec($database_sql);

		return $new_pdo;
	}

	/**
	 * Returns array of all author data
	 *
	 * Gets all of the author data from the database, returns it to a variable
	 *
	 * @return mixed|null Array on success, NULL if no data available.
	 */
	public function get_authors() {

		if ( $this->queryResults('Authors') == TRUE ) {

			return $this->results_array();
		}

		return NULL;
	}

	/**
	 * Returns array of all Category data
	 *
	 * Gets all of the category data from the database, returns it to a variable
	 *
	 * @return mixed|null Array on success, NULL if no data available.
	 */
	public function get_categories ( ) {

		if ( $this->queryResults('Categories', $this->searchVal ) == TRUE ) {

			return $this->results_array();
		}

		return NULL;
	}

	/**
	 * Returns array of all article data
	 *
	 * Gets all of the article data from the database, returns it to a variable
	 *
	 * @return mixed|null Array on success, NULL if no data available.
	 */
	public function get_articles ( ) {

		if ( $this->queryResults('Articles') == TRUE ) {

			return $this->results_array();
		}

		return NULL;
	}

	/**
	 * Returns array of all tag data
	 *
	 * Gets all of the tag data from the database, returns it to a variable
	 *
	 * @return mixed|null Array on success, NULL if no data available.
	 */
	public function get_tags ( ) {

		if ( $this->queryResults('Tags') == TRUE ) {

			return $this->results_array();
		}

		return NULL;
	}

}