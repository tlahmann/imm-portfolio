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
