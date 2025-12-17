<?php
/**
 * Language Switcher Widget
 * WordPress widget for language switching
 */

if (!defined('ABSPATH')) {
    exit;
}

class AMST_Language_Switcher_Widget extends WP_Widget {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'amst_language_switcher',
            __('Language Switcher', 'auto-multilingual-seo-translator'),
            [
                'description' => __('Display a language switcher for multilingual content', 'auto-multilingual-seo-translator')
            ]
        );
    }
    
    /**
     * Widget output
     * 
     * @param array $args Widget arguments
     * @param array $instance Widget instance
     */
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $style = isset($instance['style']) ? $instance['style'] : 'dropdown';
        echo do_shortcode('[language_switcher style="' . esc_attr($style) . '"]');
        
        echo $args['after_widget'];
    }
    
    /**
     * Widget form
     * 
     * @param array $instance Widget instance
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $style = !empty($instance['style']) ? $instance['style'] : 'dropdown';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php _e('Title:', 'auto-multilingual-seo-translator'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('style')); ?>">
                <?php _e('Style:', 'auto-multilingual-seo-translator'); ?>
            </label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('style')); ?>" 
                    name="<?php echo esc_attr($this->get_field_name('style')); ?>">
                <option value="dropdown" <?php selected($style, 'dropdown'); ?>>
                    <?php _e('Dropdown', 'auto-multilingual-seo-translator'); ?>
                </option>
                <option value="list" <?php selected($style, 'list'); ?>>
                    <?php _e('List', 'auto-multilingual-seo-translator'); ?>
                </option>
            </select>
        </p>
        <?php
    }
    
    /**
     * Update widget
     * 
     * @param array $new_instance New instance
     * @param array $old_instance Old instance
     * @return array Updated instance
     */
    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['style'] = (!empty($new_instance['style'])) ? sanitize_text_field($new_instance['style']) : 'dropdown';
        return $instance;
    }
}
