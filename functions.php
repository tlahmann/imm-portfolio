<?php
/**
 * Functions and definitions
 *
 * @package WordPress
 * @subpackage Imm
 * @since Imm 1.0
 */

// Many to Many Linker

function get_meta_boxes( $screen = null, $context = 'advanced' ) {
    global $wp_meta_boxes;

    if ( empty( $screen ) )
        $screen = get_current_screen();
    elseif ( is_string( $screen ) )
        $screen = convert_to_screen( $screen );

    $page = $screen->id;

    return $wp_meta_boxes[$page][$context];          
}

function get_projects_for_subject_id( $subject_id = 0 ) {
    $found = array();

    if ( 0 < $subject_id ) {
        $args = array(
            'post_type' => 'imm_project',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_subject_id',
                    'value' => $subject_id,
                    'type' => 'NUMERIC',
                    'compare' => '='
                )
            )
        );
        $projects = new \WP_Query( $args );
        if ( $projects->have_posts() ) {
            while ( $projects->have_posts() ) {
                $project = $projects->next_post();
                $found["{$project->ID}"] = $project;
            }
        }
    }

    return $found;
}

function get_subject_extra_data_for_subject_id( $subject_id = 0 ) {
    $data = array();
    if ( 0 < $subject_id ) {
        $current = get_post_meta( $subject_id, '_subject_info', true );
        if ( is_array($current) ) {
            $data = $current;
        }
    }
    return $data;
}

function imm_add_custom_box() {
    $screens = [ 'imm_subject', 'imm_project' ];
    foreach ( $screens as $screen ) {
        add_meta_box(
            'imm_subject_box_id',                 // Unique ID
            'Custom Meta Box Title',      // Box title
            'imm_subject_custom_box_html',  // Content callback, must be of type callable
            $screen                            // Post type
        );
    }
}
add_action( 'add_meta_boxes', 'imm_add_custom_box' );

function imm_subject_custom_box_html( $post ) {
    ?>
    <label for="wporg_field">Description for this field</label>
    <select name="wporg_field" id="wporg_field" class="postbox">
        <option value="">Select something...</option>
        <option value="something">Something</option>
        <option value="else">Else</option>
    </select>
    <?php
}
