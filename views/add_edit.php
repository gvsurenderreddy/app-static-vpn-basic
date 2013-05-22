<?php

/**
 * IPsec add or edit settings view.
 *
 * @category   apps
 * @package    static-vpn-basic
 * @subpackage views
 * @author     Tim Burgess <trburgess@gmail.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/static_vpn_basic/
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

$this->lang->load('base');
$this->lang->load('static_vpn_basic');

///////////////////////////////////////////////////////////////////////////////
// Form handler
///////////////////////////////////////////////////////////////////////////////

if ($form_type === 'edit') {
    $read_only = FALSE;
    $read_only_name = TRUE;
    $form_path = '/static_vpn_basic/edit';
    $buttons = array(
        form_submit_update('submit'),
        anchor_cancel('/app/static_vpn_basic/')
    );
} elseif ($form_type === 'add') {
    $read_only = FALSE;
    $read_only_name = FALSE;
    $form_path = '/static_vpn_basic/add';
    $buttons = array(
        form_submit_add('submit'),
        anchor_cancel('/app/static_vpn_basic/')
    );
} else {
    $read_only = TRUE;
    $read_only_name = TRUE;
    $form_path = '/static_vpn_basic/view';
    $buttons = array(
        anchor_edit('/app/static_vpn_basic/edit/'.$name),
        anchor_delete('/app/static_vpn_basic/delete/'.$name),
        anchor_custom('/app/static_vpn_basic', lang('base_return_to_summary'))
    );
}

//ARRAYS
$leftoptions = array(
    $left => $left,
    //'%defaultroute' => lang('static_vpn_basic_defaultroute'),
    //'%any' => 'Any'
);
$leftoptions = array_merge($leftoptions, $extips);

$leftsipoptions = array(
    '' => lang('static_vpn_basic_notspecified'),
    $leftsourceip => $leftsourceip,
);
$leftsipoptions = array_merge($leftsipoptions, $lanips);

$gatewayoptions = array(
    '' => lang('static_vpn_basic_notspecified'),
    $leftnexthop => $leftnexthop
);
$gatewayoptions = array_merge($gatewayoptions, $gateways);

$policytypes = array(
    'add' => lang('static_vpn_basic_listenonly'),
    'start' => lang('static_vpn_basic_automatic'),
    //'ignore' => lang('static_vpn_basic_manualstart')
);


//strip @prefix as these are handled in controller
$leftid = trim($leftid, '@');
$rightid = trim($rightid, '@');

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

$attributes = array('autocomplete' => 'off');
echo form_open($form_path . '/' . $name, $attributes);
echo form_header(lang('base_settings'));

echo fieldset_header(lang('static_vpn_basic_general'));
echo field_input('name', $name, lang('static_vpn_basic_name'), $read_only_name);
echo field_dropdown('auto', $policytypes, $auto, lang('static_vpn_basic_policytype'), $read_only);

echo fieldset_header(lang('static_vpn_basic_local'));
//echo field_input('left', $left, lang('static_vpn_basic_left'), $read_only);
echo field_dropdown('left', $leftoptions, $left, lang('static_vpn_basic_left'), $read_only);
echo field_input('leftsubnet', $leftsubnet, lang('static_vpn_basic_leftsubnet'), $read_only);
//echo field_input('leftnexthop', $leftnexthop, lang('static_vpn_basic_leftnexthop'), $read_only);
echo field_dropdown('leftnexthop', $gatewayoptions, $leftnexthop, lang('static_vpn_basic_leftnexthop'), $read_only);
//echo field_input('leftsourceip', $leftsourceip, lang('static_vpn_basic_leftsourceip'), $read_only);
echo field_dropdown('leftsourceip', $leftsipoptions, $leftsourceip, lang('static_vpn_basic_leftsourceip'), $read_only);

echo fieldset_header(lang('static_vpn_basic_remote'));
echo field_input('right', $right, lang('static_vpn_basic_right'), $read_only);
echo field_input('rightsubnet', $rightsubnet, lang('static_vpn_basic_rightsubnet'), $read_only);
echo field_input('rightnexthop', $rightnexthop, lang('static_vpn_basic_rightnexthop'), $read_only);
echo field_input('rightsourceip', $rightsourceip, lang('static_vpn_basic_rightsourceip'), $read_only);

echo fieldset_header(lang('static_vpn_basic_ike_psk'));
//show password field only when editing
if (!$read_only) {
    echo field_password('psk', $psk, lang('static_vpn_basic_password'), $read_only);
} else {
    echo field_input('psk', lang('static_vpn_basic_hidden'), lang('static_vpn_basic_password'), $read_only);
}

echo field_button_set($buttons);

echo form_footer();
echo form_close();

