<?php

namespace Nhrcc\CoreContributions;

/**
 * The admin class
 */
class Blocks extends App {

    /**
     * Class constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Register blocks
     *
     * @return void
     */
    public function init() {
        add_action( 'init', [ $this, 'register_blocks' ] );
    }

    /**
     * Register the blocks
     *
     * @return void
     */
    public function register_blocks() {
        register_block_type( 
            NHRCC_PATH . '/build/core-contributions-block',
            // array(
            //     'render_callback' => array($this, 'render_table_block'),
            // )
        );

        // register_block_type( __DIR__ . '/build/copyright-date-block' );
        
    }

    // public function render_table_block($attributes) {
        
    // }
}
