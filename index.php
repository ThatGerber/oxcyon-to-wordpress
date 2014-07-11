<?php
/**
 * Oxcyon to WordPress
 */
$import_folder = dirname( __FILE__ );

require_once( ABSPATH . '/wp-load.php');

include( $import_folder . "/class_wp2ox.php");
include( $import_folder . "/class_wp2ox_select_query.php");

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
include( $import_folder . "/class_wp2ox_dal.php");
$wp2ox_dbh             = new wp2ox_dal;
$wp2ox_dbh->dbusername = 'Dev_User';
$wp2ox_dbh->dbpassword = 'Welcome2013';
$wp2ox_dbh->database   = 'beverage_old';

/**
 * # AUTHORS #
 *
 * Let's create our new Authors
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
reportText( 'h3', 'Creating database connection to Author Table');
$wp2ox_author_sql = 'SELECT * FROM ' . $wp2ox_data->brand . '_authors_old';

// The Query
$oxc_authors = new wp2ox_select_query( $wp2ox_dbh, $wp2ox_author_sql );
$oxc_authorData = $oxc_authors->queryResults(); // query results
reportText( 'h3', 'Adding New Users and creating Author reference table');

// Author reference data
$wp2ox_author_array = array();

// The Import
echo '<ul>';
foreach ( $oxc_authorData as $author ) {
	$moduleSID = $author['ModuleSID']; // unused
	$author_email = $author['Email'];
	$author_displayName = $author['Full Name'];
	$author_firstName = $author['First Name'];
	$author_lastName = $author['Last Name'];
	$author_description = $author['Bio'];
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
	reportText( 'li', "$author_displayName - $moduleSID <strong>to</strong> $author_displayName - $author_userId");
}
echo '</ul>';

// Push array of data to object
$wp2ox_data->authors = $wp2ox_author_array;

// Close the handle
//unset( $oxc_authors );

/**
 * # CATEGORIES #
 *
 * Importing the categories.
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
reportText( 'h3', "Adding new Categories");

// The SQL
$wp2ox_category_sql = 'SELECT ModuleSID, Title
    FROM ' . $wp2ox_data->brand . '_Taxonomy_old
    WHERE Parent
    LIKE :value';

// The Query
$oxc_categories = new wp2ox_select_query( $wp2ox_dbh, $wp2ox_category_sql );
$oxc_category_data = $oxc_categories->queryResults( $wp2ox_data->category_value );

// Reference arrays
$wp2ox_category_array = array();

// The Import
echo '<ul>';
foreach ( $oxc_category_data as $old_category ) {
	$new_cat_ID = wp_create_category( $old_category['Title'] );
	$wp2ox_category_array[$new_cat_ID] = $old_category['ModuleSID'];
	reportText(
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
 * Importing the tags next.
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
reportText( 'h3', "Creating Tag Reference Table");
$oxc_tagSql = 'SELECT ModuleSID, Title
    FROM ' . $oxc_importBrand . '_Taxonomy_old'; //$oxc_tagSql
$oxc_tags = new oxc_selectQuery( $oxc_bevOld ); // new select query
$oxc_tagData = $oxc_tags->queryResults( $oxc_tagSql ); // query results
/**
 * $oxc_tagId
 * Tag Reference Array
 * Key = Tag Title
 * Value = Old Tag ID
 */
$oxc_tagId = array();
echo '<ul>';
foreach ($oxc_tagData as $oxc_tag) {
	$oxc_tagId[ $oxc_tag['Title'] ] = $oxc_tag['ModuleSID'];
	reportText(
		'li',
		$oxc_tag['ModuleSID'] . " - " . $oxc_tag['Title'] . " added to table"
	);
}
echo '</ul>';

/**
 * 2. TAGS
 *
 * Now we're going to add create an array of Tags for Wordpress
 */
// sql statement - Same info, remove "WHERE"
$oxc_tagSql = 'SELECT ModuleSID, Title
    FROM ' . $wp2ox_obj->brand . '_Taxonomy_old';
// get the data and push it to a variable
$oxc_tagData = $oxc_bevOld->query( $oxc_tagSql );
/**
 * Tag Reference Array
 * Key = Tag Title
 * Value = Old Tag ID
 */
$oxc_tagId = array();
foreach ($oxc_tagData as $oxc_tag) {
    $oxc_tagId[$oxc_tag['Title']] = $oxc_tag['ModuleSID'];
}
/************************************************************************************
 * ARTICLES
 *
 * Time to start iterating through the articles
 */
$oxc_articlesSql = 'Select * FROM ' . $wp2ox_obj->brand . '_articles_Old';
$oxc_oldData = $oxc_bevOld->query( $oxc_articlesSql );
$oxc_oldData->setFetchMode(PDO::FETCH_ASSOC);
//global - used calculating dates
$oxc_seconds = 0;
while ( $oxc_row = $oxc_oldData->fetch() ) {
    //
    // Body Content - fix encoding on body content
    $oxc_bodyString = mb_convert_encoding( $oxc_row['Body Copy'], 'UTF-8' );
    //
    // Post Title (Used in slug)
    $oxc_title = sanitize_title( $oxc_row['Title'] );
    //
    // Post Excerpt
    $oxc_deck = mb_convert_encoding( $oxc_row['Deck'], 'UTF-8' );
    //
    // Post Date
    $oxc_postDate = date("Y-m-d H:i:s", strtotime($oxc_row['StartDate']));
    //
    // Post Date GMT
    $oxc_postDateGmt = date("Y-m-d H:i:s", strtotime( $oxc_row['StartDate'] ) - 1800 );
    //
    // Categories and Tags
    $oxc_postCategories = array();  // Category
    $oxc_tagArray       = array();  // Tags
    // Start the loop
    $oxc_taxArray = explode(', ', $oxc_row['Taxonomy']); // turn taxonomy into Comma string
    foreach ($oxc_taxArray as $oxc_category) {
        $oxc_tagTitle = array_search($oxc_category, $oxc_tagId);
        $oxc_catId = array_search($oxc_category, $oxc_categoryID);
        if ( $oxc_tagTitle ) {
            array_push( $oxc_tagsArray, array_search($oxc_category, $oxc_tagId) );
        }
        if ( $oxc_catId ) {
            array_push( $oxc_postCategories, array_search($oxc_category, $oxc_categoryID) );
        }
    }
    $oxc_postTags = implode(',', $oxc_tagArray); // Tags again
    //
    // Post Data
    $oxc_post = array(
        'post_content'   => $oxc_bodyString, // The full text of the post.
        'post_name'      => sanitize_title_with_dashes($oxc_title), // The name (slug) for your post
        'post_title'     => $oxc_title, // The title of your post.
        // will need to import to a table
        //'post_author'    => [ <user ID> ] // The user ID number of the author. Default is the current user ID.
        'post_excerpt'   => $oxc_deck, // For all your post excerpt needs.
        'post_date'      => $oxc_postDate, // The time post was made.
        'post_date_gmt'  => $oxc_postDateGmt, // The time post was made, in GMT.
        'comment_status' => 'open', // Default is the option 'default_comment_status', or 'closed'.
        'post_category'  => $oxc_postCategories, // Default empty.
        'tags_input'     => $oxc_postTags// Default empty.
    );
    wp_insert_post( $oxc_post, true );  // Create Post
}