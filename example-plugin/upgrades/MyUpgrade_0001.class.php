<?php

class MyUpgrade_0001 extends UpgradeScriptModel {
    /**
     * Initial system version
     *
     * @var string
     */
    public $from_version = '0.1';

    /**
     * Final system version
     *
     * @var string
     */
    public $to_version = '1.0';

    /**
     * Return script actions
     *
     * @param void
     * @return array
     */
    function getActions( ) {
        // array('action', 'description');
        return array(
            'update_existing_tables' => 'Upgrade_0001. You can write what you want',
        );
    }// getActions

    function update_existing_tables( ) {
        return true;
        return 'Error'; // otherwise
    }

}
