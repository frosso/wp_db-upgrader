<?php

abstract class UpgraderScriptModel {
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
    function get_actions( ) {
        return null;
    }// get_actions

    /**
     * Return from version
     *
     * @return float
     */
    function get_from_version( ) {
        return $this->from_version;
    }// get_from_version

    /**
     * Return to version
     *
     * @return float
     */
    function get_to_version( ) {
        return $this->to_version;
    }// get_to_version

    /**
     * Identify this uprade script
     *
     * @return string
     */
    function get_group( ) {
        return (string)$this->from_version . '-' . (string)$this->to_version;
    }// get_group

}
