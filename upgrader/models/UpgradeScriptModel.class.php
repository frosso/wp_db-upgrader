<?php

abstract class UpgradeScriptModel {
    /**
     * Initial system version
     *
     * @var string
     */
    public $from_version;

    /**
     * Final system version
     *
     * @var string
     */
    public $to_version;

    /**
     * Return upgrade actions
     *
     * @return array
     */
    function getActions( ) {
        return null;
    }// getActions

    /**
     * Return from version
     *
     * @return float
     */
    function getFromVersion( ) {
        return $this->from_version;
    }// getFromVersion

    /**
     * Return to version
     *
     * @return float
     */
    function getToVersion( ) {
        return $this->to_version;
    }// getToVersion

    /**
     * Identify this uprade script
     *
     * @return string
     */
    function getGroup( ) {
        return (string)$this->from_version . '-' . (string)$this->to_version;
    }// getGroup

    function end_upgrade( $plugin ) {
        update_option( basename( $plugin ) . '_version', $this->to_version );
        return true;
    }// end_upgrade

    function start_upgrade( ) {
        // backup of db?
        return true;
    }

}
