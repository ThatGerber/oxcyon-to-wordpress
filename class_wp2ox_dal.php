<?php
/**
 * Database layer
 *
 * @category    PHP
 * @copyright   2014
 * @license     WTFPL
 * @version     1.1.0
 * @since       2/18/2014
 */

class wp2ox_dal extends wp2ox {

	/**
	 * @var array Contains WordPress option
	 */
	private $options;

	/**
	 * @var $pdo object PHP Database Object. Contains the database connection
	 * used in the queries
	 */
	private $pdo;

	/**
	 * @var $stmt object Prepared statement
	 */
	private $stmt;

	/**
	 * @deprecated Replaced with $this->options->category_value
	 * Search Val
	 */
	private $searchVal;

	/**
	 * @deprecated Replaced with $this->options->db_username
	 * @var string $dbusername Username to access the database
	 */
	private $dbusername;

	/**
	 * @deprecated replaced with $this->options->db_pass
	 * @var string $dbpassword Password for that user.
	 */
	private $dbpassword;

	/**
	 * @deprecated Replaced with $this->options->db_name
	 * @var string $database Name of the database
	 */
	private $database;

	public function __construct() {
		$this->options = new wp2ox_data;
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

		if ( $searchVal !== null && $this->options->category_value == null ) {
			$searchVal = $this->options->category_value;
		}

		$sql = $this->sql_statement( $query );

		wp2ox::reportText( 'code', "SQL Statement: " . $sql );

		if ( $this->pdo = $this->connect() ) {

			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$this->stmt = $this->pdo->prepare( $sql );

			if ( $this->options->category_value !== null ) {
				$this->stmt->bindParam(":value", $searchVal );
			}

			$this->stmt->execute();

			$results = $this->stmt->fetchAll( PDO::FETCH_ASSOC );

			return $results;
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
			'Articles'   => 'Select * FROM ' . $this->options->articles_table,
			'Authors'    => 'SELECT * FROM ' . $this->options->author_table,
			'Categories' => 'SELECT ModuleSID, Title FROM ' . $this->options->taxonomy_table . ' WHERE ModuleSID NOT REGEXP :value',
			'Tags'       => 'SELECT ModuleSID, Title FROM ' . $this->options->taxonomy_table,
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
			$dsn = "mysql:host=localhost;dbname=" . $this->options->db_name;

			$new_pdo = new PDO(
				$dsn,
				$this->options->db_username,
				$this->options->db_pass
			);
		} catch ( Exception $oxc_e ) {

			var_export( $oxc_e->getMessage() );
		}

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

		return $this->queryResults('Authors');
	}

	/**
	 * Returns array of all Category data
	 *
	 * Gets all of the category data from the database, returns it to a variable
	 *
	 * @return mixed|null Array on success, NULL if no data available.
	 */
	public function get_categories ( ) {

		return $this->queryResults('Categories', $this->options->category_value );
	}

	/**
	 * Returns array of all article data
	 *
	 * Gets all of the article data from the database, returns it to a variable
	 *
	 * @return mixed|null Array on success, NULL if no data available.
	 */
	public function get_articles ( ) {

		return $this->queryResults('Articles');
	}

	/**
	 * Returns array of all tag data
	 *
	 * Gets all of the tag data from the database, returns it to a variable
	 *
	 * @return mixed|null Array on success, NULL if no data available.
	 */
	public function get_tags ( ) {

		return $this->queryResults('Tags');
	}

	/**
	 * Sets variables for import.
	 *
	 * @deprecated
	 * Use new object wp2ox_data
	 */
	private function set_import_variables( $option_group ) {
		$this->options        = $option_group;
		$this->dbusername     = $option_group['db_username'];
		$this->dbpassword     = isset( $option_group['db_pass'] ) ? $option_group['db_pass'] : '';
		$this->database       = $option_group['db_name'];
		$this->searchVal      = $option_group['category_value'];
	}

}