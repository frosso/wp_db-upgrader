<?php

class UpgraderModel {

    /**
     * Which is our plugin name?
     */
    private $plugin;

    /**
     * Which is our file version? Every time you do an upgrade in the db, you should createa script and increase this number
     */
    private $files_version;

    /**
     * the directory where we can find the upgrader
     */
    private $upgrader_path = '/app/libraries/upgrader';

    /**
     * Where are our upgrades?
     */
    private $upgrades_folder = '/upgrades';

    public function __construct( $attributes ) {
        // TODO: set args. without them, you cannot instantiate this class
        $minimal_attributes = array(
            'plugin' => 0,
            'files_version' => 0,
        );
        foreach ( $attributes as $attribute => $value ) {
            $this->$attribute = $value;
            unset( $minimal_attributes[$attribute] );
        }
        if ( count( $minimal_attributes ) > 0 ) {
            // there still are attributes to set
            throw new Exception( "UpgraderModel: You have to set all the minimal attributes (plugin, files_version)", 1 );
        }
        register_activation_hook( $this->get_plugin( ), array(
            &$this,
            'plugin_activation'
        ) );

    }

    function plugin_activation( ) {
        if ( !is_plugin_active( $this->get_plugin( ) ) ) {
            // first installation or it has been (activated and deactivated)
            if ( $this->get_db_version( ) !== false ) {
                // plugin previously installed, but deactivated
                $this->set_version( $this->get_db_version( ) );
            } else {
                // plugin never installed before
                $this->set_version( $this->get_db_version( ) );
            }
        }
    }

    /**
     * @return: false if its a fresh installation, the version otherwise
     */
    public function get_db_version( ) {
        return get_option( $this->get_plugin( true ) . '_version', false );
    }

    public function get_upgrader_url( ) {
        return plugins_url( $this->upgrader_path, $this->get_plugin( ) );
    }

    public function get_upgrades_folder( ) {
        return dirname( $this->get_plugin( ) ) . $this->upgrades_folder;
    }

    public function get_plugin( $basename = false ) {
        return $basename ? basename( $this->plugin ) : $this->plugin;
    }

    /**
     * checks if this plugin needs to be upgraded
     */
    final public function is_upgradable( ) {
        $old_version = $this->get_db_version( );

        $return = false;

        if ( $this->files_version != $old_version ) {
            // is it a fresh installation? if not, we have to check for upgrade scripts
            if ( $old_version ) {
                $available_scripts = $this->get_scripts_disponibili( $old_version );
                if ( count( $available_scripts ) > 0 ) {
                    $return = true;
                }
            }
            // otherwise it's a fresh installation, we don't have to upgrade
        }

        return $return;
    }

    /**
     * get the scripts that are newer that $new_version
     */
    public function get_scripts_disponibili( $newer_than = '' ) {
        $files = $upgrades = UpgraderUtilities::get_files( $this->get_upgrades_folder( ), 'php' );
        $result = array( );

        if ( count( $files ) > 0 ) {
            sort( $files );

            foreach ( $files as $file ) {
                require_once $file;
                $basename = basename( $file );

                $class_name = substr( $basename, 0, strpos( $basename, '.' ) );

                $script = new $class_name( $this );

                if ( $script instanceof UpgraderScriptModel && version_compare( $script->getToVersion( ), $newer_than, '>' ) ) {
                    $result[] = $script;
                } // if
            } // foreach
        }// if

        return empty( $result ) ? false : $result;

    }

    /**
     * Return a script, given the group
     *
     * @param string $group
     * @return UpgraderScriptModel, false if not found
     */
    public function get_script_by_group( $group ) {
        $files = UpgraderUtilities::get_files( $this->get_upgrades_folder( ), 'php' );

        if ( !empty( $files ) ) {
            foreach ( $files as $file ) {
                require_once $file;
                $basename = basename( $file );

                $class_name = substr( $basename, 0, strpos( $basename, '.' ) );

                $script = new $class_name( $this );

                if ( $script instanceof UpgraderScriptModel && $script->getGroup( ) == $group ) {
                    return $script;
                } // if
            } // foreach
        }// if

        return false;
    }// getScript

    /**
     * You can override this method if you want to, but you need to call it with super::set_version()
     */
    protected function set_version( $version ) {
        update_option( $this->get_plugin( true ) . '_version', $version );
    }

    /**
     * Execute given action
     *
     * @param string $group
     * @param string $action
     * @return boolean
     */
    public function execute_action( $group, $action ) {
        $script = $this->get_script_by_group( $group );

        if ( $script instanceof UpgraderScriptModel ) {
            if ( method_exists( $script, $action ) ) {
                // TODO: controllare se ultima azione
                $result = $script->$action( );
                if ( $result === true ) {
                    $actions = $script->getActions( );
                    // let's find out if we are at the last action of the script
                    $action_number = count( $actions );
                    for ( $i = 0, $limit = count( $actions ); $i < $limit; ++$i ) {
                        if ( isset( $actions[$action] ) ) {
                            $action_number = $i + 1;
                            // $i starts at 0
                            break;
                        }

                    }
                    // are we at the last action set?
                    if ( count( $actions ) == $action_number ) {
                        // we need to increase the plugin version number
                        $this->set_version( $script->getToVersion( ) );
                    }
                }
                return $result;
            } else {
                if ( $action == 'no_action' ) {
                    $this->set_version( $script->getToVersion( ) );
                    return true;
                } else {
                    return "Invalid action";
                }
            }

        } else {
            return "Invalid group";
        } // if
    } // execute_action

}
