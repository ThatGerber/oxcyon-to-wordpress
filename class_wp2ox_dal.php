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

class wp2ox_dal {

	public $dbusername;
	public $dbpassword;
	public $database;

	public function __construct() {}

	public function categories ( $brand, $cat_value ) {
		$sql = 'SELECT ModuleSID, Title
    		FROM ' . $brand . '_Taxonomy_old
    		WHERE Parent
    		LIKE :value';
		$value = array(":value" => $cat_value);

		return $this->query($sql, $value);
	}

	public function results_array( $name ) {



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