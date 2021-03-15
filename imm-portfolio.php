<?php
/**
 * @package imm
 */
/*
Plugin Name: imm Portfolio
Description: Portfolio-Plugin für die imm Website.
Version: 0.0.12
Author: Tobias Lahmann
Author URI: https://github.com/tlahmann
License: GPLv2 or later
*/

define( 'IMM__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Call an instance from our class
$plugin_start = new imm_portfolio();

class imm_portfolio
{

    /*
     * Constructor - the brain of our class
     */
    public function __construct()
    {
        add_action('init', array( $this, 'register_subjects_taxonomy' ));
        add_action('init', array( $this, 'register_subjects_post_type' ));
        add_action('init', array( $this, 'register_projects_taxonomy' ));
        add_action('init', array( $this, 'register_projects_post_type' ));
    }

    public function register_subjects_post_type()
    {
        // args for the new post_type
        $args = array(
            // Sichtbarkeit des Post Types
            'public'              => true,
            // Standart Ansicht im Backend aktivieren (Wie Artikel / Seiten)
            'show_ui'             => true,
            // Soll es im Backend Menu sichtbar sein?
            'show_in_menu'        => true,
            // Position im Menu
            'menu_position'       => 20,
            // Post Type in der oberen Admin-Bar anzeigen?
            'show_in_admin_bar'   => true,
            // in den Navigations Menüs sichtbar machen?
            'show_in_nav_menus'   => true,
             
            // Hier können Berechtigungen in einem Array gesetzt werden
            // oder die standart Werte post und page in form eines Strings gesetzt werden
            'capability_type'     => 'post',
 
            // Soll es im Frontend abrufbar sein?
            'publicly_queryable'  => true,
 
            // Soll der Post Type aus der Suchfunktion ausgeschlossen werden?
            'exclude_from_search' => false,
 
            // Welche Elemente sollen in der Backend-Detailansicht vorhanden sein?
            'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
 
            // Soll der Post Type Archiv-Seiten haben?
            'has_archive'         => false,
            
            // Soll man den Post Type exportieren können?
            'can_export'          => true,

            // What given taxonomies (categories/labels) are given for an empty post
            'taxonomies' => array('subject_categories'),
             
            // Slug unseres Post Types für die redirects
            // dieser Wert wird später in der URL stehen
            'rewrite'             => array('slug' => 'subjects' ),
            
            // Icon shown in the wordpress admin menu
            'menu_icon' => 'dashicons-admin-multisite', //Find the appropriate dashicon here: https://developer.wordpress.org/resource/dashicons/
        
            // Backend string values
            'labels'              => array(
                'name'               => _x('Fach', 'post type general name'),
                'singular_name'      => _x('Fach', 'post type singular name'),
                'add_new'            => __('Neues Fach anlegen'),
                'add_new_item'       => __('Neues Fach anlegen'),
                'edit_item'          => __('Fach-Daten bearbeiten'),
                'new_item'           => __('Neues Fach'),
                'all_items'          => __('Alle Fächer'),
                'view_item'          => __('Fächer ansehen'),
                'search_items'       => __('Fächer durchsuchen'),
                'not_found'          => __('Keine Fächer gefunden'),
                'not_found_in_trash' => __('Keine Fächer im Papierkorb gefunden'),
                'parent_item_colon'  => '',
                'menu_name'          => 'Fach'
            ),
        );

        // Custom Post Type registrieren
        register_post_type('imm_subject', $args);
    }

    //The following snippet is used to enable categories for the subjects CPT.
    public function register_subjects_taxonomy()
    {
        register_taxonomy(
            'subject_categories',  // The name of the taxonomy. Name should be in slug form (no spaces and all lowercase. no caps).
            'subjects',            // This taxonomy can be applied to the custom post type 'subjects' 
            array(
                'hierarchical' => false,
                'label' => 'Keywords',  //Label Displayed in the Admin when creating a new subject
                'query_var' => true,
                'rewrite' => array(
                    'slug' => 'subjects', // This controls the base slug that will display before each term
                    'with_front' => false // Don't display the category base before
                ),
            )
        );
    }

    public function register_projects_post_type()
    {
        // args for the new post_type
        $args = array(
            // Sichtbarkeit des Post Types
            'public'              => true,
            // Standart Ansicht im Backend aktivieren (Wie Artikel / Seiten)
            'show_ui'             => true,
            // Soll es im Backend Menu sichtbar sein?
            'show_in_menu'        => true,
            // Position im Menu
            'menu_position'       => 21,
            // Post Type in der oberen Admin-Bar anzeigen?
            'show_in_admin_bar'   => true,
            // in den Navigations Menüs sichtbar machen?
            'show_in_nav_menus'   => false,
             
            // Hier können Berechtigungen in einem Array gesetzt werden
            // oder die standart Werte post und page in form eines Strings gesetzt werden
            'capability_type'     => 'post',
 
            // Soll es im Frontend abrufbar sein?
            'publicly_queryable'  => true,
 
            // Soll der Post Type aus der Suchfunktion ausgeschlossen werden?
            'exclude_from_search' => false,
 
            // Welche Elemente sollen in der Backend-Detailansicht vorhanden sein?
            'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ),

            // Soll der Post Type Archiv-Seiten haben?
            'has_archive'         => false,
            
            // Soll man den Post Type exportieren können?
            'can_export'          => true,

            // What given taxonomies (categories/labels) are given for an empty post
            'taxonomies' => array('project_categories'),
             
            // Slug unseres Post Types für die redirects
            // dieser Wert wird später in der URL stehen
            'rewrite'             => array('slug' => 'projects' ),
            
            // Icon shown in the wordpress admin menu
            'menu_icon' => 'dashicons-media-document', //Find the appropriate dashicon here: https://developer.wordpress.org/resource/dashicons/
        
            // Backend string values
            'labels'              => array(
                'name'               => _x('Projekt', 'post type general name'),
                'singular_name'      => _x('Projekt', 'post type singular name'),
                'add_new'            => __('Neues Projekt anlegen'),
                'add_new_item'       => __('Neues Projekt anlegen'),
                'edit_item'          => __('Projekt-Daten bearbeiten'),
                'new_item'           => __('Neues Projekt'),
                'all_items'          => __('Alle Projekte'),
                'view_item'          => __('Projekte ansehen'),
                'search_items'       => __('Projekte durchsuchen'),
                'not_found'          => __('Keine Projekte gefunden'),
                'not_found_in_trash' => __('Keine Projekte im Papierkorb gefunden'),
                'parent_item_colon'  => '',
                'menu_name'          => 'Projekt'
            ),
        );

        // Custom Post Type registrieren
        register_post_type('imm_project', $args);
    }

    //The following snippet is used to enable categories for the projects CPT.
    public function register_projects_taxonomy()
    {
        register_taxonomy(
            'project_categories',  //The name of the taxonomy. Name should be in slug form (no spaces and all lowercase. no caps).
            'projects',        //post type name
            array(
                'hierarchical' => true,
                'label' => 'Keywords',  //Label Displayed in the Admin when creating a new project
                'query_var' => true,
                'rewrite' => array(
                    'slug' => 'projects', // This controls the base slug that will display before each term
                    'with_front' => false // Don't display the category base before
                )
            )
        );
    }
}

require_once( IMM__PLUGIN_DIR . 'functions.php' );
if ( is_admin() ) {
    new Many_To_Many_Linker();
}
