<?php
header('Content-type: text/html; charset=utf-8');
/*
 * Template Name: Post/Excerpt HTML Clean
 */

// Include for DB connection?
include("init.inc.php");

echo "Beginning Conversion Process. <br />";


// Establish new PDO
try {
    //$bevData = new PDO("mysql:host=127.0.0.1;dbname=beverage_data", 'Dev_User', 'Welcome2013');
    $bevData = new PDO(
        "mysql:host=127.0.0.1;dbname=beverage_data", 'Dev_User', 'Welcome2013');
} catch ( Exception $e ) {
    echo 'Connection failed: ' . $e->getMessage();
}
// New class instance
$config = array(
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
$find = Array(
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
$replace = Array(
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


$i = 0;
echo "Running convert </br>";

$db = new wp2ox( $bevData );
$sql = 'SELECT ID, post_content, post_excerpt
FROM wp_posts
WHERE post_type="post"';
$data = $db->getData( $sql );

$tidy = new tidy;
foreach ($data as $row) {
    $id = $row["ID"];
    $oldExcerpt = $row["post_excerpt"];
    $oldPost = $row["post_content"];
    $tidyData = $tidy->repairString( $oldPost, $config, 'UTF8');
    $weirdData = str_replace($find, $replace, $tidyData);
    $newData =  strip_html_tags( $weirdData );
    $insert = array(
        ":post"     => $newData,
        ":postID"   => $id
    );
    $updateSQL = "UPDATE wp_posts SET post_content = :post WHERE ID = :postID";
    // Comment out when not running Code
    //$update = $db->getData($updateSQL, $insert);
    if ($update != 0) {
        $i++;
    }
}
echo "Conversion Complete.<br />";
echo "$i rows updated <br />";