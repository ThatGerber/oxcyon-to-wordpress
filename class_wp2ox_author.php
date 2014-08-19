<?php
/**
 * Class wp2ox_authors
 */
class wp2ox_author {

	/**
	 * Source array
	 * @var array
	 */
	public $idArray;
	/**
	 * Check array
	 * @var array
	 */
	public $author_id;
	/**
	 * @var mixed
	 */
	public $results;

	/*
	 * Supplied author byline
	 */
	public $author_byline;

    // Construct
    public function __construct( ) { }

	/**
	 * checks to see if it's in array
	 *
	 * Returns array of category ID's (INT)
	 */
    public function get_author() {

		// Check if you should return the default author or not
		if ( $this->author_is_empty() === TRUE ) {

			return 1;
		}

		$byline = substr( $this->author_byline, 3 );
		// check the byline
		$byline_author = array_search( $byline, $this->idArray );

		if ( $byline_author ) {

			return array_search( $byline_author, $this->idArray );
		}

        $newId = array_search($this->author_id, $this->idArray);

        if ( $newId ) {

            return $newId;
        } else {

            return false;
        }
    }

	private function author_is_empty() {
		if (
		!isset( $this->idArray ) ||
		!isset( $this->author_id )
		) {

			return TRUE;
		}

		return FALSE;
	}


	/**
	 * if the tag is match, add to array
	 *
	 * @return bool|mixed
	 * @deprecated
	 *
	 * Just validate the data.
	 */
	public function resultTerms( ) {
		// Start the loop

		return $this->get_author();
	}
}