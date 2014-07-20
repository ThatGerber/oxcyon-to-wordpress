<?php
/**
 * Oxcyon to WordPress
 */
$import_folder = dirname( __FILE__ );

/** The main class and sub / extended classes */
include( $import_folder . "/class_wp2ox.php");

// Database abstraction layer
include( $import_folder . "/class_wp2ox_dal.php");

// Tidy
include( $import_folder . "/class_wp2ox_format.php");

// Author/Category creator
include( $import_folder . "/class_wp2ox_author.php");

// Category
include( $import_folder . "/class_wp2ox_category.php");

// Tag Creator
include( $import_folder . "/class_wp2ox_tag.php");


new wp2ox;
