<?php
/**
 * Plugin Name: Oxcyon to WordPress
 * Plugin URI:
 * Description: Converts existing information from MySQL database to WordPress data.
 * Version: 0.3.0
 * Author: Christopher Gerber
 * Author URI: http://www.chriswgerber.com/
 * License: WTFPL
 */
/*
       DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
                    Version 2, December 2004

 Copyright (C) 2014 Christopher Gerber <chriswgerber@gmail.com>

 Everyone is permitted to copy and distribute verbatim or modified
 copies of this license document, and changing it is allowed as long
 as the name is changed.

            DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
   TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION

  0. You just DO WHAT THE FUCK YOU WANT TO.
 */

/** Constants */

// /path/to/plugin/file
define( 'WP2OX_DIR', dirname( __FILE__ ) );

// http://website.com/plugins/{WP2OX_URL}
define( 'WP2OX_URL', plugins_url() );

// Display admin page if working on the back-end.

if ( is_admin() ) {
	include( WP2OX_DIR . '/admin/wp2ox_admin.php');
}
