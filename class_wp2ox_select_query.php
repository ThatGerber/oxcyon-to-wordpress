<?php
/**
 * Class oxc_selectQuery
 *
 * @var PDO $pdo
 * @var $sql = sql select statement
 * @var $searchVal = Value to select from
 *
 * returns array of data
 */
class wp2ox_select_query {

	/**
	 * @var $pdo object PHP Database Object. Contains the database connection
	 * used in the queries
	 */

	public $sql;

	private $pdo;

	/** @var $stmt object  */
	private $stmt;

	/**
	 * Initializes the PDO
	 *
	 * @param $pdo pdo database connection
	 */
	public function __construct( $pdo, $sql = NULL ) {
		$this->pdo = $pdo;
		$this->sql = $sql;
	}

	public function queryResults( $searchVal = null ) {

		$this->stmt = $this->pdo->prepare( $this->sql );

		if ( $searchVal ) {
			$this->stmt->bindParam(':value', $searchVal);
		}

		$this->stmt->execute();

		return $this->stmt->fetchAll( PDO::FETCH_ASSOC );
	}

	public function updated() {
		if ( $this->stmt->rowCount() >= 1) {

			return true;
		} else {

			return false;
		}
	}
}