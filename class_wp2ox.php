<?php
/**
 * Class wp2ox
 *
 * Imports the data. Connects with other classes to get information.
 *
 * Lots of god classes in here. Should be cleaned up and split apart more.
 *
 * @category    PHP
 * @copyright   2014
 * @license     WTFPL
 * @version     1.1.0
 * @since       2/18/2014
 */
class wp2ox {

	/**
	 * @var array $options Options array
	 */
	private $options;

	/**
	 * @var array $reference_array
	 */
	public $reference_array;


	/**
	 * @var object Array of Authors
	 */
	public $wp2ox_authors;

	/**
	 * @var object Array of Categories
	 */
	public $wp2ox_categories;

	/**
	 * Number of posts imported
	 *
	 * @var string $tags number of posts imported
	 */
	public $postNumber;

	/**
	 * Import the user settings and set up the object.
	 */
	function __construct() {

		$this->options = new wp2ox_data;

		/**
		 * DATABASE CONNECTION
		 */
		$this->wp2ox_dbh = new wp2ox_dal;

		if ( strlen( $this->options->taxonomy_table ) ) {
			/**
			 * IMPORT TAGS
			 */
			$this->import_tags();

			/**
			 * IMPORT AUTHORS
			 */
			$this->import_categories();
		}

		if ( strlen( $this->options->author_table ) ) {
			/**
			 * IMPORT AUTHORS
			 */
			$this->import_authors();
		}

		if ( strlen( $this->options->articles_table ) ) {
			/**
			 * IMPORT ARTICLES
			 */
			$this->import_articles();
		}
	}

	/**
	 * Adds a Reference Array
	 *
	 * Creates a nested array of reference data. To be used to query later on when importing articles.
	 *
	 * !IMPORTANT
	 * This will be deprecated in favor of wp2ox_data object.
	 *
	 * @param $name string|int Name of array to store it in.
	 * @param $val1 string|int Key to store
	 * @param $val2 string|int Value to store
	 *
	 * @return bool
	 */
	public function add_reference($name, $val1, $val2) {

		$array = $this->reference_array[$name];

		if ( $array[ $val1 ] = $val2 ) {

			return true;
		}

		return false;
	}

	/**
	 * Returns a reference array stored through add_reference
	 *
	 * !IMPORTANT
	 * This will be deprecated in favor of wp2ox_data object.
	 *
	 * @param $name string Name of array requested
	 *
	 * @return array
	 */
	public function get_reference_array($name) {

		return $this->reference_array[$name];
	}

	/**
	 * Prints the information. Wraps it in whatever you tell it to wrap in.
	 *
	 * @param $stringH string element tag, no brackets
	 * @param $string string String to wrap in brackets.
	 */
	static function reportText( $stringH, $string ) {

		echo '<' . $stringH . '>' . $string . '</' . $stringH . '>';
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
		$postNumber = 0; // iterate the query

		$articles = $this->wp2ox_dbh->get_articles();

		foreach ( $articles as $old_article ) {

			/** Set Up Author's Object */
			$oxc_postAuthor          = new wp2ox_author();
			$oxc_postAuthor->idArray = $this->wp2ox_authors;

			if ( isset( $old_article['Author'] ) ) {
				// Post Author
				$oxc_postAuthor->author_id = $old_article['Author'];
			}
			if ( isset( $old_article['Byline'] ) ) {
				$oxc_postAuthor->author_byline = $old_article['Byline'];
			}

			/** Set Up Categories Object */
			$oxc_postCategories = new wp2ox_category();
			$oxc_postCategories->idArray = $this->wp2ox_categories;
			if ( isset( $old_article['Taxonomy'] ) ) {
				$oxc_postCategories->data = $old_article['Taxonomy'];
			}

			/** Set Up Tags Object */
			$oxc_postTags = new wp2ox_category();
			$oxc_postTags->idArray = $this->get_reference_array('Tags');
			if ( isset( $old_article['1st Tier Type'] ) ){
				$oxc_postTags->data = $old_article['1st Tier Type'];
			}

			// Post Content
			$allowed_tags =
				array(
					'p' => array(),
					'a' => array(
						'href' => array(),
						'title' => array()
					),
					'br' => array(),
					'em' => array(),
					'strong' => array(),
					'i' => array(),
					'b' => array(),
					'div' => array(),
				);
			$body_text = wp_kses($old_article['Body Copy'], $allowed_tags);

			// The New Post
			$new_post = array(
				'post_content'   => $body_text,                                          // The full text of the post.
				'post_name'      => sanitize_title_with_dashes( $old_article['Title'] ), // The name (slug) for your post
				'post_title'     => $old_article['Title'],                               // The title of your post.
				'post_status'    => 'publish',                                           // Set to Publish
				'post_author'    => intval( $oxc_postAuthor->get_author() ),          // The user ID number of the author. Default is the current user ID.
				'post_excerpt'   => wp_strip_all_tags( mb_convert_encoding( $old_article['Caption'], 'UTF-8' ) ), // For all your post excerpt needs.
				'post_date'      => date("Y-m-d H:i:s", strtotime($old_article['StartDate'])), // The time post was made.
				'post_date_gmt'  => date("Y-m-d H:i:s", strtotime( $old_article['StartDate'] ) - 1800 ), // The time post was made, in GMT.
				'comment_status' => 'open', // Default is the option 'default_comment_status', or 'closed'.
				'post_category'  => $oxc_postCategories->get_categories(), // Default empty. array( int, int, int )
				'tags_input'     => $oxc_postTags->get_tags() // Default empty. 'tag, tag, tag'
			);
			// Create Post
			$import = wp_insert_post( $new_post, true );
			// If it imported, report and increment
			if ( $import ) {
				$postNumber++;

				wp2ox::reportText( 'p', "Post Number: {$postNumber} added successfully");

				if ( isset( $old_article['Byline'] ) && strlen( $old_article['Byline'] ) >= 1 ) {
					wp2ox::reportText('em', 'Byline added to post.');

					add_post_meta( $import, 'Byline', $old_article['Byline'] );

				}

				if ( strlen( $old_article['Image'] ) >= 1 ) {

					$featured_image = $this->import_featured_image( $import, $old_article['Image'], $this->options->image_dir );

					if ( $featured_image !== FALSE ) {
						wp2ox::reportText('em', 'Image added to post.');
					}
				}
			}

		}
		// It's done. Move along.
		wp2ox::reportText( 'h2', "Import complete. {$postNumber} posts added to database.");

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

		$authors = $this->wp2ox_dbh->get_authors();

		$this->wp2ox_authors = new wp2ox_data( FALSE );

		// The Import
		echo '<table>';

		foreach ( $authors as $author ) {
			$moduleSID = $author['ModuleSID']; // unused
			$author_email = $author['Email'];
			$author_displayName = $author['Full Name'];
			$author_firstName = $author['First Name'];
			$author_lastName = $author['Last Name'];
			$author_description = wp_strip_all_tags( $author['Bio'] );
			$registerDate = date("Y-m-d H:i:s", strtotime($author['StartDate']));
			// Creating new author
			$newAuthor = array(
				'user_login'      => strtolower( $author_firstName[0] . $author_lastName ),
				"user_email"      => $author_email,
				"display_name"    => $author_displayName,
				"first_name"      => $author_firstName,
				"last_name"       => $author_lastName,
				"description"     => $author_description,
				"rich_editing"    => true,
				"user_registered" => $registerDate,
			);
			$userId = wp_insert_user( $newAuthor );

			if( ! is_wp_error( $userId ) ) {

				/** Add the new user to the array */
				$this->wp2ox_authors->$userId    = $moduleSID;
				$this->wp2ox_authors->$moduleSID = $author_firstName . ' ' . $author_lastName;

				// deprecating
				$this->reference_array['Authors']["$userId"]    = $moduleSID;
				$this->reference_array['Authors']["$moduleSID"] = $author_firstName . ' ' . $author_lastName;


				/** Show results on the page */
				wp2ox::reportText(
					'tr',
					"<td>{$author_firstName} {$author_lastName} - {$moduleSID} </td><td><strong>to</strong></td><td> {$newAuthor['user_login']} - {$userId}</td>"
				);
			}
		}
		echo '</table>';
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

		$categories = $this->wp2ox_dbh->get_categories();

		$this->wp2ox_categories = new wp2ox_data( FALSE );

		// The Import
		echo '<table>';
		foreach ( $categories as $old_category ) {

			$new_cat_ID = wp_create_category( $old_category['Title'] );

			$this->wp2ox_categories->$new_cat_ID = $old_category['ModuleSID'];
			$this->reference_array['Categories']["$new_cat_ID"] = $old_category['ModuleSID'];

			wp2ox::reportText(
				'tr',
				"<td>" . $old_category['Title'] . "</td><td> (" . $old_category['ModuleSID'] . ") </td><td>to " . $new_cat_ID . "</td>"
			);
		}
		echo '</table>';

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

		$tags = $this->wp2ox_dbh->get_tags();

		// The Import
		echo '<table>';
		foreach ( $tags as $old_tag ) {

			$title = $old_tag['Title'];

			$this->reference_array['Tags']["$title"] = $old_tag['ModuleSID'];

			wp2ox::reportText(
				'tr',
				"<td>" . $old_tag['ModuleSID'] . " </td><td>-</td><td> " . $old_tag['Title'] . " </td><td>added to table"
			);

		}
		echo '</table>';

	}

	public function import_featured_image( $parent_post_id, $image_dir, $image_folder ) {

		// Break supplied path into parts
		$image_dir_parts = explode('/', $image_dir);

		// Get the file name from those parts
		$image_filename = end( $image_dir_parts );

		// Get the path to the upload directory.
		$wp_upload_dir = wp_upload_dir();

		// $filename should be the path to a file in the upload directory.
		$filename = WP_CONTENT_DIR . '/' . $image_folder . $image_filename;

		if ( file_exists( $filename ) ) {
			// Check the type of file. We'll use this as the 'post_mime_type'.
			$filetype = wp_check_filetype( basename( $filename ), null );

			// Prepare an array of post data for the attachment.
			$attachment = array(
				'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
				'post_mime_type' => $filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);

			// Insert the attachment.
			$attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );

			if ( $attach_id !== FALSE ) {
				// Generate the metadata for the attachment, and update the database record.
				$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );

				wp_update_attachment_metadata( $attach_id, $attach_data );

				if ( FALSE !== update_post_meta( $parent_post_id, '_thumbnail_id', $attach_id ) ) {

					return TRUE;
				}
			}
		}

		return FALSE;
	}

}


