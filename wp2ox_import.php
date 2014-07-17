<?php
/**
 * Oxcyon to WordPress
 */
$import_folder = dirname( __FILE__ );

/** Required to load WordPress */
require_once( ABSPATH . '/wp-load.php');

/** The main class and sub / extended classes */
include( $import_folder . "/class_wp2ox.php");

class wp2ox_import {

	private $wp2ox_data;

	private $wp2ox_dbh;

	public function __construct() {
		/**
		 * $wp2ox_data is the reference object used throughout the import. It'll hold various reference
		 * arrays and other information used to by other classes. It also holds static functions needed
		 * at various times.
		 *
		 * @var object $wp2ox_data
		 */
		$wp2ox_data = new wp2ox;
		$this->wp2ox_data = $wp2ox_data;

		/**
		 * DATABASE CONNECTION
		 */
		$wp2ox_dbh = new wp2ox_dal;
		$this->wp2ox_dbh = $wp2ox_dbh;

		$this->import_authors();
		$this->import_categories();
		$this->import_tags();
		$this->import_articles();
	}

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
	public function import_articles() {
		wp2ox::reportText( 'h3', "Importing Articles");

		// The Import
		$this->wp2ox_data->postNumber = 1; // iterate the query

		foreach ( $this->wp2ox_dbh->get_articles() as $old_article ) {
			// Post Author
			$oxc_postAuthor     = new wp2ox_author( $old_article['Author'], $this->wp2ox_data->get_reference_array('Authors') );
			// Post Categories
			$oxc_postCategories = new wp2ox_category( $old_article['Taxonomy'], $this->wp2ox_data->get_reference_array('Categories') );
			// Post Tags
			$oxc_postTags       = new wp2ox_tag( $old_article['Taxonomy'], $this->wp2ox_data->get_reference_array('Tags') );
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
				wp2ox::reportText( 'p', "Post Number: {$this->wp2ox_data->postNumber} added successfully");
				$this->wp2ox_data->postNumber++;
			}
		}
		// It's done. Move along.
		wp2ox::reportText( 'h2', "Import complete. {$this->wp2ox_data->postnumber} posts added to database.");

	}
	/**
	 * # AUTHORS #
	 *
	 * To import the authors, not much has to be done to the physical data. The data is just
	 * lined up to match with the WordPress wp_insert_user() function. The wp_insert_user()
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

	public function import_authors() {
		wp2ox::reportText( 'h3', 'Adding New Users.');

		// The Import
		echo '<ul>';
		foreach ( $this->wp2ox_dbh->get_authors() as $author ) {
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
			$this->wp2ox_data->add_reference('Authors', $userId, $moduleSID);

			/** Show results on the page */
			wp2ox::reportText(
				'li',
				"{$author_displayName} - {$moduleSID} <strong>to</strong> {$author_displayName} - {$userId}"
			);
		}
		echo '</ul>';
	}

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
	public function import_categories() {
		wp2ox::reportText( 'h3', "Adding new Categories");

		// The Import
		echo '<ul>';
		foreach ( $this->wp2ox_dbh->get_categories() as $old_category ) {

			$new_cat_ID = wp_create_category( $old_category['Title'] );

			$this->wp2ox_data->add_reference('Categories', $new_cat_ID, $old_category['ModuleSID']);

			wp2ox::reportText(
				'li',
				$old_category['title'] . " (" . $old_category['ModuleSID'] . ") to " . $new_cat_ID
			);
		}
		echo '</ul>';

	}

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
	public function import_tags() {
		wp2ox::reportText( 'h3', "Creating Tag Reference Table");

		// The Import
		echo '<ul>';
		foreach ( $this->wp2ox_dbh->get_tags() as $old_tag ) {

			$this->wp2ox_data->add_reference('Tags', $old_tag['Title'], $old_tag['ModuleSID'] );

			wp2ox::reportText(
				'li',
				$old_tag['ModuleSID'] . " - " . $old_tag['Title'] . " added to table"
			);

		}
		echo '</ul>';

	}
}

new wp2ox_import;