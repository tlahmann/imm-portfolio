<?php
/**
 * @package WordPress
 * @subpackage imm
 */
/*
Plugin Name: imm Portfolio
Description: Portfolio-Plugin für die imm Website. Das Plugin liefert die Custom Post types <strong>Subject</strong> - Ein vom imm angebotenes Fach - <strong>Project</strong> - Ein in einem Fach durchgeführtes Projekt
Version: 0.0.23
Author: Tobias Lahmann
Author URI: https://github.com/tlahmann
License: GPLv2 or later
*/

// Store the current plugin path in a global variable
define( 'IMM__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * ==================================================
 * Load the functions associated with this plugin.
 * ==================================================
 */
require_once( IMM__PLUGIN_DIR . 'functions.php' );

// Call an instance from the project class
require_once( IMM__PLUGIN_DIR . 'Project.class.php' );
new Project();

// Call an instance from the subject class
require_once( IMM__PLUGIN_DIR . 'Subject.class.php' );
new Subject();
