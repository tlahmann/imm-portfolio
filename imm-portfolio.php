<?php
/**
 * @package imm
 */
/*
Plugin Name: imm Portfolio
Description: Portfolio-Plugin für die imm Website.
Version: 0.0.14
Author: Tobias Lahmann
Author URI: https://github.com/tlahmann
License: GPLv2 or later
Last Modifications: 
    restructure classes
*/

// Store the current plugin path in a global variable
define( 'IMM__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( IMM__PLUGIN_DIR . 'functions.php' );

// Call an instance from our class
require_once( IMM__PLUGIN_DIR . 'Subject.class.php' );
new Subject();

require_once( IMM__PLUGIN_DIR . 'Project.class.php' );
new Project();

if ( is_admin() ) {
    require_once( IMM__PLUGIN_DIR . 'ManyToManyLinker.class.php' );
    new ManyToManyLinker();
}
