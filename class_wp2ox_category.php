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
    // source data for matching
    protected $idArray = Array();
    // old categories
    protected $data = Array();
    // return data
    protected $results;

    // Construct
    public function __construct( $data, $array ) {
        // bust up string into array
        $this->data     = explode( ', ', $data ); // $oxc_row['Taxonomy'];
        // data to compare against
        $this->idArray  = $array;
        return $this->resultTerms();
    }
    // if the tag is match, add to array
    protected function resultTerms( ) {
        // Start the loop
        foreach ( $this->data as $string ) {
            // see if a category matches
            $newTerm = $this->validateData( $string );
            array_push( $newTerm, $this->results );
        }
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