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
	reportText(
		'li',
		$old_tag['ModuleSID'] . " - " . $old_tag['Title'] . " added to table"
	);
}
echo '</ul>';

$wp2ox_data->tags = $wp2ox_tag_array;

/************************************************************************************
 * 4. ARTICLES
 *
 * Time to start iterating through the articles
 */
reportText( 'h3', "Importing Articles");

// The SQL
$wp2ox_article_sql = 'Select * FROM ' . $wp2ox_data->brand . '_articles_Old';

// The Query
$old_articles = new wp2ox_select_query( $wp2ox_dbh, $wp2ox_article_sql );
$old_article_data = $old_articles->queryResults();

// The Import
$postNumber = 1; // iterate the query
foreach ( $old_article_data as $old_article ) {
	// Author
	$oxc_postAuthor     = new oxc_authorCategoryTag( $old_article['Author'], $oxc_authorId );
	// Categories
	$oxc_postCategories = new oxc_authorCategoryTag( $old_article['Taxonomy'], $oxc_categoryID );
	// Tags
	$oxc_postTags       = new oxc_postTags( $old_article['Taxonomy'], $wp2ox_data->tags );

	// Post Data
	$oxc_post = array(
		// The full text of the post.
		'post_content'   => mb_convert_encoding( $oxc_row['Body Copy'], 'UTF-8' ),
		// The name (slug) for your post
		'post_name'      => sanitize_title_with_dashes( $oxc_row['Title'] ),
		// The title of your post.
		'post_title'     => sanitize_title( $oxc_row['Title'] ),
		// will need to import to a table
		// The user ID number of the author. Default is the current user ID.
		'post_author'    => 'Beverage Dynamics',
		// For all your post excerpt needs.
		'post_excerpt'   => mb_convert_encoding( $oxc_row['Deck'], 'UTF-8' ),
		// The time post was made.
		'post_date'      => date("Y-m-d H:i:s", strtotime($oxc_row['StartDate'])),
		// The time post was made, in GMT.
		'post_date_gmt'  => date("Y-m-d H:i:s", strtotime( $oxc_row['StartDate'] ) - 1800 ),
		// Default is the option 'default_comment_status', or 'closed'.
		'comment_status' => 'open',
		// Default empty. array( int, int, int )
		'post_category'  => $oxc_postCategories,
		// Default empty. 'tag, tag, tag'
		'tags_input'     => $oxc_postTags
	);
	// Create Post
	$import = wp_insert_post( $oxc_post, true );
	if ( $import ) {
		reportText( 'p', "Post Number: $postNumber added successfully");
		$postNumber++;
	}
}