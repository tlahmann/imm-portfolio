<?php
/**
 * ManyToMany linker class
 *
 * @package WordPress
 * @subpackage imm
 * @since imm 0.0.14
 */

 /**
 * The class imm_portfolio provides two new post types for the WP backbone. These 
 * store subjects taught at ulm university and projects conducted within these 
 * teaching periods. Each post type has a taxonomy associated to define further
 * categories and keywords
 */
class Subject
{

    /*
     * Constructor called when wp launches the plugin
     */
    public function __construct()
    {
        add_action('init', array( $this, 'register_subjects_post_type' ));
    }

    /**
     * Create and register a custom post type (CPT) with the wordpress backend
     *
     * @return void
     */
    public function register_subjects_post_type(): void
    {
        // args for the new post_type
        $args = array(
            /* (bool) Whether a post type is intended for use publicly either via the admin interface or by front-end 
            users. While the default settings of $exclude_from_search, $publicly_queryable, $show_ui, and 
            $show_in_nav_menus are inherited from public, each does not rely on this relationship and controls a very 
            specific intention. Default false. */
            'public'              => true,
            // bool) Whether to generate and allow a UI for managing this post type in the admin. Default is value of $public.
            'show_ui'             => true,
            /* (bool|string) Where to show the post type in the admin menu. To work, $show_ui must be true. If true, the post type 
            is shown in its own top level menu. If false, no menu is shown. If a string of an existing top level menu (eg. 
            'tools.php' or 'edit.php?post_type=page'), the post type will be placed as a sub-menu of that. Default is value of 
            $show_ui. */
            'show_in_menu'        => true,
            /* (int) The position in the menu order the post type should appear. To work, $show_in_menu must be true. 
            Default null (at the bottom). */
            'menu_position'       => 21,
            // (bool) Makes this post type available via the admin bar. Default is value of $show_in_menu.
            'show_in_admin_bar'   => true,
            // (bool) Makes this post type available for selection in navigation menus. Default is value of $public.
            'show_in_nav_menus'   => true,
             
            /* (string) The string to use to build the read, edit, and delete capabilities. May be passed as an array to allow for 
            alternative plurals when using this argument as a base to construct the capabilities, e.g. array('story', 'stories'). 
            Default 'post'. */
            'capability_type'     => 'post',
 
            /* (bool) Whether queries can be performed on the front end for the post type as part of parse_request(). 
            Endpoints would include:
                ?post_type={post_type_key}
                ?{post_type_key}={single_post_slug}
                ?{post_type_query_var}={single_post_slug} If not set, the default is inherited from $public. */
            'publicly_queryable'  => true,
 
            /* (bool) Whether to exclude posts with this post type from front end search results. Default is the opposite 
            value of $public. */
            'exclude_from_search' => false,
 
            /* (array) Core feature(s) the post type supports. Serves as an alias for calling add_post_type_support()
            directly. Core features include 'title', 'editor', 'comments', 'revisions', 'trackbacks', 'author', 'excerpt',
            'page-attributes', 'thumbnail', 'custom-fields', and 'post-formats'. Additionally, the 'revisions' feature
            dictates whether the post type will store revisions, and the 'comments' feature dictates whether the comments
            count will show on the edit screen. A feature can also be specified as an array of arguments to provide
            additional information about supporting that feature. Example: array( 'my_feature', array( 'field' => 'value' ) ).
            Default is an array containing 'title' and 'editor'. */
            'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
 
            /* (bool|string) Whether there should be post type archives, or if a string, the archive slug to use.
            Will generate the proper rewrite rules if $rewrite is enabled. Default false. */
            'has_archive'         => false,
            
            // (bool) Whether to allow this post type to be exported. Default true.
            'can_export'          => true,

            // (bool) Whether to delete posts of this type when deleting a user.
            'delete_with_user'    => false,

            /* (string[]) An array of taxonomy identifiers that will be registered for the post type.
            Taxonomies can be registered later with register_taxonomy() or register_taxonomy_for_object_type(). */
            'taxonomies' => array('subject_categories'),
             
            /* (bool|array) Triggers the handling of rewrites for this post type. To prevent rewrite, set to false. Defaults to 
            true, using $post_type as slug. To specify rewrite rules, an array can be passed with any of these keys: 
                'slug' (string) Customize the permastruct slug. Defaults to $post_type key.
                'with_front' (bool) Whether the permastruct should be prepended with WP_Rewrite::$front. Default true.
                'feeds' (bool) Whether the feed permastruct should be built for this post type. Default is value of $has_archive.
                'pages' (bool) Whether the permastruct should provide for pagination. Default true.
                'ep_mask' (int) Endpoint mask to assign. If not specified and permalink_epmask is set, inherits from $permalink_epmask. 
                    If not specified and permalink_epmask is not set, defaults to EP_PERMALINK.
            */
            'rewrite'             => array('slug' => 'subjects' ),
            
            // Icon shown in the wordpress admin menu
            // Find the appropriate dashicon here: https://developer.wordpress.org/resource/dashicons/
            'menu_icon' => 'dashicons-admin-multisite', 
        
            /* (string[]) An array of labels for this post type. If not set, post labels are inherited for non-hierarchical 
            types and page labels for hierarchical ones. See get_post_type_labels() for a full list of supported labels. */
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
        register_post_type('subject', $args);
    }
}
