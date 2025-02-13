<?php

namespace Nhrcc\CoreContributions\Frontend;

use Nhrcc\CoreContributions\App;

/**
 * Shortcode handler class
 */
class Shortcode extends App
{

    /**
     * Initialize the class
     */
    function __construct()
    {
        // add_shortcode('nhrcc_core_contributions', [$this, 'render_shortcode']);
    }

    /**
     * Shortcode handler
     *
     * @param  array $atts
     * @param  string $content
     *
     * @return string
     */
    public function render_shortcode($atts, $content = '')
    {
        
    }
}
