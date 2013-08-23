<?php

class UpgraderManager {

    private static $upgraders = array( );

    private static $action2 = 'plugin_upgrader';

    function __construct( ) {
        if ( is_admin( ) ) {
            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                // we're doing an ajax request
                add_action( 'wp_ajax_upgrader-next', array(
                    'UpgraderManager',
                    'next'
                ) );
            } else {
                if ( !self::is_plugin_page( ) ) {
                    add_action( 'init', array(
                        'UpgraderManager',
                        'redirect'
                    ) );
                }
            }
        }

        if ( self::is_plugin_page( true ) ) {
            add_action( 'admin_footer', array(
                'UpgraderManager',
                'admin_footer'
            ) );
            add_action( 'admin_enqueue_scripts', array(
                'UpgraderManager',
                'enqueue_scripts'
            ) );
        }
    }

    public static function add_upgrader( $upgrader ) {
        if ( $upgrader instanceof UpgraderModel ) {
            self::$upgraders[$upgrader->get_plugin( true )] = $upgrader;
        }
    }

    /**
     * Returns a list of plugin to upgrade, false if none is found
     */
    public static function to_upgrade( ) {
        $to_upgrade = array( );
        foreach ( self::$upgraders as $name => $upgrader ) {
            if ( $upgrader->is_upgradable( ) ) {
                $to_upgrade[] = $name;
            }
        }
        return count( $to_upgrade ) > 0 ? $to_upgrade : false;
    }

    static function redirect( ) {
        $upgrades = self::to_upgrade( );
        if ( $upgrades && current_user_can( 'update_plugins' ) ) {
            if ( !isset( $_REQUEST['action2'] ) ) {
                $url = admin_url( 'plugins.php?action2=' . self::$action2 . '&what=' . implode( ',', $upgrades ) );
                wp_redirect( $url );
                exit ;
            }
        }
    }

    /**
     * Checks if we are in the plugin page. if $also_upgrader === true, we also check if we are trying to upgrade
     */
    static function is_plugin_page( $also_upgrader = false ) {
        // global
        global $pagenow;

        // vars
        $return = false;

        // validate page
        if ( in_array( $pagenow, array( 'plugins.php' ) ) ) {

            if ( $also_upgrader === true ) {
                // validate action2
                if ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == self::$action2 ) {
                    $return = true;
                }
            } else {
                $return = true;
            }

        }

        // return
        return $return;
    }

    /**
     * Get an upgrader. If $name is '', returns the first upgrader registered so far
     */
    static function get_upgrader( $name = '' ) {
        $upgrader = false;

        if ( $name == '' ) {// return the first upgrader registered
            $keys = array_keys( self::$upgraders );
            $upgrader = self::$upgraders[$keys[0]];
        } else {
            // does the upgrader $name exists?
            if ( isset( self::$upgraders[$name] ) ) {
                $upgrader = self::$upgraders[$name];
            }
        }

        return $upgrader;
    }

    static function enqueue_scripts( ) {
        wp_enqueue_script( 'upgrader_js', self::get_upgrader( )->get_upgrader_url( ) . '/assets/upgrader.js', array( 'jquery-ui-dialog' ) );

        wp_localize_script( 'upgrader_js', 'upgrader_js_object', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'upgrader_url' => self::get_upgrader( )->get_upgrader_url( ),
        ) );
        wp_localize_script( 'upgrader_js', 'upgrader_translations', array( 'all_done' => __( 'All done!' ), ) );

        wp_enqueue_style( 'wp-jquery-ui-dialog' );
    }

    static function admin_footer( ) {
        // TODO: fare
        $response = '';

        $upgrades = self::to_upgrade( );

        if ( $upgrades ) {
            $response .= '<ul id="upgrader_actions_list">';
            foreach ( $upgrades as $upgrade ) {
                $plugin = self::get_upgrader( $upgrade );
                $current_version = $plugin->get_db_version( );
                $plugin_name = $plugin->get_plugin( true );
                $response .= "<li id='" . $plugin_name . "_upgrade_description' >" . sprintf( __( "Your database version for %s is %s and here are the steps that we need to execute so we can upgrade it to the latest version:" ), $plugin_name, $current_version ) . "</li>";

                $available_scripts = $plugin->get_scripts_disponibili( $plugin->get_db_version( ) );

                foreach ( $available_scripts as $script ) {
                    $group = $script->getGroup( );
                    if ( $script->getActions( ) ) {
                        foreach ( $script->getActions() as $action => $description ) {
                            $response .= '<li upgrade_plugin="' . $plugin_name . '" upgrade_group="' . $group . '" upgrade_action="' . $action . '" class="not_done">' . $description . '</li>';
                        }
                    } else {
                        $response .= '<li upgrade_plugin="' . $plugin_name . '" upgrade_group="' . $group . '" upgrade_action="no_action" class="not_done">Upgrade version</li>';
                    }
                }

            }
            $response .= '</ul>';
            echo '<div id="upgrader-content">' . $response . '</div>';
        }
    }

    public static function next( ) {
        $result = '';

        $next_plugin = isset( $_REQUEST['next_plugin'] ) ? $_REQUEST['next_plugin'] : '';
        $next_group = isset( $_REQUEST['next_group'] ) ? $_REQUEST['next_group'] : '';
        $next_action = isset( $_REQUEST['next_action'] ) ? $_REQUEST['next_action'] : '';

        $upgrader = self::get_upgrader( $next_plugin );

        if ( $upgrader ) {
            $result = $upgrader->execute_action( $next_group, $next_action );
        } else {
            $result = 'Upgrader not found';
        }

        if ( $result !== true ) {
            header( 'HTTP/1.1 500 Internal Server Error' );
            echo $result;
        }

        die( );
    }

}

new UpgraderManager( );
