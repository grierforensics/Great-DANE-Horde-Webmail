<?php

// *** Great DANE Preferences ***

$prefGroups['greatdane'] = array(
    'column' => _("General"),
    'label' => _("Great DANE"),
    'desc' => _("Configure Great DANE support for S/MIME."),
    'members' => array(
        'greatdanemanagement'
    ),
    'suppress' => function() {
        return (!isset($GLOBALS['prefs']['use_smime']));
    }
);

$_prefs['greatdanemanagement'] = array(
    'value' => array(
        'gd_engine_addr', 'gd_try_encrypt'
    ),
    'type' => 'container'
);

$_prefs['gd_engine_addr'] = array(
    'type' => 'text',
    'value' => 'http://127.0.0.1:25353',
    'advanced' => true,
    'desc' => _("Great DANE Engine Address")
);

$_prefs['gd_try_encrypt'] = array(
    'type' => 'checkbox',
    'value' => false,
    'desc' => _("Should S/MIME encryption be performed opportunistically?")
);

