<?php

/**
 * Class oxc_postTags
 * @extends oxc_postCategories
 *
 * @var $data
 * String from taxonomy column
 * @var $array
 * Array of Category IDs
 *
 * returns string of tags
 */
class oxc_postTags extends oxc_authorCategoryTag {

	/**
	 * @param $data
	 * @param $array
	 */
	public function __construct( $data, $array ) {
        parent::__construct($data, $array);
    }

    protected function resultTerms( ) {
        // Start the loop
        foreach ( $this->data as $string ) {
            // see if a category matches
            $newTerm = $this->validateData( $string );
            array_push( $newTerm, $this->results );
        }
        $tagResults = explode( ', ', $this->results );
        return $tagResults;
    }

}