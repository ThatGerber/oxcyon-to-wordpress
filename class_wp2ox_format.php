<?php
/**
 * Import Functions Page
 *
 * This will hold functions and classes related to the project import.
 *
 * @category    PHP
 * @copyright   2014
 * @license     WTFPL
 * @version     1.1.0
 * @since       2/18/2014
 */

/**
 * Class wp2ox_tidy
 *
 * Holds data and references for transferring between Wordpress and Oxcyon.
 */
class wp2ox_tidy extends tidy {

	/**
	 * @var array Configuration for Tidy
	 */
	public $config = array(
		"bare"              => true,
		"clean"             => true,
		"DocType"           => "omit",
		"drop-font-tags"    => true,
		"drop-proprietary-attributes" => true,
		"join-classes" => true,
		"merge-divs" => true,
		"merge-spans" => true,
		"output-encoding" => 'UTF8',
		"show-body-only" => true,
		"word-2000" => true,
	);

	/**
	 * @var array Array of items to find.
	 */
	public $find = Array(
		'<span>',     // No Spans
		'</span>',    // No Spans
		'<html>',    // No HTML
		'</html>',    // No HTML
		'<body>',    // No Body
		'</body>',    // No Body
		"\n",         // Get rid of newlines for wordpress
		'®',          // Registered (remove working)
		'Ã¢â‚¬Å“',    // left side double smart quote
		'Ã¢â‚¬Â',   // right side double smart quote
		'Ã¢â‚¬Ëœ',    // left side single smart quote
		'Ã¢â‚¬â„¢',   // right side single smart quote
		'â',          // single quote
		'Ã¢â‚¬Â¦',    // elipsis
		'Ã¢â‚¬â€',  // em dash
		'Ã¢â‚¬â€œ',   // en dash
		'Â',          // register
		'â¢',       // tm
	);

	/**
	 * @var array Items to put in place of the above items.
	 */
	public $replace = Array(
		" ", // Span open
		" ", // Span Close
		" ", // html open
		" ", // html Close
		" ", // Body open
		" ", // Body Close
		" ", // newlines
		'',  // Remove working (Reg)
		'"',
		'"',
		"'",
		"'",
		"'", // single quote
		"...",
		"-",
		"-",
		'®',
		'™', // tm
	);

	/**
	 * @param null|string $html HTML fragment to tidy up.
	 */
	function __construct( $html ) {
		// New Instance
		parent::__construct();
		// Run repairString with config.
		$new_html = $this->repairString( $html, $this->config, 'UTF8' );

		// Fixes the html by removing bad tags and replacing bad characters
		$fixed_html = $this->fix_html( $new_html, $this->find, $this->replace );

		return $fixed_html;
	}

	/**
	 * @param $string  string HTML fragment to fix
	 * @param $find    array  Array of data to replace
	 * @param $replace array  Array of data to insert
	 *
	 * @return string
	 */
	protected function fix_html( $string, $find, $replace ) {
		// Remove bad characters
		$new_html = str_replace( $find, $replace, $string );
		// Remove unnecessary / deprecated parts
		$final_html = $this->strip_html_tags( $new_html );

		return $final_html;
	}

	/**
	 * Strip HTML Tags
	 *
	 * Removes HTML tags from a document. It strips standard HTML tags to turn an HTML Doc
	 * into a string. Removes all content within the strings, as well. Use it to remove tags
	 * as well as remove content within it.
	 *
	 * @param $text string HTML fragment
	 *
	 * @return string $text Returns a string of text that has none of the formatting below.
	 */
	static function strip_html_tags( $text ) {
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

}

