<?php

// Include for DB connection?
include("init.inc.php");

/*
 * Template Name: Post/Excerpt HTML Clean
 */
header('Content-type: text/html; charset=utf-8');

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
$db = new wp2ox( $bevData );
$sql = 'SELECT ID, post_content, post_excerpt
FROM wp_posts
WHERE post_type="post"';
$data = $db->getData( $sql );
$tidy = new tidy;
$config = array(
    "bare"              => true,
    "clean"             => true,
    "DocType"           => "omit",
    "drop-font-tags"    => true,
    "drop-proprietary-attributes" => true,
	"join-classes" => true,
	"merge-divs" => true,
    "merge-spans" => true,
	"show-body-only" => true,
    "word-2000" => true,
);
$find[] = '<span>';     // No Spans
$find[] = '</span>';    // No Spans
$find[] = '<html>';    // No HTML
$find[] = '</html>';    // No HTML
$find[] = '<body>';    // No Body
$find[] = '</body>';    // No Body
$find[] = "\n";         // Get rid of newlines for wordpress
$find[] = '®';          // Registered (remove working)
$find[] = 'Ã¢â‚¬Å“';    // left side double smart quote
$find[] = 'Ã¢â‚¬Â';   // right side double smart quote
$find[] = 'Ã¢â‚¬Ëœ';    // left side single smart quote
$find[] = 'Ã¢â‚¬â„¢';   // right side single smart quote
$find[] = 'â';          // single quote
$find[] = 'Ã¢â‚¬Â¦';    // elipsis
$find[] = 'Ã¢â‚¬â€';  // em dash
$find[] = 'Ã¢â‚¬â€œ';   // en dash
$find[] = 'Â';          // register
$find[] = 'â¢';       // tm

$replace[] = " "; // Span open
$replace[] = " "; // Span Close
$replace[] = " "; // html open
$replace[] = " "; // html Close
$replace[] = " "; // Body open
$replace[] = " "; // Body Close
$replace[] = " "; // newlines
$replace[] = '';  // Remove working (Reg)
$replace[] = '"';
$replace[] = '"';
$replace[] = "'";
$replace[] = "'";
$replace[] = "'"; // single quote
$replace[] = "...";
$replace[] = "-";
$replace[] = "-";
$replace[] = '®';
$replace[] = '™'; // tm

$find_replace = array(
	'<span>'   => "",    // No Spans
	'</span>'  => "",    // No Spans
	'<html>'   => "",    // No HTML
	'</html>'  => "",    // No HTML
	'<body>'   => "",    // No Body
	'</body>'  => "",    // No Body
	"\n"       => "",    // Get rid of newlines for wordpress
	'®'        => "",    // Registered (remove working)
	'Ã¢â‚¬Å“'  => '"',   // left side double smart quote
	'Ã¢â‚¬Â' => '"',   // right side double smart quote
	'Ã¢â‚¬Ëœ'  => "'",   // left side single smart quote
	'Ã¢â‚¬â„¢' => "'",   // right side single smart quote
	'â'        => "'",   // single quote
	'Ã¢â‚¬Â¦'  => "...", // elipsis
	'Ã¢â‚¬â€'=> "-",   // em dash
	'Ã¢â‚¬â€œ' => "-",   // en dash
	'Â'        => '®',   // register
	'â¢'     => '™',   // tm
);


$i = 0;
echo "Running convert </br>";
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