<?php
header('Content-type: text/html; charset=utf-8');
/**
 * Oxcyon to WordPress
 */

$import_folder = dirname( __FILE__ );
/** Required to load WordPress */
require_once( ABSPATH . '/wp-load.php');

/** The main class and sub / extended classes */
include( $import_folder . "/class_wp2ox.php");

/**
 * $wp2ox_data is the reference object used throughout the import. It'll hold various reference
 * arrays and other information used to by other classes. It also holds static functions needed
 * at various times.
 *
 * @var object $wp2ox_data
 */
$wp2ox_data            = new wp2ox;

/**
 * Update brand and category ID Value with each import
 *
 * Brand is the three letter code for the brand that we're working with in the old data. It's
 * prepended to the table for reference to each item's data.
 *
 * cat_value is the SQL "Like" statement used to capture just the categories. It should be
 * surrounded by '%' in order to search within string's for categories. portion of ModuleSID
 * to matched for category - "Category" Value
 */
$wp2ox_data->brand     = 'BDX';
$wp2ox_data->category_value = "%AAAAAAAAAAAAAAAA%";

/**
 * OLD DATABASE CONNECTION
 *
 * Username for database connection
 * Password to connect
 * Database to connect into
 */
$wp2ox_dbh             = new wp2ox_dal;
$wp2ox_dbh->dbusername = 'Dev_User';
$wp2ox_dbh->dbpassword = 'Welcome2013';
$wp2ox_dbh->database   = 'beverage_old';

/**
 * # AUTHORS #
 *
 * To import the authors, not much has to be done to the physical data. The data is just
 * lined up to match with the wordpress wp_insert_user() function. The wp_insert_user()
 * function will return an ID for that author. The ID is then matched up with the old,
 * Oxcyon ID and stored in the wp2ox_data object.
 *
 * *SQL*
 * First we set up the SQL statement. this calls the brand to target the author's table.
 * The goal is to target all of the data from the author table, wherever it may be. Adjust
 * accordingly.
 *
 * *Query*
 * Then, we perform the query to get the data. It returns a associated array of the data.
 * Create a new wp2ox_select_query with the PDO object and the SQL statement
 *
 * *Import*
 * The data is then imported into the database. We gather it into a bunch of variables that
 * are passed into the function to import the new user. Once the data is imported, the
 * user_id is stored in an array with the moduleSID so that it can be referenced later when
 * importing the next stories
 *
 */

// The SQL
$wp2ox_data->reportText( 'h3', 'Creating database connection to Author Table');
$wp2ox_author_sql = 'SELECT * FROM ' . $wp2ox_data->brand . '_authors_old';

// The Query
$oxc_authors = new wp2ox_select_query( $wp2ox_dbh, $wp2ox_author_sql );
$oxc_authorData = $oxc_authors->queryResults(); // query results
$wp2ox_data->reportText( 'h3', 'Adding New Users and creating Author reference table');

// Author Reference Array
$wp2ox_author_array = array();

// The Import
echo '<ul>';
foreach ( $oxc_authorData as $author ) {
	$moduleSID = $author['ModuleSID']; // unused
	$author_email = $author['Email'];
	$author_displayName = $author['Full Name'];
	$author_firstName = $author['First Name'];
	$author_lastName = $author['Last Name'];
	$author_description = wp_strip_all_tags( $author['Bio'] );
	$registerDate = date("Y-m-d H:i:s", strtotime($author['StartDate']));
	// Creating new author
	$newAuthor = array(
		"user_email" => $author_email,
		"display_name" => $author_displayName,
		"first_name" => $author_firstName,
		"last_name" => $author_lastName,
		"description" => $author_description,
		"rich_editing" => true,
		"user_registered" => $registerDate,
	);
	$userId = wp_insert_user( $newAuthor );

	/** Add the new user to the array */
	$wp2ox_author_array[ $userId ] = $moduleSID;

	/** Show results on the page */
	$wp2ox_data->reportText(
		'li',
		"$author_displayName - $moduleSID <strong>to</strong> $author_displayName -
		$author_userId"
	);
}
echo '</ul>';

// Push array of data to object
$wp2ox_data->authors = $wp2ox_author_array;

/**
 * # CATEGORIES #
 *
 * Importing the categories run much the same was importing the authors. The data is pulled
 * and matched up to import into the wp_create_category() function. The function returns a
 * category ID and that is matched with the old ID and stored in the wp2ox_data object
 *
 * *SQL*
 * First we set up the SQL statement. this calls the brand to target the author's table.
 * The goal is to target all of the data from the author table, wherever it may be. Adjust
 * accordingly.
 *
 * Since we have a value associated with our search term, we'll have to make sure that it
 * is added as a variable when we do our query.
 *
 * *Query*
 * Then, we perform the query to get the data. It returns a associated array of the data.
 * Create a new wp2ox_select_query with the PDO object and the SQL statement
 *
 * *Import*
 * The data is then imported into the database. We gather it into a bunch of variables that
 * are passed into the function to import the new user. Once the data is imported, the
 * user_id is stored in an array with the moduleSID so that it can be referenced later when
 * importing the next stories
 */
$wp2ox_data->reportText( 'h3', "Adding new Categories");

// The SQL
$wp2ox_category_sql = 'SELECT ModuleSID, Title
    FROM ' . $wp2ox_data->brand . '_Taxonomy_old
    WHERE Parent
    LIKE :value';

// The Query
$oxc_categories = new wp2ox_select_query( $wp2ox_dbh, $wp2ox_category_sql );
$oxc_category_data = $oxc_categories->queryResults( $wp2ox_data->category_value );

// Category Reference Array
$wp2ox_category_array = array();

// The Import
echo '<ul>';
foreach ( $oxc_category_data as $old_category ) {
	$new_cat_ID = wp_create_category( $old_category['Title'] );
	$wp2ox_category_array[$new_cat_ID] = $old_category['ModuleSID'];
	$wp2ox_data->reportText(
		'li',
		$old_category['title'] . " (" . $old_category['ModuleSID'] . ") to " . $new_cat_ID
	);
}
echo '</ul>';

// Store the Reference
$wp2ox_data->categories = $wp2ox_category_array;

/************************************************************************************
 * # TAGS #
 *
 * Importing the tags next. Because tags are imported on a "per post" basis, this step is
 * mainly executed in order to create the reference array that is called in a later function.
 * The data will be referenced during the post import.
 *
 * *SQL*
 * First we set up the SQL statement. this calls the brand to target the author's table.
 * The goal is to target all of the data from the author table, wherever it may be. Adjust
 * accordingly.
 *
 * Since we have a value associated with our search term, we'll have to make sure that it
 * is added as a variable when we do our query.
 *
 * *Query*
 * Then, we perform the query to get the data. It returns a associated array of the data.
 * Create a new wp2ox_select_query with the PDO object and the SQL statement
 *
 * *Import*
 * The data is then imported into the database. We gather it into a bunch of variables that
 * are passed into the function to import the new user. Once the data is imported, the
 * user_id is stored in an array with the moduleSID so that it can be referenced later when
 * importing the next stories
 */
$wp2ox_data->reportText( 'h3', "Creating Tag Reference Table");

// The SQL
$wp2ox_tag_sql = 'SELECT ModuleSID, Title
    FROM ' . $wp2ox_data->brand . '_Taxonomy_old'; //$oxc_tagSql

// The Query
$oxc_tags = new wp2ox_select_query( $wp2ox_dbh, $wp2ox_tag_sql );
$oxc_tag_data = $oxc_tags->queryResults();

/**
 * $oxc_tagId
 * Tag Reference Array
 * Key = Tag Title
 * Value = Old Tag ID
 */
$wp2ox_tag_array = array();

// The Import
echo '<ul>';
foreach ( $oxc_tag_data as $old_tag ) {
	$wp2ox_tag_array[ $old_tag['Title'] ] = $old_tag['ModuleSID'];
	$wp2ox_data->reportText(
		'li',
		$old_tag['ModuleSID'] . " - " . $old_tag['Title'] . " added to table"
	);
}
echo '</ul>';

// Store the Reference
$wp2ox_data->tags = $wp2ox_tag_array;

/************************************************************************************
 * # ARTICLES #
 *
 * This will do the hard work matching articles to their necessary information.
 *
 * *SQL*
 * The SQL statement grabs all of the data from the articles table and returns it as an
 * associative array.
 *
 * *Query*
 * The query pulls that array from the object and prepares it for the foreach() statement.
 *
 * *Import*
 * Each import takes a bit of time. The length of time the script needs to run depends on
 * how many articles we're importing into the database.
 *
 * Unique classes have been created to handle the information for each part of the post:
 * Author, category, and tags.
 *
 * The Body content is handled in a unique way. Because of all of the mis-formatted
 * information, it runs through a modified PHP Tidy class to clean up bad tags and prepare
 * the content to be used on the site. It takes a poorly formatted string and returns it
 * as a properly formatted HTML doc, stripping bad tags and non-"WordPress Post" content.
 *
 */
$wp2ox_data->reportText( 'h3', "Importing Articles");

// The SQL
$wp2ox_article_sql = 'Select * FROM ' . $wp2ox_data->brand . '_articles_Old';

// The Query
$old_articles = new wp2ox_select_query( $wp2ox_dbh, $wp2ox_article_sql );
$old_article_data = $old_articles->queryResults();

// The Import
$postNumber = 1; // iterate the query
foreach ( $old_article_data as $old_article ) {
	// Post Author
	$oxc_postAuthor     = new wp2ox_author( $old_article['Author'], $wp2ox_data->authors );
	// Post Categories
	$oxc_postCategories = new wp2ox_category( $old_article['Taxonomy'], $wp2ox_data->categories );
	// Post Tags
	$oxc_postTags       = new wp2ox_tag( $old_article['Taxonomy'], $wp2ox_data->tags );
	// Post Content
	$body_copy          = new wp2ox_tidy( mb_convert_encoding( $old_article['Body Copy'], 'UTF-8' ) );
	// The New Post
	$new_post = array(
		'post_content'   => $body_copy, // The full text of the post.
		'post_name'      => sanitize_title_with_dashes( $old_article['Title'] ), // The name (slug) for your post
		'post_title'     => sanitize_title( $old_article['Title'] ), // The title of your post.
		'post_author'    => intval( $oxc_postAuthor ), // The user ID number of the author. Default is the current user ID.
		'post_excerpt'   => wp_strip_all_tags( mb_convert_encoding( $old_article['Deck'], 'UTF-8' ) ), // For all your post excerpt needs.
		'post_date'      => date("Y-m-d H:i:s", strtotime($old_article['StartDate'])), // The time post was made.
		'post_date_gmt'  => date("Y-m-d H:i:s", strtotime( $old_article['StartDate'] ) - 1800 ), // The time post was made, in GMT.
		'comment_status' => 'open', // Default is the option 'default_comment_status', or 'closed'.
		'post_category'  => $oxc_postCategories, // Default empty. array( int, int, int )
		'tags_input'     => $oxc_postTags // Default empty. 'tag, tag, tag'
	);
	// Create Post
	$import = wp_insert_post( $new_post, true );
	// If it imported, report and increment
	if ( $import ) {
		$wp2ox_data->reportText( 'p', "Post Number: $postNumber added successfully");
		$postNumber++;
	}
}
// It's done. Move along.
$wp2ox_data->reportText( 'h2', "Import complete. $postnumber posts added to database.");