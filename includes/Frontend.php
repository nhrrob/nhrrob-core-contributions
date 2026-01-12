<?php
namespace Nhrcc\CoreContributions;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Frontend handler class
 */
class Frontend {

    /**
     * Initialize the class
     */
    function __construct() {
        new Frontend\Shortcode();
    }
}
