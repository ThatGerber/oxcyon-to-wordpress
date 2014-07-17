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

	/** @var $stmt object  */
	private $stmt;

	/**
	 * @var string $sql SQL statement
	 */
	//public $sql;

	/** Search Val */

	public $searchVal;

	public function __construct($pdo) {
		parent::set_variables( get_option( 'wp2ox_settings' ) );
		$this->pdo = $pdo;
		//$this->sql = $sql;
	}

	public function categories ( $brand, $cat_value ) {
		$sql = 'SELECT ModuleSID, Title
    		FROM ' . $brand . '_Taxonomy_old
    		WHERE Parent
    		LIKE :value';
		$value = array(":value" => $cat_value);

		return $this->query($sql, $value);
	}

	public function results_array( $table ) {

		$this->queryResults( $this->searchVal );

		return $this->stmt->fetchAll( PDO::FETCH_ASSOC );
	}

	public function queryResults( $searchVal = null ) {

		if ( $searchVal !== null ) {
			$this->searchVal = $searchVal;
		}

		$this->stmt = $this->pdo->prepare( $this->sql );

		if ( $searchVal ) {
			$this->stmt->bindParam(':value', $this->searchVal);
		}

		$this->stmt = $this->stmt->execute();

		return TRUE;
	}



	public function updated() {
		if ( $this->stmt->rowCount() >= 1) {

			return true;
		} else {

			return false;
		}
	}

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

	private function query( $sql, $search_val = NULL ) {
		$dbh = $this->connect();

		if ( isset( $sql ) ) {
			$data = $dbh->prepare($sql);
		} else {
			return FALSE;
		}
		if ( isset( $search_val ) ) {
			$data->execute( $search_val );
		} else {
			$data->execute();
		}

		return $data->fetchAll( PDO::FETCH_ASSOC );
	}
}