<?php
/**
 * Class wp2ox_admin
 *
 * Displays the admin page for the importer.
 *
 * @TODO Register page as a WordPress import tool.
 *
 * @category    PHP
 * @copyright   2014
 * @license     WTFPL
 */
class wp2ox_admin {

	/**
	 * Holds the values to be used in the fields callbacks
	 * @var array
	 */
	private $options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_management_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );

	}

	public function register_import_page() {
		register_importer( 'wp2ox', 'Oxcyon to WordPress', 'Import an Oxcyon Website into WordPress', array( $this, 'page_init' ) );
	}

	/**
	 * Add Tools Page
	 */
	public function add_management_page() {

		add_management_page(
			'Oxcyon to WordPress Import', // Page Title
			'Oxcyon 2 WP',                // Admin Menu Title
			'manage_options',             // Capabilities Needed
			'wp2ox',                      // Menu Slug
			array( $this, 'wp2ox_admin_form' ) // Function to run
		);

	}

	/**
	 * Run to create the settings page.
	 */
	public function page_init() {

		// Register settings
		$this->wp2ox_register_settings();

		// Add sections to settings page.
		$this->wp2ox_add_sections();

	}

	/**
	 * Register the plugin settings.
	 */
	public function wp2ox_register_settings() {
		// register our settings
		register_setting(
			'wp2ox-settings-group',
			'wp2ox_settings',
			array( $this, 'wp2ox_options_validate' )
		);
	}

	/**
	 * Create form for plugin settings.
	 */
	public function wp2ox_add_sections() {

		/** Basic Settings */
		add_settings_section(
			'basic_settings', // ID
			'Import Settings', // Title
			array( $this, 'basic_section_callback' ), // Callback
			'wp2ox_basic' // Page
		);

		/** Brand */
		add_settings_field(
			'image_dir', // ID
			'Image Directory', // Title
			array( $this, 'image_callback' ), // Callback
			'wp2ox_basic', // Page
			'basic_settings' // Section
		);

		/** Category Value */
		add_settings_field(
			'category_value',
			'Category Value',
			array( $this, 'cat_value_callback' ),
			'wp2ox_basic',
			'basic_settings'
		);

		/** Database Settings */
		add_settings_section(
			'database_settings', // ID
			'Database Settings', // Title
			array( $this, 'db_section_callback' ), // Callback
			'wp2ox_database' // Page
		);

		/** Database Username */
		add_settings_field(
			'db_user', // ID
			'Database Username', // Title
			array( $this, 'db_user_callback' ), // Callback
			'wp2ox_database', // Page
			'database_settings' // Section
		);

		/** Database password */
		add_settings_field(
			'db_pass', // ID
			'Database Password', // Title
			array( $this, 'db_pass_callback' ), // Callback
			'wp2ox_database', // Page
			'database_settings' // Section
		);

		/** Database Name */
		add_settings_field(
			'db_name', // ID
			'Database Name', // Title
			array( $this, 'db_name_callback' ), // Callback
			'wp2ox_database', // Page
			'database_settings' // Section
		);

		/** Tables Settings */
		add_settings_section(
			'tables_settings', // ID
			'Table Settings', // Title
			array( $this, 'table_section_callback' ), // Callback
			'wp2ox_tables' // Page
		);

		/** Author Table Name */
		add_settings_field(
			'aut_table', // ID
			'Author Table', // Title
			array( $this, 'aut_table_callback' ), // Callback
			'wp2ox_tables', // Page
			'tables_settings' // Section
		);

		/** Taxonomy Table Name */
		add_settings_field(
			'tax_table', // ID
			'Taxonomy Table', // Title
			array( $this, 'tax_table_callback' ), // Callback
			'wp2ox_tables', // Page
			'tables_settings' // Section
		);

		/** Article Table Name */
		add_settings_field(
			'art_table', // ID
			'Article Table', // Title
			array( $this, 'art_table_callback' ), // Callback
			'wp2ox_tables', // Page
			'tables_settings' // Section
		);
	}

	/**
	 * Options page callback
	 */
	public function wp2ox_admin_form () {

		$this->options = get_option( 'wp2ox_settings' );
		$import_script = WP2OX_DIR . '/wp2ox_import.php';

		?>
		<div class="wrap">

			<h2>Oxcyon to WordPress Import</h2>

		<?php if ( !isset( $_POST['import_wp2ox'] ) ) { ?>

			<p>
				Enter the necessary values below. After all of the fields have been set, press "Import" to run the
				import script.
			</p>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'wp2ox-settings-group' );

				$this->settings_section_wrap( 'wp2ox_basic' );

				$this->settings_section_wrap( 'wp2ox_database' );

				$this->settings_section_wrap( 'wp2ox_tables' );
				?>

			</form>

			<form method="post" action="">
				<p class="submit">
					<input type="submit" value="Import the Data" name="import_wp2ox" class="button " />
				</p>

			</form>

		<?php } else {

			include( $import_script );

			new wp2ox;

		} ?>

		</div>
	<?php
	}

	/**
	 * Sanitize and validate input. Accepts an array, return a sanitized array.
	 */
	public function wp2ox_options_validate( $input ) {
		// Say our second option must be safe text with no HTML tags

		$new_input = array();

		$new_input['image_dir']      = wp_filter_nohtml_kses( $input[ 'image_dir' ] );
		$new_input['category_value'] = wp_filter_nohtml_kses( $input[ 'category_value' ] );
		$new_input['db_username']    = wp_filter_nohtml_kses( $input[ 'db_username' ] );
		$new_input['db_password']    = wp_filter_nohtml_kses( $input[ 'db_password' ] );
		$new_input['db_name']        = wp_filter_nohtml_kses( $input[ 'db_name' ] );
		$new_input['author_table']   = wp_filter_nohtml_kses( $input[ 'author_table' ] );
		$new_input['taxonomy_table'] = wp_filter_nohtml_kses( $input[ 'taxonomy_table' ] );
		$new_input['articles_table'] = wp_filter_nohtml_kses( $input[ 'articles_table' ] );

		return $new_input;
	}

	/**
	 * Wraps each settings section in a pretty box.
	 */
	public function settings_section_wrap( $string = null ) {
		?>
		<div class="postbox ">
			<div class="inside">
				<?php do_settings_sections( $string ); ?>
			</div>
		</div>

		<?php submit_button( 'Save All Settings' );
	}

	public function basic_section_callback() {
		?>
		<p>Tell the script where to look for images and categories.</p>
	<?php
	}
	public function db_section_callback() {
		?>
		<p>Set the database values for the data to be imported.</p>
	<?php
	}
	public function table_section_callback() {
		?>
		<p>Set the values of the tables to target with the import.</p>
	<?php
	}

	public function image_callback() {
		?>
		<label for="wp2ox_settings[image_dir]">
			<input type="text" name="wp2ox_settings[image_dir]" value="<?php echo $this->options['image_dir']; ?>" />
			<em>Directory of Images (<?php echo WP_CONTENT_DIR; ?>/{<strong>Image Directory/</strong>})</em>
		</label>
		<p>
			Directory that the script should look for within the "Uploads" folder, with the trailing slash.
			For example, if images are located in <code>wp-content/uploads/archive/</code>, enter <code>archive/</code>
			in the above field.
		</p>
	<?php
	}

	public function cat_value_callback() {
		?>
		<p>
			<label for="wp2ox_settings[category_value]"><input type="text" name="wp2ox_settings[category_value]" value="<?php echo $this->options['category_value']; ?>" /> <em>Valid MySQL REGEXP statement</em></label>
		</p>
		<p>
			The category value is a REGEXP statement to target categories within that table. Example:
			<code>[A-Ea-e][A-Ea-e][A-Ea-e][A-Ea-e][A-Ea-e][A-Ea-e]</code>
		</p>
	<?php
	}

	public function db_user_callback() {
		?>
		<p>
			<label for="wp2ox_settings[db_username]">
				<input type="text" name="wp2ox_settings[db_username]" value="<?php echo $this->options['db_username']; ?>" />
				<em>Username to access database with old data.</em>
			</label>
		</p>
	<?php
	}

	public function db_pass_callback() {
		?>
		<p>
			<label for="wp2ox_settings[db_password]">
				<input type="text" name="wp2ox_settings[db_password]" value="<?php echo $this->options['db_password']; ?>" />
				<em>Password to access database with old data.</em>
			</label>
		</p>
	<?php
	}

	public function db_name_callback() {
		?>
		<p>

		</p>
		<p>
			<label for="wp2ox_settings[db_name]">
				<input type="text" name="wp2ox_settings[db_name]" value="<?php echo $this->options['db_name']; ?>" />
				<em>Name of Database containing old data.</em>
			</label>
		</p>
	<?php
	}

	public function aut_table_callback() {
		?>
		<p>
			<label for="wp2ox_settings[author_table]">
				<input type="text" name="wp2ox_settings[author_table]" value="<?php echo $this->options['author_table']; ?>" />
				<em>Full name of the author table to be imported.</em>
			</label>
		</p>
	<?php
	}

	public function tax_table_callback() {
		?>
		<p>
			<label for="wp2ox_settings[taxonomy_table]">
				<input type="text" name="wp2ox_settings[taxonomy_table]" value="<?php echo $this->options['taxonomy_table']; ?>" />
				<em>Full name of taxonomy table to be imported.</em>
			</label>
		</p>
	<?php
	}

	public function art_table_callback() {
		?>
		<p>
			<label for="wp2ox_settings[articles_table]">
				<input type="text" name="wp2ox_settings[articles_table]" value="<?php echo $this->options['articles_table']; ?>" />
				<em>Full name of articles table to be imported.</em>
			</label>
		</p>
	<?php
	}

}

new wp2ox_admin();