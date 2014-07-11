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
class oxc_authorCategoryTag {
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

function strip_html_tags( $text ) {
	$text = preg_replace(
		[
			'@<head[^>]*?>.*?</head>@siu',
			'@<style[^>]*?>.*?</style>@siu',
			'@<title[^>]*?>.*?</title>@siu',
			'@<script[^>]*?.*?</script>@siu',
			'@<object[^>]*?.*?</object>@siu',
			'@<embed[^>]*?.*?</embed>@siu',
			'@<applet[^>]*?.*?</applet>@siu',
			'@<noframes[^>]*?.*?</noframes>@siu',
			'@<noscript[^>]*?.*?</noscript>@siu',
			'@<noembed[^>]*?.*?</noembed>@siu',
			"/class\s*=\s*'[^\']*[^\']*'/"
		],
		array('', '', '', '', '', '', '', '', '', '', ''),
		$text );

	return $text;
}


function reportText( $stringH, $string ) {
	echo '<' . $stringH . '>' . $string . '</' . $stringH . '>';
}