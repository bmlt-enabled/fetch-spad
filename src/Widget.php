<?php

namespace Spad;

class Widget extends \WP_Widget
{
    public function __construct()
    {
        $widget_ops = array(
            'classname' => 'widget',
            'description' => 'Displays the Spiritual Principle A Day',
        );
        parent::__construct('widget', 'Fetch SPAD', $widget_ops);
    }

    public function widget($args, $instance): void
    {
        $spad_main = new Main();
        echo $args['before_widget'];
        if (! empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        echo $spad_main->runMain();
        echo $args['after_widget'];
    }

    public function form($instance): void
    {
        $title = ! empty($instance['title']) ? $instance['title'] : esc_html__('Title', 'text_domain');
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_attr_e('Title:', 'text_domain'); ?>
            </label>
            <input
                class="widefat"
                id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                type="text"
                value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance): array
    {
        $instance = array();
        $instance['title'] = (! empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
}
