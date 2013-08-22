<?php

abstract class UpgraderController {

    /**
     * Which is our plugin name?
     */
    protected $plugin;

    /**
     * Which is our file version? Every time you do an upgrade in the db, you should createa script and increase this number
     */
    protected $files_version;

    /**
     * the directory where we can find the upgrader
     */
    protected $upgrader_path = '/app/libraries/upgrader';

    /**
     * Where are our upgrades?
     */
    protected $upgrades_folder = '/upgrades';

    function __construct( ) {
        register_activation_hook( $this->get_plugin( ), array(
            &$this,
            'plugin_activation'
        ) );

        add_action( 'wp_ajax_upgrader-next', array(
            &$this,
            'next'
        ) );

        if ( !(defined( 'DOING_AJAX' ) && DOING_AJAX) ) {
            if ( is_admin( ) && !$this->validate_page( ) ) {
                add_action( 'init', array(
                    &$this,
                    'redirect'
                ) );
            }
        }

        if ( $this->validate_page( ) && $this->worth_upgrading( ) ) {
            add_action( 'admin_footer', array(
                &$this,
                'admin_footer'
            ) );
            add_action( 'admin_enqueue_scripts', array(
                &$this,
                'enqueue_scripts'
            ) );
        }

    }

    function plugin_activation( ) {
        if ( !is_plugin_active( $this->get_plugin( ) ) ) {
            // first installation or (activation and deactivation)
            if ( $this->get_db_version( ) !== false ) {
                // plugin previously installed, but deactivated
                update_option( basename( $this->get_plugin( ) ) . '_version', $this->get_db_version( ) );
            } else {
                // plugin never installed before
                update_option( basename( $this->get_plugin( ) ) . '_version', $this->files_version );
            }
        }
    }

    function next( ) {

        $result = '';
        $next_group = $_REQUEST['next_group'];
        $next_action = $_REQUEST['next_action'];

        $group = UpgradeModel::getScriptByGroup( $next_group, $this->get_upgrades_folder( ) );

        if ( $group instanceof UpgradeScriptModel ) {
            if ( $next_action == 'end_upgrade' )
                $result = $group->$next_action( $this->get_plugin( ) );
            else
                $result = $group->$next_action( );
        } else {
            $result = 'Group not found';
        }

        if ( $result !== true ) {
            header( 'HTTP/1.1 500 Internal Server Error' );
            echo $result;
        }

        die( );
    }

    function redirect( ) {
        if ( $this->worth_upgrading( ) && current_user_can( 'update_plugins' ) ) {
            $url = admin_url( 'plugins.php?action=upgrade&what=' . basename( $this->get_plugin( ) ) );
            wp_redirect( $url );
            exit ;
        }
    }

    function worth_upgrading( ) {
        $old_version = $this->get_db_version( );

        $return = false;

        if ( $this->files_version != $old_version ) {

            if ( $old_version ) {

                //cerchiamo di capire se esistono degli script più nuovi della nostra versione
                $upgrades = UpgradeModel::get_files( $this->get_upgrades_folder( ) );
                $available_scripts = UpgradeModel::scripts_disponibili( $old_version, $upgrades );
                if ( count( $available_scripts ) > 0 ) {
                    $return = true;
                }
            }
            // otherwise it's a fresh installation
        }

        return $return;

    }

    /**
     * @return: false if its a fresh installation, the version otherwise
     */
    function get_db_version( ) {
        return get_option( basename( $this->get_plugin( ) ) . '_version', false );
    }

    function get_upgrader_url( ) {
        return plugins_url( $this->upgrader_path, $this->get_plugin( ) );
    }

    function get_upgrades_folder( ) {
        return dirname( $this->get_plugin( ) ) . $this->upgrades_folder;
    }

    function get_plugin( ) {
        return $this->plugin;
    }

    function enqueue_scripts( ) {
        wp_enqueue_script( 'upgrader_js', $this->get_upgrader_url( ) . '/assets/upgrader.js', array( 'jquery-ui-dialog' ) );

        wp_localize_script( 'upgrader_js', 'upgrader_js_object', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'upgrader_url' => $this->get_upgrader_url( ),
        ) );
        wp_localize_script( 'upgrader_js', 'upgrader_translation', array( 'all_done' => __( 'All done!' ), ) );

        wp_enqueue_style( 'wp-jquery-ui-dialog' );
    }

    /*
     *  validate_page
     *
     *  @description: returns true | false. Used to stop a function from continuing
     */
    function validate_page( ) {
        // global
        global $pagenow;

        // vars
        $return = false;

        // validate page
        if ( in_array( $pagenow, array( 'plugins.php' ) ) ) {

            // validate post type
            if ( isset( $_GET['action'] ) && $_GET['action'] == 'upgrade' ) {

                if ( isset( $_GET['what'] ) && $_GET['what'] == basename( $this->get_plugin( ) ) ) {
                    $return = true;
                }

            }

        }

        // return
        return $return;
    }

    function admin_footer( ) {

        $upgrades = UpgradeModel::get_files( $this->get_upgrades_folder( ) );

        $current_version = $this->get_db_version( );

        $available_scripts = UpgradeModel::scripts_disponibili( $current_version, $upgrades );

        if ( $available_scripts ) {
            $response = "<p>" . sprintf( __( "Your database version is %s and here are the steps that we need to execute so we can upgrade it to the latest version:" ), $current_version ) . "</p>";

            $response .= '<ul id="upgrader_actions_list">';
            foreach ( $available_scripts as $script ) {
                $group = $script->getGroup( );

                $response .= '<li upgrade_group="' . $group . '" upgrade_action="start_upgrade" class="not_done">' . __( "Start Group upgrade" ) . '</li>';

                if ( $script->getActions( ) ) {
                    foreach ( $script->getActions() as $action => $description ) {
                        $response .= '<li upgrade_group="' . $group . '" upgrade_action="' . $action . '" class="not_done">' . ($description) . '</li>';
                    }
                }

                $response .= '<li upgrade_group="' . $group . '" upgrade_action="end_upgrade" class="not_done">' . __( "End Group upgrade" ) . '</li>';

            }
            $response .= '</ul>';
        } else {
            $response = "<p>" . sprintf( __( "Your db version is %s, but no upgrade scripts are present" ), $current_version ) . "</p>";
        }

        echo '<div id="upgrader-content">' . $response . '</div>';
    }

}
