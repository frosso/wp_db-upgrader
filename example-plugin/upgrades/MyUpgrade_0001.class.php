<?php

class MyUpgrade_0001 extends UpgraderScriptModel {

    public $from_version = '0.1';

    public $to_version = '1.0';

    function getActions( ) {
        // array('action', 'description');
        return array( 'update_existing_tables' => 'Upgrade_0001. You can write what you want', );
    }

    function update_existing_tables( ) {
        return true;
        // otherwise
        return 'Generic Error';
    }

}
