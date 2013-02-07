<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'static_vpn_basic';
$app['version'] = '1.3.6';
$app['release'] = '1';
$app['vendor'] = 'Tim Burgess';
$app['packager'] = 'Tim Burgess';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('static_vpn_basic_app_description');
$app['tooltip'] = lang('static_vpn_basic_tooltip');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('static_vpn_basic_app_name');
$app['category'] = lang('base_category_network');
$app['subcategory'] = lang('base_subcategory_vpn');

/////////////////////////////////////////////////////////////////////////////
// Controllers
/////////////////////////////////////////////////////////////////////////////

$app['controllers']['static_vpn']['title'] = lang('static_vpn_basic_app_name');

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['core_requires'] = array(
    'app-network',
    'app-ipsec-core'
);

$app['core_file_manifest'] = array(
    'logrotate-ipsec' => array('target' => '/etc/logrotate.d/ipsec'),
    'rsyslog-ipsec.conf' => array('target' => '/etc/rsyslog.d/ipsec.conf'),
);

$app['delete_dependency'] = array(
    'app-static-vpn-basic',
    'app-static-vpn-basic-core',
    'openswan'
);
