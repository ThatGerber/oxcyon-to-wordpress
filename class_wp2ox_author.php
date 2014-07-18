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
	protected $author_id;
	/**
	 * @var mixed
	 */
	protected $results;

	protected $author_byline;

    // Construct
    public function __construct( $author_id, $array, $author_byline = null ) {
        // Author ID to look for
        $this->author_id    = $author_id;
		// Story Byline
		$this->author_byline = $author_byline;
        // data to compare against
        $this->idArray = $array;
    }
    // if the tag is match, add to array
    public function resultTerms( ) {
        // Start the loop

        return $this->validateData();
    }
    // checks to see if it's in array
    protected function validateData( ) {

		$byline = substr( $this->author_byline, 3 );
		// check the byline
		$byline_author = array_search( $byline, $this->idArray );

		if ( $byline_author ) {

			return array_search( $byline_author, $this->idArray );
		}

        $newId = array_search($this->author_id, $this->idArray);

        if ( $newId && $this->author_id != 'B8571AB171084BCAAE1DA200F6622E5D' ) {

            return $newId;
        } else {

            return false;
        }
    }
}