<?php
/*
 Plugin Name: Example plugin
 Plugin URI: https://github.com/frosso/wp_db-upgrader
 Description: Just an example about how to use wp_db-upgrader
 Version: 1.0
 Author: frosso
 License: Commercial
 */

define( 'MY_PLUGIN', __FILE__ );

define( 'MY_PLUGIN_PATH', dirname( MY_PLUGIN ) );

define( 'MY_PLUGIN_FILES_VERSION', '2.0' );

define( 'MY_UPGRADER_PATH', MY_PLUGIN_PATH . '/upgrader' );

// let's include the library
// you need to move it inside your plugin directory
include_once MY_UPGRADER_PATH . '/upgrader.php';

class MyPluginUpgrader extends UpgraderController {
    protected $plugin = MY_PLUGIN;

    protected $upgrader_path = MY_UPGRADER_PATH;

    protected $files_version = MY_PLUGIN_FILES_VERSION;

    public function __construct( ) {
        parent::__construct( );
    }

    function plugin_activation( ) {
        parent::plugin_activation( );
        
        if ( version_compare( $this->get_db_version( ), '0.2', '<' ) ) {
            // first installation
            update_option( basename( $this->get_plugin( ) ) . '_version', $this->files_version );
        }
    }

    // even more checks?
    public function worth_upgrading( ) {
        $result = parent::worth_upgrading( );

        // try guessing the version
        if ( $result === false ) {
            // let's check somewhere (in the DB?) which version is installed
            // might be a new installation, here you can do more check to guess the version installed
            if ( true ) {
                $return = true;
            }
            // otherwise it's a fresh installation
        }

        return $result;
    }

    /**
     * @return: false if its a fresh installation, the version otherwise
     */
    function get_db_version( ) {
        $version = parent::get_db_version( );
        if ( $version === false ) {
            // might be a new installation, here you can do more check to guess the version installed
            if ( true ) {
                $version = '0.1';
            }
            // altrimenti vuol dire che è un'installazione fresca
        }
        return $version;
    }

}
