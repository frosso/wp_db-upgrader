<?php

if ( !class_exists( UpgradeModel ) )
    include_once dirname( __FILE__ ) . '/models/UpgradeModel.class.php';

if ( !class_exists( UpgradeScriptModel ) )
    include_once dirname( __FILE__ ) . '/models/UpgradeScriptModel.class.php';

if ( !class_exists( UpgraderController ) )
    include_once dirname( __FILE__ ) . '/controllers/UpgraderController.class.php';
