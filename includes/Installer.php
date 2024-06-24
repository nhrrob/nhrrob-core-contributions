<?php

namespace Nhrcc\CoreContributions;

/**
 * Installer class
 */
class Installer {

    /**
     * Run the installer
     *
     * @return void
     */
    public function run() {
        $this->add_version();
        $this->create_tables();
    }

    /**
     * Add time and version on DB
     */
    public function add_version() {
        $installed = get_option( 'nhrcc_core_contributions_installed' );

        if ( ! $installed ) {
            update_option( 'nhrcc_core_contributions_installed', time() );
        }

        update_option( 'nhrcc_core_contributions_version', NHRCC_VERSION );
    }

    /**
     * Create necessary database tables
     *
     * @return void
     */
    public function create_tables() {
        //
    }
}
