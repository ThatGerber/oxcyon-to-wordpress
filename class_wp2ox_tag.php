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
 *
 *
 * @deprecated Replaced with function within wp2ox_category
 */
class wp2ox_tag extends wp2ox_category {

	/**
	 * @param $data
	 * @param $array
	 */
	public function __construct( $data, $array ) {
        parent::__construct($data, $array);

    }

}