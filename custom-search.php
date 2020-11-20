<?php

class CustomSearch {
    
    public $search_term;
    public $search_placeholder = '%%search-term%%';

    public function __construct() {
        $this->shortcode_dec();
        $this->set_search_term();
    }

    public function shortcode_dec() {
        add_shortcode('custom-search-field', array($this, 'hubnd_custom_search'));
    }

    public function set_search_term() {
        if (!empty($_GET['search-term'])) {
            $this->search_term = $_GET['search-term'];
        } else {
            $this->search_term = '';
        }
    }

    public function hubnd_custom_search() {
        ob_start();
        echo $this->hubnd_custom_search_wrapper('start');
        $search_results = '';
        $html = $this->custom_search_html();
        $sanitized = $this->validate_input();

        if ($this->search_term) {
            $search_results = $this->display_search_results($sanitized);
        }
        
        $html = ob_get_clean();
        return $html . $search_results . $this->hubnd_custom_search_wrapper('end');
        
    }

    public function hubnd_custom_search_wrapper($place) {
        if ($place === 'start') {
            echo '<div class="hubnd_custom_search_wrapper">';
        } else {
            echo '</div>';
        }
    }

    public function custom_search_html() {
        ?>
        <div class="hubnd-custom-search-box">
            <form action="<?php the_permalink(); ?> " method="get">
                <div class="custom-search-box-fields">
                    <input type="text" name="search-term" placeholder="Search Research Topics" class="custom-search-input" value="<?php echo $this->search_term; ?>">
                    <input type="hidden" name="submitted" value="1">
                    <input type="submit" class="search-button" value="SEARCH"></input>
                </div>
            </form>
        </div>
        <?php
    }

    private function validate_input() {
        if (!empty($this->search_term)) {
            // sanitize user inputted search term
            $term = trim($this->search_term);
            $term = stripslashes($term);
            $sanitized_term = htmlspecialchars($term);
            $sanitized_term = strip_tags($sanitized_term);

            //run it through WPs sanitization just for added layer of safety
            $sanitized_term = sanitize_text_field($sanitized_term);
            return $sanitized_term;
        }
    }

    public function display_search_results($search_term) {
        $url_data = $this->query_urls();
        $temp_search_term = $search_term;
        $results_str = '';
        $category_list = get_terms(array('taxonomy' => 'search_topic_category'));

        // Sort category list by description
        usort($category_list, function($a,$b) {
            return strcmp($a->description, $b->description);
        });

        $results_str = '<div class="hubnd-custom-search-results">';

        foreach($category_list as $cat) {          
            $results_str .= '<div class="hubnd-custom-search-results-category">';  
            $results_str .= '<h4 class="hubnd-custom-search-results-heading">' . $cat->name . '</h4>';

            foreach($url_data as $url) {
                if (in_array($cat->slug, $url[3])) {
                    $temp_search_term = $url[2] === 'plus-sign' ? urlencode($search_term) : rawurlencode($search_term);
                    $replaced_url = str_replace($this->search_placeholder, $temp_search_term, $url[0]);
                    $results_str .= '<p class="search-result-link"><a href="' . $replaced_url . '" target="_blank">' . $url[1] . '</a></p>';
                }
            }

            $results_str .= '</div>';
        }

        $results_str .= '</div>';

        return $results_str;   
    }

    public function query_urls() {
        $urls = [];
        $url_post_types = get_posts(
           array(
               'post_type' => 'search_urls',
               'posts_per_page' => '-1',
           )
        );

        foreach($url_post_types as $url) {
            $url_meta_arr = [];
            $content = $url->post_content;
            $title = $url->post_title;
            $categories = get_the_terms($url->ID, 'search_topic_category');
            $category_arr = [];
            $meta = get_post_meta($url->ID, 'space_format_dropdown', true);

            foreach($categories as $category) {
                if ($category->slug) {
                    array_push($category_arr, $category->slug);
                } 
            }

            if (!empty($content)) {
                if (strpos($content, $this->search_placeholder) !== false) {
                    $content = trim($content);
                    $url_meta_arr[] = $content;
                }
            } else {
                $url_meta_arr[] = ''; // pass empty string to maintain order in array
            }

            if ($title) {
                $url_meta_arr[] = $title;
            } else {
                $url_meta_arr[] = '';
            }

            if ($meta) {
                $url_meta_arr[] = $meta;
            } else {
                $url_meta_arr[] = '';
            }

            $url_meta_arr[] = $category_arr; // will be an empty array anyway if not set
            $urls[] = $url_meta_arr;
        }

        return $urls;
    }

}

$custom_search = new CustomSearch();
