<?php
/*
 Plugin Name: Example plugin
 Plugin URI: https://github.com/frosso/wp_db-upgrader
 Description: Just an example about how to use wp_db-upgrader
 Version: 2.0
 Author: frosso
 License: Commercial
 */

define( 'MY_PLUGIN', __FILE__ );

define( 'MY_PLUGIN_PATH', dirname( MY_PLUGIN ) );

define( 'MY_PLUGIN_FILES_VERSION', '2.0' );

define( 'MY_UPGRADER_PATH', '/upgrader' );

// let's include the library
// you need to move it inside your plugin directory
include_once MY_UPGRADER_PATH . '/upgrader.php';

class MyPluginUpgrader extends UpgraderModel {

    private $detected_previously_installed = true;

    public function __construct( ) {
        $args = array(
            'plugin' => MY_PLUGIN,
            'files_version' => MY_PLUGIN_FILES_VERSION,
            'upgrader_path' => MY_UPGRADER_PATH,
        );
        parent::__construct( $args );
    }

    /**
     * @return: false if its a fresh installation, the version otherwise
     */
    function get_db_version( ) {
        $version = parent::get_db_version( );
        if ( $version === false ) {
            // might be a new installation, here you can do more check to guess the version installed
            if ( $this->detected_previously_installed ) {
                $version = '1.0';
            }
            // altrimenti vuol dire che è un'installazione fresca
        }
        return $version;
    }

}

UpgraderManager::add_upgrader( new MyPluginUpgrader( ) );
