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
 * Class wp2ox
 *
 * Holds data and references for transferring between Wordpress and Oxcyon.
 */
class wp2ox {


	private $options;

	/**
	 * @var $brand string Which brand is being worked with.
	 */
	public $brand;

	/**
	 * @var $category_value string "Like" string for database search
	 */
	public $category_value;

	/**
	 * @var array $reference_array
	 */
	public $reference_array;

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
		$this->set_variables( get_option( 'wp2ox_settings' ) );

		/**
		 * DATABASE CONNECTION
		 */
		$wp2ox_dbh = new wp2ox_dal;
		$this->wp2ox_dbh = $wp2ox_dbh;

		$this->import_tags();
		$this->import_categories();
		$this->import_authors();
		$this->import_articles();
	}

	protected function set_variables( $option_group ) {
		$this->options        = $option_group;
		$this->brand          = $option_group['brand'];
		$this->category_value = $option_group['category_value'];
	}

	/**
	 * Adds a Reference Array
	 *
	 * Creates a nested array of reference data. To be used to query later on when importing articles.
	 *
	 * @param $name string|int Name of array to store it in.
	 * @param $val1 string|int Key to store
	 * @param $val2 string|int Value to store
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

			// Post Author
			$oxc_postAuthor     = new wp2ox_author(
				$old_article['Author'],
				$this->get_reference_array('Authors'),
				$old_article['Byline']
			);
			// Post Categories
			$oxc_postCategories = new wp2ox_category(
				$old_article['Taxonomy'],
				$this->get_reference_array('Categories')
			);
			// Post Tags
			$oxc_postTags       = new wp2ox_tag(
				$old_article['Taxonomy'],
				$this->get_reference_array('Tags')
			);
			// Post Content
			$body_copy          = new wp2ox_tidy( $old_article['Body Copy']	);
			// The New Post
			//var_dump( $oxc_postAuthor->resultTerms() );
			$new_post = array(
				'post_content'   => $body_copy->repaired_html, // The full text of the post.
				'post_name'      => sanitize_title_with_dashes( $old_article['Title'] ), // The name (slug) for your post
				'post_title'     => $old_article['Title'], // The title of your post.
				'post_status'    => 'publish',
				'post_author'    => intval( $oxc_postAuthor->resultTerms() ), // The user ID number of the author. Default is the current user ID.
				'post_excerpt'   => wp_strip_all_tags( mb_convert_encoding( $old_article['Deck'], 'UTF-8' ) ), // For all your post excerpt needs.
				'post_date'      => date("Y-m-d H:i:s", strtotime($old_article['StartDate'])), // The time post was made.
				'post_date_gmt'  => date("Y-m-d H:i:s", strtotime( $old_article['StartDate'] ) - 1800 ), // The time post was made, in GMT.
				'comment_status' => 'open', // Default is the option 'default_comment_status', or 'closed'.
				'post_category'  => $oxc_postCategories->resultTerms(), // Default empty. array( int, int, int )
				'tags_input'     => $oxc_postTags->resultTerms() // Default empty. 'tag, tag, tag'
			);
			// Create Post
			$import = wp_insert_post( $new_post, true );
			// If it imported, report and increment
			if ( $import ) {
				$postNumber++;
				wp2ox::reportText( 'p', "Post Number: {$postNumber} added successfully");

				$featured_image = $this->import_featured_image( $import, $old_article['Image'], $this->options['image_folder'] );

				if ( $featured_image !== FALSE ) {
					wp2ox::reportText('em', 'Image added to post');
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
				'user_login' => strtolower( $author_firstName[0] . $author_lastName ),
				"user_email" => $author_email,
				"display_name" => $author_displayName,
				"first_name" => $author_firstName,
				"last_name" => $author_lastName,
				"description" => $author_description,
				"rich_editing" => true,
				"user_registered" => $registerDate,
			);
			$userId = wp_insert_user( $newAuthor );

			if( ! is_wp_error( $userId ) ) {
				$this->reference_array['Authors']["$userId"] = $moduleSID;
				$this->reference_array['Authors']["$moduleSID"] = $author_firstName . ' ' . $author_lastName;

				/** Add the new user to the array */

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

		// The Import
		echo '<table>';
		foreach ( $categories as $old_category ) {

			$new_cat_ID = wp_create_category( $old_category['Title'] );

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
		$image_filename = end( array_values( $image_dir_parts ) );

		// Get the path to the upload directory.
		$wp_upload_dir = wp_upload_dir();

		// $filename should be the path to a file in the upload directory.
		$filename = $wp_upload_dir['path'] . $image_folder . $image_filename;

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

				if ( FALSE !== update_post_meta( $parent_post_id, 'thumbnail_id', $attach_id ) ) {

					return TRUE;
				}
			}
		}

		return FALSE;
	}

}


