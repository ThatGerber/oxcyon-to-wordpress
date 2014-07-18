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
	protected $idArray;
	/**
	 * @var array
	 */
	protected $data;
	/**
	 * @var array
	 */
	protected $results;

	/**
	 * Le Constructor
	 *
	 * Takes a comma separated string, turns it into an array.
	 *
	 * Takes that array, and checks to see if it's
	 *
	 * @param string $csv
	 * @param array  $values
	 */
	public function __construct( $csv, $values ) {
		// Takes comma separated string and turns into array
		$this->data     = explode( ', ', $csv );
        // data to compare against
        $this->idArray  = $values;
    }

	/**
	 * Result Terms
	 *
	 * Takes an array of possible strings, checks if they're in the category array
	 * If they are, it adds to results array
	 *
	 * @return array
	 */
	public function resultTerms( ) {
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
}