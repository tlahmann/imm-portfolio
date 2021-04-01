<?php
/**
 * Functions and definitions
 *
 * @package WordPress
 * @subpackage Imm
 * @since Imm 0.13
 */


/**
 * Grab all posts of the specified type
 *
 * @param   string  name of the post type
 *
 * @return  array   an array of \WP_Post objects
 */
function get_all_of_post_type($type_name = ''): array
{
    $items = array();
    if (!empty($type_name)) {
        $args = array(
        'post_type' => "{$type_name}",
        'posts_per_page' => -1,
        'order' => 'ASC',
        'orderby' => 'title'
    );
        $results = new \WP_Query($args);
        if ($results->have_posts()) {
            while ($results->have_posts()) {
                $items[] = $results->next_post();
            }
        }
    }
    return $items;
}

/**
 * Update the rewrite rules to find custom post type \Project
 * when wp is routing to these resources
 *
 * @param   array   all currently set rewrite rules
 *
 * @return  array   current rules with new rules added
 */
function imm_rewrite_rules($rules) {
    $newRules  = array();
    // If the URL is '/projects/some-project' show the results of 'index.php?project=some-project'
    $newRules['projects/(.+)/?$'] = 'index.php?project=$matches[1]';
    
    // Add to set of existing rules
    return array_merge($newRules, $rules);
}
add_filter('rewrite_rules_array', 'imm_rewrite_rules');

/**
 * Remove the default post from the wordpress admin panel
 *
 * @return  void
 */
function post_remove (): void
{ 
   remove_menu_page('edit.php');
}
//adding action for triggering function call
add_action('admin_menu', 'post_remove');   
