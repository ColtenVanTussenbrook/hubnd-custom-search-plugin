<?php 
    function search_urls_post_type() {
        register_post_type('search_urls',
            array(
                'label'               => 'Custom Search URLs',
                'description'         => 'List of URLs to output with user-inputted search term on Research Topics page',
                'public'              => true,
                'exclude_from_search' => true,
                'show_in_menu'        => true,
                'taxonomies'          => array( 'search_topic_category')
            )
        );
    }

    function register_search_category_taxonomy() {
        $labels = array(
            'name'                       => 'Search Topic Categories',
            'singular_name'              => 'Search Topic Category',
        );
        $args   = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_admin_column' => false,
        );
        register_taxonomy( 'search_topic_category', [ 'search_urls' ], $args );
   }

    function space_format_meta() {
        add_meta_box(
            'space_format',
            'Space Format',
            'space_format_callback',
            'search_urls',
            'advanced',
            'high'
        );
    }

    function space_format_callback($post) {
        $values = get_post_custom( $post->ID );
        $selected = isset( $values['space_format_dropdown'] ) ? esc_attr( $values['space_format_dropdown'][0] ) : '';
        wp_nonce_field('space_format_nonce', 'meta_box_nonce');
        ?>
        <label for="space_format_dropdown">What does the site use for spaces in a search term?</label>
        <br>
        <select name="space_format_dropdown" id="space_format_dropdown">
            <option value="percent-sign" <?php selected($selected, 'percent-sign'); ?>>Percent Sign(%)</option>
            <option value="plus-sign" <?php selected($selected, 'plus-sign'); ?>>Plus Sign(+)</option>
        </select>
        <?php
    }

    function space_format_meta_save($post_id) {
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if( !isset($_POST['meta_box_nonce'] ) || !wp_verify_nonce($_POST['meta_box_nonce'], 'space_format_nonce' ) ) return;
        if( !current_user_can( 'edit_post' ) ) return;

        if (isset($_POST['space_format_dropdown'])) {
            update_post_meta($post_id, 'space_format_dropdown', esc_attr($_POST['space_format_dropdown']));
        }
    }

    function check_search_term_param($post_id) {
        global $post; 
        $error = false;
        $status = get_post_status($post_id);
        $content = $_POST['post_content'];
        
        if (strpos($content, '%%search-term%%') != true) {
            $error = new WP_Error('no_search_term', 'No %%search-term%% found in the url. Make sure you replace the search term with %%search-term%% in the URL');
        }

        if (!$error) {
            if ($status !== 'publish') {
                $post_data = array(
                    'ID' => $post_id,
                    'post_status' => 'publish',
                );
                wp_update_post($post_data);
            }
        } else {
            if ($status === 'publish') {
                $post_data = array(
                    'ID' => $post_id,
                    'post_status' => 'draft',
                );
                wp_update_post($post_data);
            }

            add_filter('redirect_post_location', function( $location ) use ( $error ) {
                return add_query_arg( 'no_search_term_error', $error->get_error_code(), $location );
            });
        }

    }

    function no_search_term_error() {
        if ( array_key_exists( 'no_search_term_error', $_GET) ) { ?>
            <div class="error">
                <p>
                    <?php
                        switch($_GET['no_search_term_error']) {
                            case 'no_search_term':
                                echo 'No %%search-term%% found in the url. Make sure you replace the search term with %%search-term%% in the URL';
                                break;
                            default:
                                echo 'An error ocurred when saving the post. Check the %%search-term%%. Contact developer if issue continues';
                                break;
                        }
                    ?>
                </p>
            </div><?php
        }
    }
    
    add_action('init', 'search_urls_post_type');
    add_action('init', 'register_search_category_taxonomy');
    add_action('add_meta_boxes', 'space_format_meta');
    add_action('save_post', 'space_format_meta_save');
    add_action('save_post_search_urls', 'check_search_term_param');
    add_action('admin_notices', 'no_search_term_error');

?>
