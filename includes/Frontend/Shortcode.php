<?php

namespace Nhrcc\CoreContributions\Frontend;

/**
 * Shortcode handler class
 */
class Shortcode {

    /**
     * Initialize the class
     */
    function __construct() {
        add_shortcode( 'nhrcc-core-contributions', [ $this, 'render_shortcode' ] );
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
        wp_enqueue_script( 'nhrcc-core-contributions-script' );
        wp_enqueue_style( 'nhrcc-core-contributions-style' );

        return '<div class="nhrcc-core-contributions-shortcode">Hello from Shortcode</div>';
    }
}
