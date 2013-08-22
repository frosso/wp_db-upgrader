<?php

class Upgrade_0002 extends UpgradeScriptModel {
    /**
     * Initial system version
     *
     * @var string
     */
    public $from_version = '1.0';

    /**
     * Final system version
     *
     * @var string
     */
    public $to_version = '2.0';

    /**
     * Return script actions
     *
     * @param void
     * @return array
     */
    function getActions( ) {
        // nothing to do here
        return array( );
    }// getActions

}
