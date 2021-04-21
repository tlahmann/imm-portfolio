<?php
/**
 * @package WordPress
 * @subpackage imm
 */
/*
Plugin Name: imm Portfolio
Description: Portfolio-Plugin für die imm Website. Das Plugin liefert die Custom Post types <strong>Subject</strong> - Ein vom imm angebotenes Fach - <strong>Project</strong> - Ein in einem Fach durchgeführtes Projekt
Version: 0.2.1
Author: <a href="https://github.com/tlahmann" target="_blank">Tobias Lahmann</a> and <a href="https://github.com/JuliusSchuerrle" target="_blank">Julius Schürrle</a>
License: GPLv2 or later
*/

// Store the current plugin path in a global variable
define('IMM__PLUGIN_DIR', plugin_dir_path(__FILE__));

/**
 * ==================================================
 * Load the functions associated with this plugin.
 * ==================================================
 */
require_once(IMM__PLUGIN_DIR . 'functions.php');

// Call an instance from the project class
require_once(IMM__PLUGIN_DIR . 'Project.class.php');
new Project();

// Call an instance from the subject class
require_once(IMM__PLUGIN_DIR . 'Subject.class.php');
new Subject();

// Call an instance from the supervisor class
require_once(IMM__PLUGIN_DIR . 'Supervisor.class.php');
new Supervisor();

// Call an instance from the impression class
require_once(IMM__PLUGIN_DIR . 'Impression.class.php');
new Impression();
