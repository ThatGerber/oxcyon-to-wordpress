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
class wp2ox_author {

	/**
	 * Source array
	 * @var array
	 */
	protected $idArray;
	/**
	 * Check array
	 * @var array
	 */
	protected $data;
	/**
	 * @var mixed
	 */
	protected $results;

    // Construct
    public function __construct( $data, $array ) {
        // Author ID to look for
        $this->data    = $data;
        // data to compare against
        $this->idArray = $array;

        return $this->resultTerms();
    }
    // if the tag is match, add to array
    protected function resultTerms( ) {
        // Start the loop
		$this->results = $this->validateData( $this->data );

        return $this->results;
    }
    // checks to see if it's in array
    protected function validateData( $string ) {
        $newId = array_search($string, $this->idArray);
        if ( $newId ) {

            return $newId;
        } else {

            return false;
        }
    }
}