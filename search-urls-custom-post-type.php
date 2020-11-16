<?php 
    function search_urls_post_type() {
        register_post_type('search_urls',
            array(
                'label'      => 'Custom Search URLs',
                'description' => 'List of URLs to output with user-inputted search term on Research Topics page',
                'public'      => true,
                'exclude_from_search' => true,
                'show_in_menu' => true,
            )
        );
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

    function cpt_submenu() {
        add_submenu_page(
            'edit.php?post_type=search_urls', 
            'Documentation', 
            'Documentation', 
            'manage_options', 
            'search_urls_documentation', 
            'search_urls_documentation_callback'
        );
    }

    function search_urls_documentation_callback() {
        ?>
        <div class="custom-search-url-documentation">
            <h1>Custom Search URL Documentation</h1>
            <h2>Adding New URLS</h2>
                <div>
                <h4>If you want to add/edit a URL to show up in the custom search</h4>
                    <ol>
                        <li>Click "Custom Search URLs in WordPress Admin menu in left of screen</li>
                        <li>Click Add New</li>
                        <li>In the 'Add Title' field, type in what the link will display as in the search result, i.e. 'Dr. Michael Gregor Videos'</li>
                        <li>In the large content field, type in the actual URL and replace what the search term would be with %%search-term%%, i.e. https://www.youtube.com/c/NutritionfactsOrgMD/search?query=%%search-term%%</li>  
                        <li>Double check the link has %%search-term%% in it, exactly like that, as that's what will be replaced by the user's actual term.</li>
                        <li>In the Space Format dropdown, select which format of spacing the URL uses. Spaces will either be a plus sign or a percent sign.</li>
                        <li>Click "Publish" or "Update", depending on if it's a new entry or editing an existing one</li>
                        <li>Test out that your new URL is showing up in the search results</li>
                    </ol>
                <br>
                <h4>To delete a URL</h4>
                <ol>
                    <li>Hover over the entry under Custom Search URLS and click trash</li>
                </ol>
                <br>
                <h4>To place search box on a page</h4>
                <ol>
                    <li>Go to specific page or post and use the Divi Builder</li>
                    <li>Add a new module, and use the Code module</li>
                    <li>Type in ['custom-search-field'] in the Code module text field</li>
                    <li>Save Page/Post</li>
                </ol>
                <p>You can move the search box around just as you would any other module within Divi</p>
                <br>

                <h4>If you run into issues or find a bug, contact the developer at</h4>
                <ul>
                    <li>coltenvantussenbrook@gmail.com</li>
                </ul>
        </div>

        <?php
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

        if ($error) {
            
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
    add_action('add_meta_boxes', 'space_format_meta');
    add_action('save_post', 'space_format_meta_save');
    add_action('admin_menu', 'cpt_submenu');
    add_action('save_post_search_urls', 'check_search_term_param');
    add_action('admin_notices', 'no_search_term_error');

?>
