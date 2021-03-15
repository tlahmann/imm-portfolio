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

class Many_To_Many_Linker
{
    protected $_already_saved = false;  # Used to avoid saving twice

    public function __construct()
    {
        $this->do_initialize();
    }

    protected function do_initialize()
    {
        add_action(
            'save_post',
            array( $this, 'save_meta_box_data' ),
            10,
            2
        );

        add_action(
            "add_meta_boxes_imm_subject",
            array( $this, 'setup_subject_boxes' )
        );

        add_action(
            "add_meta_boxes_imm_project",
            array( $this, 'setup_project_boxes' )
        );
    }

    public function setup_subject_boxes(\WP_Post $post)
    {
        add_meta_box(
            'imm_subjects_related_projects_box',            // Unique ID
            __('Related Projects', 'language'),             // Box title
            array( $this, 'draw_subject_projects_box' ),    // Content callback, must be of type callable
            $post->post_type,                               // Post type
            'side',
            'default'
        );

        add_meta_box(
            'imm_subjects_extra_data',
            __('Subject Details', 'language'),
            array( $this, 'draw_subject_details_box' ),
            $post->post_type,
            'side',
            'default'
        );
    }

    protected function get_subject_details_meta($post_id = 0)
    {
        $default = $this->get_default_subject_details_meta();
        $current = get_post_meta($post_id, '_subject_info', true);
        if (!is_array($current)) {
            $current = $default;
        } else {
            foreach ($default as $k => $v) {
                if (!array_key_exists("{$k}", $current)) {
                    $current["{$k}"] = $v;
                }
            }
        }
        return $current;
    }

    protected function get_default_subject_details_meta()
    {
        return array(
            'favorite_color' => '',
            'height' => '',
            'eye_color' => ''
        );
    }

    public function draw_subject_details_box(\WP_Post $post)
    {
        $current_meta = $this->get_subject_details_meta($post->ID);

        echo <<<HTML
<p>
    <label for="subject_favorite_color">Favorite Color:</label>
    <input type="text" name="subject_favorite_color" value="{$current_meta['favorite_color']}" id="subject_favorite_color" />
</p>
<p>
    <label for="subject_height">Height:</label>
    <input type="text" name="subject_height" value="{$current_meta['height']}" id="subject_height" />
</p>
<p>
    <label for="subject_eye_color">Eye Color:</label>
    <input type="text" name="subject_eye_color" value="{$current_meta['eye_color']}" id="subject_eye_color" />
</p>
HTML;

        # No need for nonce - already added in related projects
    }

    public function draw_subject_projects_box(\WP_Post $post)
    {
        $all_projects = $this->get_all_of_post_type('imm_project');

        $linked_project_ids = $this->get_subject_project_ids($post->ID);

        if (0 == count($all_projects)) {
            $choice_block = '<p>No projects found in the system.</p>';
        } else {
            $choices = array();
            foreach ($all_projects as $project) {
                $checked = (in_array($project->ID, $linked_project_ids)) ? ' checked="checked"' : '';

                $display_name = esc_attr($project->post_title);
                $choices[] = <<<HTML
<label><input type="checkbox" name="project_ids[]" value="{$project->ID}" {$checked}/> {$display_name}</label>
HTML;
            }
            $choice_block = implode("\r\n", $choices);
        }

        # Make sure the user intended to do this.
        wp_nonce_field(
            "updating_{$post->post_type}_meta_fields",
            $post->post_type . '_meta_nonce'
        );

        echo $choice_block;
    }

    # Grab all posts of the specified type
    # Returns an array of post objects
    protected function get_all_of_post_type($type_name = '')
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

    # Get array of project ids for a particular subject id
    protected function get_subject_project_ids($subject_id = 0)
    {
        $ids = array();
        if (0 < $subject_id) {
            $args = array(
                'post_type' => 'imm_project',
                'posts_per_page' => -1,
                'order' => 'ASC',
                'orderby' => 'title',
                'meta_query' => array(
                    array(
                        'key' => '_subject_id',
                        'value' => (int)$subject_id,
                        'type' => 'NUMERIC',
                        'compare' => '='
                    )
                )
            );
            $results = new \WP_Query($args);
            if ($results->have_posts()) {
                while ($results->have_posts()) {
                    $item = $results->next_post();
                    if (!in_array($item->ID, $ids)) {
                        $ids[] = $item->ID;
                    }
                }
            }
        }
        return $ids;
    }

    public function setup_project_boxes(\WP_Post $post)
    {
        add_meta_box(
            'imm_project_related_subjects_box',
            __('Related Subjects', 'language'),
            array( $this, 'draw_project_subjects_box' ),
            $post->post_type,
            'side',
            'default'
        );
    }

    public function draw_project_subjects_box(\WP_Post $post)
    {
        $all_subjects = $this->get_all_of_post_type('imm_subject');

        $linked_subject_ids = $this->get_project_subject_ids($post->ID);

        if (0 == count($all_subjects)) {
            $choice_block = '<p>No subjects found in the system.</p>';
        } else {
            $choices = array();
            foreach ($all_subjects as $subject) {
                $checked = (in_array($subject->ID, $linked_subject_ids)) ? ' checked="checked"' : '';

                $display_name = esc_attr($subject->post_title);
                $choices[] = <<<HTML
<label><input type="checkbox" name="subject_ids[]" value="{$subject->ID}" {$checked}/> {$display_name}</label>
HTML;
            }
            $choice_block = implode("\r\n", $choices);
        }

        # Make sure the user intended to do this.
        wp_nonce_field(
            "updating_{$post->post_type}_meta_fields",
            $post->post_type . '_meta_nonce'
        );

        echo $choice_block;
    }

    # Grab all properties related to a specific development area
    # Returns an array of property post ids
    protected function get_project_subject_ids($project_id = 0)
    {
        $ids = array();
        if (0 < $project_id) {
            $matches = get_post_meta($project_id, '_subject_id', false);
            if (0 < count($matches)) {
                $ids = $matches;
            }
        }
        return $ids;
    }

    public function save_meta_box_data($post_id = 0, \WP_Post $post = null)
    {
        $do_save = true;

        $allowed_post_types = array(
            'imm_project',
            'imm_subject'
        );

        # Do not save if we have already saved our updates
        if ($this->_already_saved) {
            $do_save = false;
        }

        # Do not save if there is no post id or post
        if (empty($post_id) || empty($post)) {
            $do_save = false;
        } elseif (! in_array($post->post_type, $allowed_post_types)) {
            $do_save = false;
        }

        # Do not save for revisions or autosaves
        if (
            defined('DOING_AUTOSAVE')
            && (
                is_int(wp_is_post_revision($post))
                || is_int(wp_is_post_autosave($post))
            )
        ) {
            $do_save = false;
        }

        # Make sure proper post is being worked on
        if (!array_key_exists('post_ID', $_POST) || $post_id != $_POST['post_ID']) {
            $do_save = false;
        }

        # Make sure we have the needed permissions to save [ assumes both types use edit_post ]
        if (! current_user_can('edit_post', $post_id)) {
            $do_save = false;
        }

        # Make sure the nonce and referrer check out.
        $nonce_field_name = $post->post_type . '_meta_nonce';
        if (! array_key_exists($nonce_field_name, $_POST)) {
            $do_save = false;
        } elseif (! wp_verify_nonce($_POST["{$nonce_field_name}"], "updating_{$post->post_type}_meta_fields")) {
            $do_save = false;
        } elseif (! check_admin_referer("updating_{$post->post_type}_meta_fields", $nonce_field_name)) {
            $do_save = false;
        }

        if ($do_save) {
            switch ($post->post_type) {
                case "imm_project":
                    $this->handle_project_meta_changes($post_id, $_POST);
                    break;
                case "imm_subject":
                    $this->handle_subject_meta_changes($post_id, $_POST);
                    break;
                default:
                    # We do nothing about other post types
                    break;
            }

            # Note that we saved our data
            $this->_already_saved = true;
        }
        return;
    }

    # Subjects can be linked to multiple projects
    # Notice that we are editing project meta data here rather than subject meta data
    protected function handle_subject_meta_changes($post_id = 0, $data = array())
    {

        # META BOX - Details
        $current_details = $this->get_subject_details_meta($post_id);

        if (array_key_exists('favorite_color', $data) && !empty($data['favorite_color'])) {
            $favorite_color = sanitize_text_field($data['favorite_color']);
        } else {
            $favorite_color = '';
        }
        if (array_key_exists('height', $data) && !empty($data['height'])) {
            $height = sanitize_text_field($data['height']);
        } else {
            $height = '';
        }
        if (array_key_exists('eye_color', $data) && !empty($data['eye_color'])) {
            $eye_color = sanitize_text_field($data['eye_color']);
        } else {
            $eye_color = '';
        }

        $changed = false;

        if ($favorite_color != "{$current_details['favorite_color']}") {
            $current_details['favorite_color'] = $favorite_color;
            $changed = true;
        }

        if ($height != "{$current_details['height']}") {
            $current_details['height'] = $height;
            $changed = true;
        }

        if ($eye_color != "{$current_details['eye_color']}") {
            $current_details['eye_color'] = $eye_color;
            $changed = true;
        }

        if ($changed) {
            update_post_meta($post_id, '_subject_info', $current_details);
        }

        # META BOX - Related Projects

        # Get the currently linked projects for this subject
        $linked_project_ids = $this->get_subject_project_ids($post_id);

        # Get the list of projects checked by the user
        if (array_key_exists('project_ids', $data) && is_array($data['project_ids'])) {
            $chosen_project_ids = $data['project_ids'];
        } else {
            $chosen_project_ids = array();
        }

        # Build a list of projects to be linked or unlinked from this subject
        $to_remove = array();
        $to_add = array();

        if (0 < count($chosen_project_ids)) {
            # The user chose at least one project to link to
            if (0 < count($linked_project_ids)) {
                # We already had at least one project linked

                # Cycle through existing and note any that the user did not have checked
                foreach ($linked_project_ids as $project_id) {
                    if (! in_array($project_id, $chosen_project_ids)) {
                        # Currently linked, but not chosen. Remove it.
                        $to_remove[] = $project_id;
                    }
                }

                # Cycle through checked and note any that are not currently linked
                foreach ($chosen_project_ids as $project_id) {
                    if (! in_array($project_id, $linked_project_ids)) {
                        # Chosen but not in currently linked. Add it.
                        $to_add[] = $project_id;
                    }
                }
            } else {
                # No previously chosen ids, simply add them all
                $to_add = $chosen_project_ids;
            }
        } elseif (0 < count($linked_project_ids)) {
            # No properties chosen to be linked. Remove all currently linked.
            $to_remove = $linked_project_ids;
        }

        if (0 < count($to_add)) {
            foreach ($to_add as $project_id) {
                # We use add post meta with 4th parameter false to let us link
                # projects to as many subjects as we want.
                add_post_meta($project_id, '_subject_id', $post_id, false);
            }
        }

        if (0 < count($to_remove)) {
            foreach ($to_remove as $project_id) {
                # We specify parameter 3 as we only want to delete the link
                # to this subject
                delete_post_meta($project_id, '_subject_id', $post_id);
            }
        }
    }
    # Projects can be linked with multiple subjects
    protected function handle_project_meta_changes($post_id = 0, $data = array())
    {

    # Get the currently linked subjects for this project
        $linked_subject_ids = $this->get_project_subject_ids($post_id);

        # Get the list of subjects checked by the user
        if (array_key_exists('subject_ids', $data) && is_array($data['subject_ids'])) {
            $chosen_subject_ids = $data['subject_ids'];
        } else {
            $chosen_subject_ids = array();
        }

        # Build a list of subjects to be linked or unlinked with this project
        $to_remove = array();
        $to_add = array();

        if (0 < count($chosen_subject_ids)) {
            # The user chose at least one subject to link to
            if (0 < count($linked_subject_ids)) {
                # We already had at least one subject already linked

                # Cycle through existing and note any that the user did not have checked
                foreach ($linked_subject_ids as $subject_id) {
                    if (! in_array($subject_id, $chosen_subject_ids)) {
                        # Currently linked, but not chosen. Remove it.
                        $to_remove[] = $subject_id;
                    }
                }

                # Cycle through checked and note any that are not currently linked
                foreach ($chosen_subject_ids as $subject_id) {
                    if (! in_array($subject_id, $linked_subject_ids)) {
                        # Chosen but not in currently linked. Add it.
                        $to_add[] = $subject_id;
                    }
                }
            } else {
                # No previously chosen ids, simply add them all
                $to_add = $chosen_subject_ids;
            }
        } elseif (0 < count($linked_subject_ids)) {
            # No properties chosen to be linked. Remove all currently linked.
            $to_remove = $linked_subject_ids;
        }

        if (0 < count($to_add)) {
            foreach ($to_add as $subject_id) {
                # We use add post meta with 4th parameter false to let us link
                # to as many subjects as we want.
                add_post_meta($post_id, '_subject_id', $subject_id, false);
            }
        }

        if (0 < count($to_remove)) {
            foreach ($to_remove as $subject_id) {
                # We specify parameter 3 as we only want to delete the link
                # to this subject
                delete_post_meta($post_id, '_subject_id', $subject_id);
            }
        }
    }
} # end of the class declaration


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
