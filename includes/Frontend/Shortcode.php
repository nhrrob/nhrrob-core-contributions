<?php

namespace NhrrobCoreContributions\Frontend;

/**
 * Shortcode handler class
 */
class Shortcode {

    /**
     * Initialize the class
     */
    function __construct() {
        add_shortcode( 'nhrrob-core-contributions', [ $this, 'render_shortcode' ] );
    }

    /**
     * Shortcode handler class
     *
     * @param  array $atts
     * @param  string $content
     *
     * @return string
     */
    public function render_shortcode( $atts, $content = '' ) {
        wp_enqueue_script( 'nhrrob-core-contributions-script' );
        wp_enqueue_style( 'nhrrob-core-contributions-style' );

        return '<div class="nhrrob-core-contributions-shortcode">Hello from Shortcode</div>';
    }
}
