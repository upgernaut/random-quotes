<?php
/**
 * Plugin Name: Random Quotes
 * Plugin URI: https://yoursite.com
 * Description: A plugin to display random quotes with bulk quote management, widget, shortcode, and PHP function support.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RandomQuotesPlugin {
    
    public function __construct() {
        add_action('init', array($this, 'create_post_type'));
        add_action('widgets_init', array($this, 'register_widget'));
        add_action('init', array($this, 'register_shortcode'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_save_bulk_quotes', array($this, 'save_bulk_quotes'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    }
    
    // Create custom post type
    public function create_post_type() {
        $labels = array(
            'name' => __('Random Quotes'),
            'singular_name' => __('Quote'),
            'menu_name' => __('Quotes'),
            'add_new' => __('Add New Quote'),
            'add_new_item' => __('Add New Quote'),
            'edit_item' => __('Edit Quote'),
            'new_item' => __('New Quote'),
            'view_item' => __('View Quote'),
            'search_items' => __('Search Quotes'),
            'not_found' => __('No quotes found'),
            'not_found_in_trash' => __('No quotes found in trash')
        );
        
        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => array('slug' => 'randquotes'),
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => array('title', 'editor')
        );
        
        register_post_type('randquotes', $args);
    }
    
    // Add admin menu
    public function add_admin_menu() {
        add_menu_page(
            'Random Quotes',
            'Quotes Manager',
            'manage_options',
            'random-quotes',
            array($this, 'admin_page'),
            'dashicons-format-quote',
            25
        );
    }
    
    // Admin page content
    public function admin_page() {
        if (isset($_POST['action']) && $_POST['action'] === 'delete_quote') {
            $this->delete_quote($_POST['quote_id']);
        }
        
        $quotes = $this->get_all_quotes();
        ?>
        <div class="wrap">
            <h1>Random Quotes Manager</h1>
            
            <div id="quotes-manager">
                <div class="quotes-form">
                    <h2>Add New Quotes</h2>
                    <div id="quotes-container">
                        <div class="quote-item">
                            <div class="quote-fields">
                                <label>Quote Content:</label>
                                <textarea name="quotes[0][content]" rows="3" placeholder="Enter your quote here..."></textarea>
                                
                                <label>Author (optional):</label>
                                <input type="text" name="quotes[0][author]" placeholder="Quote author">
                                
                                <button type="button" class="remove-quote" onclick="removeQuote(this)">Remove Quote</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="quote-actions">
                        <button type="button" id="add-quote-btn" onclick="addQuote()">+ Add Another Quote</button>
                        <button type="button" id="save-quotes-btn" onclick="saveQuotes()">Save All Quotes</button>
                    </div>
                    
                    <div id="save-message" style="display: none;"></div>
                </div>
                
                <?php if (!empty($quotes)): ?>
                <div class="existing-quotes">
                    <h2>Existing Quotes (<?php echo count($quotes); ?> total)</h2>
                    <div class="quotes-list">
                        <?php foreach ($quotes as $quote): ?>
                        <div class="existing-quote-item">
                            <div class="quote-content">
                                <blockquote><?php echo esc_html(wp_trim_words($quote['content'], 20)); ?></blockquote>
                                <?php if (!empty($quote['author'])): ?>
                                <cite>— <?php echo esc_html($quote['author']); ?></cite>
                                <?php endif; ?>
                            </div>
                            <div class="quote-actions-existing">
                                <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this quote?');">
                                    <input type="hidden" name="action" value="delete_quote">
                                    <input type="hidden" name="quote_id" value="<?php echo $quote['id']; ?>">
                                    <button type="submit" class="button-link-delete">Delete</button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
        .quotes-form {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .quote-item {
            background: #f9f9f9;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            position: relative;
        }
        
        .quote-fields label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .quote-fields textarea,
        .quote-fields input[type="text"] {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .remove-quote {
            background: #dc3232;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            position: absolute;
            top: 10px;
            right: 10px;
        }
        
        .quote-actions {
            margin-top: 20px;
        }
        
        .quote-actions button {
            margin-right: 10px;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        
        #add-quote-btn {
            background: #0073aa;
            color: white;
        }
        
        #save-quotes-btn {
            background: #00a32a;
            color: white;
        }
        
        .existing-quotes {
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .existing-quote-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 15px;
            margin-bottom: 10px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        
        .quote-content {
            flex: 1;
        }
        
        .quote-content blockquote {
            margin: 0 0 5px 0;
            font-style: italic;
        }
        
        .quote-content cite {
            font-size: 0.9em;
            color: #666;
        }
        
        #save-message {
            margin-top: 15px;
            padding: 10px;
            border-radius: 3px;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        </style>
        
        <script>
        let quoteCounter = 1;
        
        function addQuote() {
            const container = document.getElementById('quotes-container');
            const newQuote = document.createElement('div');
            newQuote.className = 'quote-item';
            newQuote.innerHTML = `
                <div class="quote-fields">
                    <label>Quote Content:</label>
                    <textarea name="quotes[${quoteCounter}][content]" rows="3" placeholder="Enter your quote here..."></textarea>
                    
                    <label>Author (optional):</label>
                    <input type="text" name="quotes[${quoteCounter}][author]" placeholder="Quote author">
                    
                    <button type="button" class="remove-quote" onclick="removeQuote(this)">Remove Quote</button>
                </div>
            `;
            container.appendChild(newQuote);
            quoteCounter++;
        }
        
        function removeQuote(button) {
            const quoteItem = button.closest('.quote-item');
            const container = document.getElementById('quotes-container');
            if (container.children.length > 1) {
                quoteItem.remove();
            } else {
                alert('You must have at least one quote field.');
            }
        }
        
        function saveQuotes() {
            const quotes = [];
            const quoteItems = document.querySelectorAll('.quote-item');
            
            quoteItems.forEach(item => {
                const content = item.querySelector('textarea').value.trim();
                const author = item.querySelector('input[type="text"]').value.trim();
                
                if (content) {
                    quotes.push({
                        content: content,
                        author: author
                    });
                }
            });
            
            if (quotes.length === 0) {
                alert('Please enter at least one quote.');
                return;
            }
            
            const saveBtn = document.getElementById('save-quotes-btn');
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
            
            const formData = new FormData();
            formData.append('action', 'save_bulk_quotes');
            formData.append('quotes', JSON.stringify(quotes));
            formData.append('nonce', '<?php echo wp_create_nonce('save_bulk_quotes'); ?>');
            
            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('save-message');
                messageDiv.style.display = 'block';
                
                if (data.success) {
                    messageDiv.className = 'success';
                    messageDiv.textContent = `Successfully saved ${data.data.count} quotes!`;
                    
                    // Clear form
                    document.querySelectorAll('.quote-item textarea, .quote-item input[type="text"]').forEach(field => {
                        field.value = '';
                    });
                    
                    // Reload page after 2 seconds to show new quotes
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    messageDiv.className = 'error';
                    messageDiv.textContent = 'Error saving quotes: ' + data.data;
                }
            })
            .catch(error => {
                const messageDiv = document.getElementById('save-message');
                messageDiv.style.display = 'block';
                messageDiv.className = 'error';
                messageDiv.textContent = 'Error saving quotes: ' + error.message;
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save All Quotes';
            });
        }
        </script>
        <?php
    }
    
    // Enqueue admin scripts
    public function enqueue_admin_scripts($hook) {
        if ($hook === 'toplevel_page_random-quotes') {
            wp_enqueue_script('jquery');
        }
    }
    
    // Save bulk quotes via AJAX
    public function save_bulk_quotes() {
        if (!wp_verify_nonce($_POST['nonce'], 'save_bulk_quotes')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $quotes = json_decode(stripslashes($_POST['quotes']), true);
        $saved_count = 0;
        
        foreach ($quotes as $quote) {
            if (!empty($quote['content'])) {
                $post_data = array(
                    'post_title' => wp_trim_words($quote['content'], 8, '...'),
                    'post_content' => sanitize_textarea_field($quote['content']),
                    'post_status' => 'publish',
                    'post_type' => 'randquotes'
                );
                
                $post_id = wp_insert_post($post_data);
                
                if ($post_id && !empty($quote['author'])) {
                    update_post_meta($post_id, '_quote_author', sanitize_text_field($quote['author']));
                }
                
                if ($post_id) {
                    $saved_count++;
                }
            }
        }
        
        wp_send_json_success(array('count' => $saved_count));
    }
    
    // Get all quotes
    public function get_all_quotes() {
        $args = array(
            'post_type' => 'randquotes',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        $query = new WP_Query($args);
        $quotes = array();
        
        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                $author = get_post_meta($post->ID, '_quote_author', true);
                $quotes[] = array(
                    'id' => $post->ID,
                    'content' => $post->post_content,
                    'author' => $author
                );
            }
        }
        
        wp_reset_postdata();
        return $quotes;
    }
    
    // Delete quote
    public function delete_quote($quote_id) {
        if (current_user_can('manage_options')) {
            wp_delete_post($quote_id, true);
        }
    }
    
    // Register widget
    public function register_widget() {
        register_widget('Random_Quotes_Widget');
    }
    
    // Register shortcode
    public function register_shortcode() {
        add_shortcode('random_quote', array($this, 'shortcode_callback'));
    }
    
    // Shortcode callback
    public function shortcode_callback($atts) {
        $atts = shortcode_atts(array(
            'show_author' => 'true',
            'class' => 'random-quote'
        ), $atts);
        
        return $this->get_random_quote_html($atts['show_author'] === 'true', $atts['class']);
    }
    
    // Get random quote HTML
    public function get_random_quote_html($show_author = true, $css_class = 'random-quote') {
        $quote_data = $this->get_random_quote();
        
        if (!$quote_data) {
            return '<div class="' . esc_attr($css_class) . '">No quotes available.</div>';
        }
        
        $html = '<div class="' . esc_attr($css_class) . '">';
        $html .= '<p style="font-size: 18px; font-weight: bold;">' . esc_html($quote_data['content']) . '</p>';
        if ($show_author && !empty($quote_data['author'])) {
            $html .= '<cite style="font-size: 18px; font-weight: bold;">— ' . esc_html($quote_data['author']) . '</cite>';
        }
        $html .= '</div>';
        
        return $html;
    }
    
    // Get random quote data
    public function get_random_quote() {
        $args = array(
            'post_type' => 'randquotes',
            'posts_per_page' => 1,
            'orderby' => 'rand',
            'post_status' => 'publish'
        );
        
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            $quote = $query->posts[0];
            $author = get_post_meta($quote->ID, '_quote_author', true);
            
            return array(
                'content' => $quote->post_content,
                'title' => $quote->post_title,
                'author' => $author
            );
        }
        
        wp_reset_postdata();
        return false;
    }
    
    // Enqueue styles
    public function enqueue_styles() {
        wp_add_inline_style('wp-block-library', '
            .random-quote blockquote {
                margin: 0;
                padding: 20px;
                background: #f9f9f9;
                border-left: 4px solid #ddd;
                font-style: italic;
            }
            .random-quote cite {
                display: block;
                margin-top: 10px;
                font-weight: bold;
                font-style: normal;
            }
            .widget .random-quote blockquote {
                padding: 15px;
                font-size: 14px;
            }
        ');
    }
}

// Widget Class
class Random_Quotes_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'random_quotes_widget',
            __('Random Quote'),
            array('description' => __('Display a random quote from your quotes collection'))
        );
    }
    
    // Widget frontend
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        $show_author = isset($instance['show_author']) ? $instance['show_author'] : true;
        
        echo $args['before_widget'];
        
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        
        $plugin = new RandomQuotesPlugin();
        echo $plugin->get_random_quote_html($show_author, 'random-quote widget-quote');
        
        echo $args['after_widget'];
    }
    
    // Widget backend form
    public function form($instance) {
        $title = isset($instance['title']) ? $instance['title'] : __('Random Quote');
        $show_author = isset($instance['show_author']) ? $instance['show_author'] : true;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" type="text" 
                   value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <input class="checkbox" type="checkbox" 
                   <?php checked($show_author, true); ?>
                   id="<?php echo $this->get_field_id('show_author'); ?>" 
                   name="<?php echo $this->get_field_name('show_author'); ?>" />
            <label for="<?php echo $this->get_field_id('show_author'); ?>">
                <?php _e('Show Quote Author'); ?>
            </label>
        </p>
        <?php
    }
    
    // Update widget settings
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['show_author'] = isset($new_instance['show_author']) ? true : false;
        return $instance;
    }
}

// Initialize the plugin
new RandomQuotesPlugin();

// PHP Function for theme developers
function display_random_quote($show_author = true, $css_class = 'random-quote') {
    $plugin = new RandomQuotesPlugin();
    return $plugin->get_random_quote_html($show_author, $css_class);
}

// PHP Function to get raw quote data
function get_random_quote_data() {
    $plugin = new RandomQuotesPlugin();
    return $plugin->get_random_quote();
}

?>