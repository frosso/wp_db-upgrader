<?php

if ( !class_exists( 'UpgradeModel' ) )
    require_once dirname( __FILE__ ) . '/models/UpgradeModel.class.php';

if ( !class_exists( 'UpgradeScriptModel' ) )
    require_once dirname( __FILE__ ) . '/models/UpgradeScriptModel.class.php';

if ( !class_exists( 'UpgraderController' ) )
    require_once dirname( __FILE__ ) . '/controllers/UpgraderController.class.php';
