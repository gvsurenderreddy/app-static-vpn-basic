<?php

/**
 * IPsec Basic Server summary view.
 *
 * @category   apps
 * @package    static-vpn-basic
 * @subpackage views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/static_vpn_basic_basic/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  
//  
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('static_vpn_basic');
$this->lang->load('base');

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    lang('static_vpn_basic_tunnel_name'),
    lang('static_vpn_basic_tunnel_local'),
    lang('static_vpn_basic_tunnel_remote'),
    lang('static_vpn_basic_tunnel_type'),
);

$types = array(
    'add' => 'Listen',
    'start' => 'Automatic',
    'ignore' => 'Manual'
);

///////////////////////////////////////////////////////////////////////////////
// Anchors
///////////////////////////////////////////////////////////////////////////////

$anchors = array(anchor_add('/app/static_vpn_basic/add/'));

///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

foreach ($tunnels as $entry) {
    $fieldwidth = 25;
    $name = $entry['name'];
    $displayname = substr($name, 0, $fieldwidth);
    $local = substr($entry['left'], 0, $fieldwidth);
    if ($local == '%defaultroute')
        $local = lang('static_vpn_basic_defaultroute');
    $remote = substr($entry['right'], 0, $fieldwidth);
    if ($remote == '%any')
        $remote = lang('static_vpn_basic_any');
    $type = $types[$entry['auto']];
    $status = $entry['status'];
    $policy = $entry['policy'];

    ///////////////////////////////////////////////////////////////////////////
    // Item buttons
    ///////////////////////////////////////////////////////////////////////////


    $detail_buttons = button_set(
        array(
            anchor_custom('/app/static_vpn_basic/view/' . $name, 'View')
        )
    );

    ///////////////////////////////////////////////////////////////////////////
    // Item details - main summary
    ///////////////////////////////////////////////////////////////////////////

    $item['title'] = $name;
    $item['action'] = '/app/static_vpn_basic/view/' . $name;
    $item['anchors'] = $detail_buttons;
    $item['details'] = array(
        $displayname,
        $local,
        $remote,
        $type
    );

    $items[] = $item;

}

///////////////////////////////////////////////////////////////////////////////
// Summary tables
///////////////////////////////////////////////////////////////////////////////

echo summary_table(
    lang('static_vpn_basic_ipsec_server'),
    $anchors,
    $headers,
    $items
);
