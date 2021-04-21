<?php
/**
 * Custom post type Project class
 * The class Project provides a new post type for the WP backbone. It stores
 * projects conducted within certain courses in each teaching period.
 *
 * @package WordPress
 * @subpackage imm
 * @since imm 0.0.14
 */
class Project
{
    private $_already_saved = false;  # Used to avoid saving twice

    /*
     * Constructor called when wp launches the plugin
     */
    public function __construct()
    {
        add_action('init', array( $this, 'register_projects_categories' ));
        add_action('init', array( $this, 'register_projects_post_type' ));
        add_action('init', array( $this, 'register_projects_sidebar' ));
        add_action('save_post', array( $this, 'save_meta_box_data' ), 10, 2);
        add_action('add_meta_boxes_project', array( $this, 'setup_project_boxes' ));
    }

    /**
     * Create and register a custom post type (CPT) with the wordpress backend
     *
     * @return void
     */
    public function register_projects_post_type(): void
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
            'menu_position'       => 8,
            // (bool) Makes this post type available via the admin bar. Default is value of $show_in_menu.
            'show_in_admin_bar'   => true,
            // (bool) Makes this post type available for selection in navigation menus. Default is value of $public.
            'show_in_nav_menus'   => false,
             
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
            'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),

            /* (bool|string) Whether there should be post type archives, or if a string, the archive slug to use.
            Will generate the proper rewrite rules if $rewrite is enabled. Default false. */
            'has_archive'         => true,
            
            // (bool) Whether to allow this post type to be exported. Default true.
            'can_export'          => true,

            // (bool) Whether to delete posts of this type when deleting a user.
            'delete_with_user'    => false,

            /* (string[]) An array of taxonomy identifiers that will be registered for the post type.
            Taxonomies can be registered later with register_taxonomy() or register_taxonomy_for_object_type(). */
            'taxonomies' => array('project_categories'),
             
            /* (bool|array) Triggers the handling of rewrites for this post type. To prevent rewrite, set to false. Defaults to
            true, using $post_type as slug. To specify rewrite rules, an array can be passed with any of these keys:
                'slug' (string) Customize the permastruct slug. Defaults to $post_type key.
                'with_front' (bool) Whether the permastruct should be prepended with WP_Rewrite::$front. Default true.
                'feeds' (bool) Whether the feed permastruct should be built for this post type. Default is value of $has_archive.
                'pages' (bool) Whether the permastruct should provide for pagination. Default true.
                'ep_mask' (int) Endpoint mask to assign. If not specified and permalink_epmask is set, inherits from $permalink_epmask.
                    If not specified and permalink_epmask is not set, defaults to EP_PERMALINK.
            */
            'rewrite'             => array('slug' => 'projects' ),
            
            // Icon shown in the wordpress admin menu
            // Find the appropriate dashicon here: https://developer.wordpress.org/resource/dashicons/
            'menu_icon' => 'dashicons-media-document',
        
            /* (string[]) An array of labels for this post type. If not set, post labels are inherited for non-hierarchical
            types and page labels for hierarchical ones. See get_post_type_labels() for a full list of supported labels. */
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
        register_post_type('project', $args);
    }

    /**
     * Enable categories for the \Project CPT
     *
     * @return void
     */
    public function register_projects_categories()
    {
        register_taxonomy(
            'project_categories',           // The name of the taxonomy. Name should be in slug form (no spaces and all lowercase. no caps).
            'projects',                     // post type name
            array(
                'hierarchical' => false,     // The keywords are hierarchical at the moment
                'label' => 'Tech. Facts',   // Label Displayed in the Admin when creating a new project
                'query_var' => true,
                'rewrite' => array(
                    'slug' => 'projects',   // This controls the base slug that will display before each term
                    'with_front' => false   // Don't display the category base before
                )
            )
        );
    }

    /**
     * Create projects sidebar
     *
     * @return void
     */
    public function register_projects_sidebar()
    {
        register_sidebar(
            array(
                'name' => __('Project Sidebar'),
                'id' => 'sidebar-project',
                'description' => 'An optional widget area for my sidebar',
                'before_widget' => '<div id="%1$s">',
                'after_widget' => "</div>"
            )
        );
    }

    /**
     * Set up all project related meta-boxes in the wordpress editor
     *
     * @param \WP_Post     $post       The current custom post type 'project' post
     *
     * @return void
     */
    public function setup_project_boxes(\WP_Post $post): void
    {
        add_meta_box(
            'imm_project_related_subject_box',
            'Projekt Metadaten',
            array( $this, 'draw_meta_boxes' ),
            $post->post_type,
            'normal',
            'default'
        );
        
        add_meta_box(
            'imm_project_related_attachment_box',
            'Projektmedien',
            array( $this, 'draw_attachments_boxes' ),
            $post->post_type,
            'normal',
            'default'
        );
    }

    public function draw_meta_boxes(\WP_Post $post)
    {
        echo '<div style="display: flex;gap: 5%;">';
        $this->draw_project_subjects_box($post);
        $this->draw_project_supervisor_box($post);
        $this->draw_project_term_box($post);
        $this->draw_project_highlight_box($post);
        echo '</div>';
    }

    /**
     * Echoes the subject select DOM element for wordpress to show in the meta box
     *
     * @param \WP_Post     $post       The current CPT \Project post
     *
     * @return void
     */
    private function draw_project_highlight_box(\WP_Post $post): void
    {
        // Build dropdown select box in the WP meta box
        $checked = get_post_meta($post->ID, '_highlight', true) == "is-highlight" ? ' checked="checked"' : '';
        $_hpt = has_post_thumbnail($post);
        $disabled = $_hpt ? '' : ' disabled="disabled"';
        $choice_block = <<<HTML
                        <label for="highlight">Projekt in den Highlights zeigen:</label><br>
                        <input type="checkbox" id="highlight" name="highlight" value="is-highlight" {$checked} {$disabled}>
                        HTML;
        if (!$_hpt) {
            $choice_block .= <<<HTML
                <small style="color:red;">Projekt kann nicht in den Highlights gezeigt werden, wenn kein Beitragsbild gesetzt ist.</small><br>
                HTML;
        }

        # Make sure the user intended to do this.
        wp_nonce_field(
            "updating_{$post->post_type}_meta_fields",
            $post->post_type . '_highlight_meta_nonce'
        );

        echo '<div style="display: flex;flex: 1 1 100%;flex-direction: column;">' . $choice_block . '</div>';
    }

    /**
     * Echoes the subject select DOM element for wordpress to show in the meta box
     *
     * @param \WP_Post     $post       The current CPT \Project post
     *
     * @return void
     */
    private function draw_project_subjects_box(\WP_Post $post): void
    {
        // All available posts of type 'subject' @see \Subject.class.php
        $all_subjects = get_all_of_post_type('subject');

        // Get the linked subject for this project post
        $linked_subject_id = $this->get_project_subject_id($post->ID);

        // Build dropdown select box in the WP meta box
        $checked = $linked_subject_id == "0" ? ' selected="selected"' : '';
        $choice_block = <<<HTML
                        <label for="subject_id">Zugeordnetes Fach:</label><br/>
                        <select name="subject_id" id="subject_id" style="width: 100%;">
                            <option value="null" {$checked}>Kein Fach</option>
                        HTML;
        if (0 == count($all_subjects)) {
            // noop
        } else {
            $choices = array();
            foreach ($all_subjects as $subject) {
                $checked = $subject->ID == $linked_subject_id ? ' selected="selected"' : '';

                // Add all available subjects to the select DOM element
                $display_name = esc_attr($subject->post_title);
                $choices[] = <<<HTML
                             <option value="{$subject->ID}" {$checked}>{$display_name}</option>
                             HTML;
            }
            $choice_block .= implode("\r\n", $choices);
        }

        // Close select section
        $choice_block .= <<<HTML
                         </select>
                         HTML;

        # Make sure the user intended to do this.
        wp_nonce_field(
            "updating_{$post->post_type}_meta_fields",
            $post->post_type . '_subject_meta_nonce'
        );

        echo '<div style="display: flex;flex: 1 1 100%;flex-direction: column;">' . $choice_block . '</div>';
    }

    /**
     * Echoes the subject select DOM element for wordpress to show in the meta box
     *
     * @param \WP_Post     $post       The current CPT \Project post
     *
     * @return void
     */
    private function draw_project_supervisor_box(\WP_Post $post): void
    {
        // All available posts of type 'supervisor' @see \supervisor.class.php
        $all_supervisors = get_all_of_post_type('supervisor');

        // Get the linked supervisor for this project post
        $linked_supervisor_id = $this->get_project_supervisor_id($post->ID);

        // Build dropdown select box in the WP meta box
        $checked = $linked_supervisor_id == "0" ? ' selected="selected"' : '';
        $choice_block = <<<HTML
                        <label for="supervisor_id">Betreuer:</label><br/>
                        <select name="supervisor_id" id="supervisor_id" style="width: 100%;">
                            <option value="null" {$checked}>Kein Betreuer</option>
                        HTML;
        if (0 == count($all_supervisors)) {
            // noop
        } else {
            $choices = array();
            foreach ($all_supervisors as $supervisor) {
                $checked = $supervisor->ID == $linked_supervisor_id ? ' selected="selected"' : '';

                // Add all available supervisors to the select DOM element
                $display_name = esc_attr($supervisor->post_title);
                $choices[] = <<<HTML
                             <option value="{$supervisor->ID}" {$checked}>{$display_name}</option>
                             HTML;
            }
            $choice_block .= implode("\r\n", $choices);
        }

        // Close select section
        $choice_block .= <<<HTML
                         </select>
                         HTML;

        # Make sure the user intended to do this.
        wp_nonce_field(
            "updating_{$post->post_type}_meta_fields",
            $post->post_type . '_supervisor_meta_nonce'
        );

        echo '<div style="display: flex;flex: 1 1 100%;flex-direction: column;">' . $choice_block . '</div>';
    }

    /**
     * Echoes the term select DOM element for wordpress to show in the meta box
     *
     * @param \WP_Post     $post       The current CPT \Project post
     *
     * @return void
     */
    private function draw_project_term_box(\WP_Post $post): void
    {
        // Get the linked subject for this project post
        $linked_term = $this->get_project_term($post->ID);
        $current_year = intval(date("Y"));

        // Build dropdown select box in the WP meta box
        $checked = $linked_term == "0" ? ' selected="selected"' : '';
        $choice_block = <<<HTML
                        <label for="term">Projektzeit:</label><br/>
                        <select name="term" id="term" style="width: 100%;">
                            <option value="null" {$checked}>Kein Semester</option>
                        HTML;

        $choices = array();
        for ($i = $current_year - 2; $i <= $current_year; $i++) {
            $first_checked = $linked_term == "$i-1" ? ' selected="selected"' : '';
            $second_checked = $linked_term == "$i-2" ? ' selected="selected"' : '';
            // Add all available subjects to the select DOM element
            $first_display_name = "Wintersemester " . ($i-1) . "/" . substr($i, -2);
            $second_display_name = "Sommersemester " . $i;
            $choices[] = <<<HTML
                            <option value="{$i}-1" {$first_checked}>{$first_display_name}</option>
                            <option value="{$i}-2" {$second_checked}>{$second_display_name}</option>
                            HTML;
        }
        $choice_block .= implode("\r\n", $choices);

        // Close select section
        $choice_block .= <<<HTML
                         </select>
                         HTML;

        # Make sure the user intended to do this.
        wp_nonce_field(
            "updating_{$post->post_type}_meta_fields",
            $post->post_type . '_period_meta_nonce'
        );

        echo '<div style="display: flex;flex: 1 1 100%;flex-direction: column;">' . $choice_block . '</div>';
    }

    public function draw_attachments_boxes(\WP_Post $post)
    {
        /**
         * The button that opens our media uploader
         * The `data-media-uploader-target` value should match the ID/unique selector of your field.
         * We'll use this value to dynamically inject the file URL of our uploaded media asset into your field once successful (in the imm-media.js file)
         */
        $media_set = get_post_meta($post->ID, '_media_ids', true);
        $media_set = $media_set !== "" ? explode(',', $media_set) : [];
        $media_string = '';
        if (0 == count($media_set)) {
            _e('Keine Projektmedien hinzugefügt');
        } else {
            $media_array = array();
            foreach ($media_set as $media) {
                $post_attachments = get_post($media);

                // Add all available medias to the select DOM element
                $image = wp_get_attachment_image($post_attachments->ID);
                $display_name = esc_attr($post_attachments->post_title);
                $media_array[] = <<<HTML
                                    <figure>
                                        {$image}
                                        <figcaption class="overlay">{$display_name}</figcaption>
                                    </figure>
                                 HTML;
            }
            $media_string .= implode("\r\n", $media_array);
        }

        $choice_block = <<<HTML
                        <fieldset>
                            <div id="imm_project_media_images">
                                {$media_string}
                            </div>
                            <input type="hidden" class="large-text" name="imm_project_media" id="imm_project_media" multiple><br>
                            <button type="button" class="button" id="events_video_upload_btn" data-media-uploader-target="#imm_project_media">Medien auswählen</button>
                        </fieldset>
                        HTML;
        echo $choice_block;
    }

    /**
     * Grab the subject ID associated with this project
     *
     * @param   number      $project_id     The current CPT \Project post id
     *
     * @return  string                      The assigned CPT \Subject post id
     */
    private function get_project_subject_id($project_id = 0): string
    {
        $ids = "0";
        if ($project_id > 0) {
            $matches = get_post_meta($project_id, '_subject_id', false);
            if (count($matches) > 0) {
                $ids = $matches[0];
            }
        }
        return $ids;
    }

    /**
     * Grab the supervisor ID associated with this project
     *
     * @param   number      $project_id     The current CPT \Project post id
     *
     * @return  string                      The assigned CPT \supervisor post id
     */
    private function get_project_supervisor_id($project_id = 0): string
    {
        $ids = "0";
        if ($project_id > 0) {
            $matches = get_post_meta($project_id, '_supervisor_id', false);
            if (count($matches) > 0) {
                $ids = $matches[0];
            }
        }
        return $ids;
    }

    /**
     * Grab the subject ID associated with this project
     *
     * @param   number      $project_id     The current CPT \Project post id
     *
     * @return  string                      The assigned CPT \Subject post id
     */
    private function get_project_term($project_id = 0): string
    {
        $ids = "0";
        if ($project_id > 0) {
            $matches = get_post_meta($project_id, '_term', false);
            if (count($matches) > 0) {
                $ids = $matches[0];
            }
        }
        return $ids;
    }

    /**
     * Handle metabox data save when the CPT is saved by wordpress
     *
     * @param   number      $project_id     The current CPT \Project post id
     * @param   \WP_Post    $post           The current \WP_Post post to be saved
     *
     * @return  void
     */
    public function save_meta_box_data($project_id = 0, \WP_Post $post = null): void
    {
        $do_save = true;

        # Do not save if we have already saved our updates
        if ($this->_already_saved) {
            $do_save = false;
        }

        # Do not save if there is no post id or post
        if (empty($project_id) || empty($post)) {
            $do_save = false;
        } elseif ($post->post_type !== 'project') {
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
        if (!array_key_exists('post_ID', $_POST) || $project_id != $_POST['post_ID']) {
            $do_save = false;
        }

        # Make sure we have the needed permissions to save [ assumes both types use edit_post ]
        if (! current_user_can('edit_post', $project_id)) {
            $do_save = false;
        }

        foreach (['_subject', '_period', '_highlight'] as $subtype) {
            # Make sure the nonce and referrer check out.
            $nonce_field_name = $post->post_type . $subtype . '_meta_nonce';
            if (! array_key_exists($nonce_field_name, $_POST)) {
                $do_save = false;
            } elseif (! wp_verify_nonce($_POST["{$nonce_field_name}"], "updating_{$post->post_type}_meta_fields")) {
                $do_save = false;
            } elseif (! check_admin_referer("updating_{$post->post_type}_meta_fields", $nonce_field_name)) {
                $do_save = false;
            }
        }

        // Only act if the post should be saved
        if ($do_save) {
            $this->handle_project_meta_changes($project_id, $_POST);

            # Note that we saved our data
            $this->_already_saved = true;
        }
        return;
    }

    /**
     * Add or remove the link between a \Project and a \Subject
     *
     * @param   number      $project_id     The current CPT \Project post id
     * @param   array       $post           The updated data
     *
     * @return  void
     */
    private function handle_project_meta_changes($project_id = 0, $data = array())
    {
        # Get the currently linked subjects for this project
        $linked_subject_id = $this->get_project_subject_id($project_id);
        $linked_term = $this->get_project_term($project_id);

        if (array_key_exists('highlight', $data)) {
            /* Get the meta value of the custom field key. */
            $meta_value = get_post_meta($project_id, '_highlight', true);
            /* Check if the post has a thumbnail and prevent or remove the highlight
            option if no image is present */
            $post = new WP_Query(array('p' => $project_id));
            $_hpt = has_post_thumbnail($post) ? $meta_value : null;
            $new_meta_value = $data['highlight'] ?? 0;
            
            if ($new_meta_value && '' == $meta_value) {
                /* We use add_post_meta with 4th parameter 'true' to have unique values. */
                add_post_meta($project_id, '_highlight', $new_meta_value, true);
            } elseif ($new_meta_value && $new_meta_value != $meta_value) {
                /* If the new meta value does not match the old value, update it. */
                update_post_meta($project_id, '_highlight', $new_meta_value);
            } elseif ('' == $new_meta_value && $meta_value) {
                /* If there is no new meta value but an old value exists, delete it. */
                delete_post_meta($project_id, '_highlight', $meta_value);
            }
        }

        # Get the list of subjects checked by the user
        if (array_key_exists('subject_id', $data) && $data['subject_id'] !== 0) {
            $subject_id = intval($data['subject_id']) ?? 0;
            
            if ($subject_id !== 0) {
                # We use add post meta with 4th parameter true to let us link
                # to one unique subject.
                update_post_meta($project_id, '_subject_id', $subject_id);
            } else {
                delete_post_meta($project_id, '_subject_id');
            }
        }

        # Get the list of supervisors checked by the user
        if (array_key_exists('supervisor_id', $data) && $data['supervisor_id'] !== 0) {
            $supervisor_id = intval($data['supervisor_id']) ?? 0;
            
            if ($supervisor_id !== 0) {
                # We use add post meta with 4th parameter true to let us link
                # to one unique supervisor.
                update_post_meta($project_id, '_supervisor_id', $supervisor_id);
            } else {
                delete_post_meta($project_id, '_supervisor_id');
            }
        }

        if (array_key_exists('term', $data)) {
            $term = $data['term'];
            
            if (intval($term) !== 0) {
                # We use add post meta with 4th parameter true to let us link
                # to one unique subject.
                update_post_meta($project_id, '_term', $term);
            } else {
                delete_post_meta($project_id, '_term');
            }
        }

        if (array_key_exists('imm_project_media', $data) && $data['imm_project_media'] !== '') {
            $media = $data['imm_project_media'];

            update_post_meta($project_id, '_media_ids', $media);
        }
    }
}
