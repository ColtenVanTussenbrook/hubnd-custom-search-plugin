<?php 

// Documentation to make adding links/categories a littler easier
// Added through a submenu inside the custom post type

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
                    <li>Select the category it should show up under in the right column that says Search Topic Categories</li>
                    * You can add categories from within the post, or see instructions below under "To add a category". Do know that you'll need to update the order in the Search Topic Categories screen. 
                    <li>In the Space Format dropdown, select which format of spacing the URL uses. Spaces will either be a plus sign or a percent sign.</li>
                    <li>Click "Publish" or "Update", depending on if it's a new entry or editing an existing one</li>
                    <li>Test out that your new URL is showing up in the search results</li>
                </ol>
            <br>
            <h4>To delete a URL</h4>
            <ol>
                <li>Hover over the entry under Custom Search URLS and click trash</li>
            </ol>
            <h4>To add a category</h4>
            <ol>
                <li>Click on Search Topic Categories under Custom Search URLs in WordPress admin bar</li>
                <li>Fill in a name for the category (this will display as the category heading)</li>
                <li>Enter a slug (just the name in lowercase and dashes where spaces would go)</li>
                <li>Ignore Parent Category</li>
                <li>Enter a number in the description for where it should show up in order with other categories. 1 will display first. Note that
                    if you update a number, the other ones won't automatically update so you <i>will</i> need to go through and update the other numbers</li>
                <li>Click Add New Category to save</li>
                * you will add these categories to the specific links in the Custom Search URLs posts.
            </ol>
            <h4>To place search box on a page</h4>
            <ol>
                <li>Go to specific page or post and use the Divi Builder</li>
                <li>Add a new module, and use the Code module</li>
                <li>Type in [custom-search-field] in the Code module text field</li>
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

add_action('admin_menu', 'cpt_submenu');
