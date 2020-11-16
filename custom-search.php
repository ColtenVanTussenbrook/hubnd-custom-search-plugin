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
        $urls = [];
        $space_format = '';
        $temp_search_term = $search_term;
        $results_str = '';
        
        // loop through and replace %%search-term%% with appropriate search
        foreach($url_data as $url) {
            $urls_inner = [];
            $anchor_text = $url[1];
            $space_format = $url[2];         

            $temp_search_term = $space_format === 'plus-sign' ? urlencode($search_term) : rawurlencode($search_term);

            $urls_inner[] = str_replace($this->search_placeholder, $temp_search_term, $url[0]);
            $urls_inner[] = $anchor_text;
            $urls[] = $urls_inner;
        }

        //$urls_inner[0] = link
        //$urls_inner[1] = anchor text

        $results_str = '<div class="hubnd-custom-search-results">';

        foreach($urls as $link_data) {
            $results_str .= '<p class="search-result-link"><a href="' . $link_data[0] . '" target="_blank">' . $link_data[1] . '</a></p>';
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
            $meta = get_post_meta($url->ID, 'space_format_dropdown', true);

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

            $urls[] = $url_meta_arr;
        }

        return $urls;
    }

}

$custom_search = new CustomSearch();
