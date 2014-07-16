<?php
/**
 * Articles
 *
 * Time to start iterating through the articles
 */
$articlesSql = 'Select * FROM BDX_articles_Old';
$oldData = $bevOld->query( $articlesSql );
$oldData->setFetchMode(PDO::FETCH_ASSOC);
//global - used calculating dates
$seconds = 0;
while ( $row = $oldData->fetch() ) {
    //
    // Body Content - fix encoding on body content
    $bodyString = mb_convert_encoding( $row['Body Copy'], 'UTF-8' );
    //
    // Post Title (Used in slug)
    $title = sanitize_title( $row['Title'] );
    //
    // Post Excerpt
    $deck = mb_convert_encoding( $row['Deck'], 'UTF-8' );
    //
    // Post Date
    $postDate = date("Y-m-d H:i:s", strtotime($row['StartDate']));
    //
    // Post Date GMT
    $gmtTimestamp = strtotime( $row['StartDate'] ) - 1800;
    $postDateGmt = date("Y-m-d H:i:s", $gmtTimestamp );
    //
    // Categories and tags
    // References
    $tagIdArray = array(); // from categories import
    $postCategoriesArray = array(); // from categories import
    // Category
    $postCategories = array();
    // Tags
    $tagArray = array();
    // Start the loop
    $taxArray = explode(', ', $row['Taxonomy']); // turn taxonomy into Comma string
    foreach ($taxArray as $category) {
        $tagTitle = array_search($category, $tagIdArray);
        $catId = array_search($category, $postCategoriesArray);

        if ( $tagTitle ) {
            array_push( $tagsArray, array_search($category, $tagTitle) );
        }
        if ( $catId ) {
            array_push( $postCategories, array_search($category, $postCategoriesArray) );
        }
    }
    //
    // Tags again
    $postTags = implode(',', $tagArray);
    //
    // Post Data
    $post = array(
        'post_content'   => $bodyString, // The full text of the post.
        'post_name'      => sanitize_title_with_dashes($title), // The name (slug) for your post
        'post_title'     => $title, // The title of your post.
        // will need to import to a table
        //'post_author'    => [ <user ID> ] // The user ID number of the author. Default is the current user ID.
        'post_excerpt'   => $deck, // For all your post excerpt needs.
        'post_date'      => $postDate, // The time post was made.
        'post_date_gmt'  => $postDateGmt, // The time post was made, in GMT.
        'comment_status' => 'open', // Default is the option 'default_comment_status', or 'closed'.
        'post_category'  => $postCategories, // Default empty.
        'tags_input'     => $postTags// Default empty.
    );
    //
    // Create Post
    wp_insert_post( $post, true );
}