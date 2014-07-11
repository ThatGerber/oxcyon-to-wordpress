<?php
/**
 * Template Name: Oxcyon Import
 */
// Need this to call WP functions
require_once( ABSPATH . 'wp-load.php');
$oxc_root = get_theme_root();
include ( $oxc_root .  '/bones/import-oxcyon-functions.php');
/**
 * Oxcyon to WordPress
 * Update with each import:
 */
$oxc_importBrand = 'BDX'; // Three letter code for brand
$oxc_categoryIdValue = "%AAAAAAAAAAAAAAAA%"; // "Category" Value, from ModuleSID

/************************************************************************************
 * OLD DATABASE CONNECTION
 */
$oxc_pdoUsername    = 'Dev_User';
$oxc_pdoPassword    = 'Welcome2013';
try {
    $oxc_bevOld = new PDO(
        "mysql:host=127.0.0.1;dbname=beverage_old",
        $oxc_pdoUsername,
        $oxc_pdoPassword);
} catch ( Exception $oxc_e ) {
    echo 'Connection failed: ' . $oxc_e->getMessage();
}
/************************************************************************************
 * 1. AUTHORS
 *
 * Let's create our new Authors
 */
$oxc_authors = new oxc_selectQuery( $oxc_bevOld ); // new select query
reportText( 'p', 'Creating database connection to Author Table');
$oxc_authorSql = 'SELECT * FROM ' . $oxc_importBrand . '_authors_old'; // the query
$oxc_authorData = $oxc_authors->queryResults( $oxc_authorSql ); // query results
reportText( 'h3', 'Adding New Users and creating Author reference table');
echo '<ul>';
// author reference data
$oxc_authorId = array();
foreach ( $oxc_authorData as $author ) {
    $moduleSID = $author['ModuleSID']; // unused
    $email = $author['Email'];
    $displayName = $author['Full Name'];
    $firstName = $author['First Name'];
    $lastName = $author['Last Name'];
    $description = $author['Bio'];
    $richEditing = true;
    $registerDate = date("Y-m-d H:i:s", strtotime($author['StartDate']));
    // Creating new author
    $newAuthor = array(
        "user_email" => $author['Email'],
        "display_name" => $author['Full Name'],
        "first_name" => $author['First Name'],
        "last_name" => $author['Last Name'],
        "description" => $author['Bio'],
        "rich_editing" => true,
        "user_registered" => date("Y-m-d H:i:s", strtotime($author['StartDate']))
    );
    $userId = wp_insert_user( $newAuthor );
    $oxc_authorId[ $userId ] = $author['ModuleSID'];
    // report to page
    reportText( 'li', "$displayName - $moduleSID to $displayName - $userId");
}
echo '</ul>';
// Close the handle
unset( $oxc_authors );
/************************************************************************************
 * 2. CATEGORIES
 *
 * Now we're going to add new categories to Wordpress
 */
reportText( 'h3', "Adding new Categories");
$oxc_catSql = 'SELECT ModuleSID, Title
    FROM ' . $oxc_importBrand . '_Taxonomy_old
    WHERE Parent
    LIKE :value';
$oxc_search = array(":value" => $oxc_categoryIdValue); // search variable
$oxc_categories = new oxc_selectQuery( $oxc_bevOld ); // new select query
$oxc_taxData = $oxc_categories->queryResults( $oxc_catSql, $oxc_search ); // query results
/**
 * Category Reference Array
 * Key = Old Category ID
 * Value = New Category ID
 */
// create new categories and push them to array;
$oxc_categoryID = array();
$oxc_categoryTitle = array();
echo '<ul>';
foreach ($oxc_taxData as $oxc_category) {
    $oxc_catID = wp_create_category( $oxc_category['Title'] );
    $oxc_categoryID[ $oxc_catID ] = $oxc_category['ModuleSID'];
    reportText(
        'li',
        $oxc_category['ModuleSID'] . " - " . $oxc_category['title'] . " to " . $oxc_catID
    );
}
echo '</ul>';
// Close the handle
unset( $oxc_categories );
/************************************************************************************
 * 3. TAGS
 *
 * Now we're going to add create an array of Tags for Wordpress
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
    $oxc_tagId[$oxc_tag['Title']] = $oxc_tag['ModuleSID'];
    reportText(
        'li',
        $oxc_tag['ModuleSID'] . " - " . $oxc_tag['Title'] . " added to table"
    );
}
echo '</ul>';
// Close the handle
unset( $oxc_tags );

/************************************************************************************
 * 4. ARTICLES
 *
 * Time to start iterating through the articles
 */
reportText( 'h3', "Importing Articles");
$oxc_articlesSql = 'Select * FROM ' . $oxc_importBrand . '_articles_Old';
$oxc_articles = new oxc_selectQuery( $oxc_bevOld ); // new select query
$oxc_articleData = $oxc_tags->queryResults( $oxc_articlesSql ); // query results
// iterate the query
$postNumber = 1;
foreach ( $oxc_articleData as $oxc_row ) {
    // Author
    $oxc_postAuthor = new oxc_authorCategoryTag( $oxc_row['Author'], $oxc_authorId );
    // Categories
    $oxc_postCategories = new oxc_authorCategoryTag( $oxc_row['Taxonomy'], $oxc_categoryID );
    // Tags
    $oxc_postTags      = new oxc_postTags( $oxc_row['Taxonomy'], $oxc_tagId );

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
// Close the handle
unset( $oxc_articles );
// Complete. Check the database.