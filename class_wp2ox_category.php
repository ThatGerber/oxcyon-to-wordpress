<?php

/**
 * Class oxc_postCategories
 * Returns array of category ID's (INT)
 *
 * @var $data   = string
 * @var $array  = reference table
 *
 * returns array
 */
class wp2ox_category {

	/**
	 * @var array
	 */
	public $idArray;
	/**
	 * @var array
	 */
	public $data;
	/**
	 * @var array
	 */
	public $results;

	/**
	 * Le Constructor
	 */
	public function __construct() { }

	/**
	 * Result Terms
	 *
	 * Takes an array of possible strings, checks if they're in the category array
	 * If they are, it adds to results array
	 *
	 * @return array
	 */
	public function get_categories( ) {

		if ( $this->data_is_empty() === TRUE ) {

			return NULL;
		}

		$this->data = explode( ', ', $this->data );

        foreach ( $this->data as $string ) {
            // see if a category matches
            $newTerm = $this->validateData( $string );
			if ($newTerm !== null ) {

				$this->results .= $newTerm . ', ';
			}
        }

		if ( $this->results !== null ) {

			return explode(', ', $this->results);
		}

		return null;
    }

	public function get_tags( ) {

		if ( $this->data_is_empty() === TRUE ) {

			return NULL;
		}

		$this->data = explode( ', ', $this->data );

		// Start the loop
		foreach ( $this->data as $string ) {
			// see if a category matches
			$newTerm = $this->validateData( $string );
			$this->results[] = $newTerm;
		}
		$tagResults = implode( ', ', $this->results );

		return $tagResults;
	}

	/**
	 * @param string $string
	 *
	 * @return null|mixed Returns new value on success, null on failure.
	 */
	protected function validateData( $string ) {

        $newId = array_search($string, $this->idArray);

        if ( $newId ) {

            return $newId;
        } else {

            return null;
        }
    }


	private function data_is_empty() {
		if (
			!isset( $this->idArray ) ||
			!isset( $this->data )
		) {

			return TRUE;
		}

		return FALSE;
	}

}