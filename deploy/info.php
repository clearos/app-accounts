<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'accounts';
$app['version'] = '1.5.40';
$app['release'] = '1';
$app['vendor'] = 'ClearFoundation';
$app['packager'] = 'ClearFoundation';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('accounts_app_description');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('accounts_app_name');
$app['category'] = lang('base_category_system');
$app['subcategory'] = 'Accounts Manager';

/////////////////////////////////////////////////////////////////////////////
// Controllers
/////////////////////////////////////////////////////////////////////////////

$app['controllers']['accounts']['title'] = $app['name'];
$app['controllers']['plugins']['title'] = lang('accounts_plugins');
$app['controllers']['extensions']['title'] = lang('accounts_extensions');

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['requires'] = array(
    'app-mode-core',
);

$app['core_requires'] = array(
    'app-events-core',
    'app-mode-core',
    'csplugin-filewatch',
);

$app['core_directory_manifest'] = array(
    '/var/clearos/events/accounts' => array(),
    '/var/clearos/events/accounts_initialized' => array(),
    '/var/clearos/accounts' => array(),
    '/var/clearos/accounts/drivers' => array(),
    '/var/clearos/accounts/plugins' => array(),
    '/var/clearos/accounts/lock' => array(
        'mode' => '0775',
        'owner' => 'root',
        'group' => 'webconfig',
    ),
);

$app['core_file_manifest'] = array(
    'accounts-init' => array(
        'target' => '/usr/sbin/accounts-init',
        'mode' => '0755',
    ),
    'accounts' => array(
        'target' => '/var/clearos/events/accounts/accounts',
        'mode' => '0755',
    ),
    'initialize-plugins' => array(
        'target' => '/usr/sbin/initialize-plugins',
        'mode' => '0755',
    ),
    'initialize-builtin-directory' => array(
        'target' => '/usr/sbin/initialize-builtin-directory',
        'mode' => '0755',
    ),
    'nscd.php'=> array('target' => '/var/clearos/base/daemon/nscd.php'),
    'filewatch-accounts-event.conf'=> array('target' => '/etc/clearsync.d/filewatch-accounts-event.conf'),
    'filewatch-accounts-initialized-event.conf'=> array('target' => '/etc/clearsync.d/filewatch-accounts-initialized-event.conf'),
);
