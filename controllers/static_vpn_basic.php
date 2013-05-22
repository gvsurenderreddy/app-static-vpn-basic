<?php

/**
 * Static VPN Basic
 *
 * @category   apps
 * @package    static-vpn-basic
 * @subpackage controllers
 * @author     Tim Burgess <trburgess@gmail.com>
 * @copyright  2011-2012 ClearFoundation
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
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \Exception as Exception;
use \clearos\apps\network\Iface as IfaceAPI;
use \clearos\apps\network\Role as Role;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Static VPN Baisc Controller.
 *
 * @category   apps
 * @package    static-vpn-basic
 * @subpackage controllers
 * @author     Tim Burgess <trburgess@gmail.com>
 * @copyright  2011-2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/static_vpn_basic_basic/
 */ 

class Static_Vpn_Basic extends ClearOS_Controller
{

    /**
     * Main view.
     *
     * @return view
     */

    function index()
    {
        // Load libraries
        //---------------
        $this->lang->load('static_vpn_basic');

        // Load views
        //-----------
        //add static_vpn_basic/network for port check (6.4 only)
        $views = array('static_vpn_basic/server', 'static_vpn_basic/summary');

        $this->page->view_forms($views, lang('static_vpn_basic_app_name'));
    }

    /**
     * Edit view.
     *
     * @param string $name Tunnel name
     *
     * @return view
     */

    function edit($name)
    {
        $this->_common($name, 'edit');
    }

    /**
     * Add view.
     *
     * @param string $name null
     *
     * @return view
     */

    function add($name = NULL)
    {
        $this->_common($name, 'add');
    }

    /**
     * View view.
     *
     * @param string $name Tunnel name
     *
     * @return view
     */

    function view($name)
    {
        $this->_common($name, 'view');
    }

    /**
     * Delete static_vpn_basic entry view.
     *
     * @param string $name Tunnel name
     *
     * @return view
     */

    function delete($name)
    {
        $confirm_uri = '/app/static_vpn_basic/destroy/' . $name;
        $cancel_uri = '/app/static_vpn_basic';
        $items = array($name);

        $this->page->view_confirm_delete($confirm_uri, $cancel_uri, $items);
    }

    /**
     * Destroys static_vpn_basic entry view.
     *
     * @param string $name Tunnel name
     *
     * @return view
     */

    function destroy($name = NULL)
    {
        // Load libraries
        //---------------
        $this->load->library('static_vpn_basic/Openswan');

        // Handle delete
        //--------------

        try {
            $this->openswan->delete_entry($name);
            $this->page->set_status_deleted();
            redirect('/static_vpn_basic');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }

    /**
     * Reload tunnel - down/up and rereadsecrets
     *
     * @param string $name Tunnel name
     *
     * @return view
     */

    function reload($name)
    {
        // Load libraries
        //---------------
        $this->load->library('static_vpn_basic/Openswan');

        // Handle delete
        //--------------

        try {
            $this->openswan->reload($name);
            $this->page->set_status_updated();
            redirect('/static_vpn_basic');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Common view/edit handler.
     *
     * @param string $name      tunnel name
     * @param string $form_type form type
     *
     * @return view
     */

    function _common($name, $form_type)
    {
        // Load dependencies
        //------------------

        $this->lang->load('static_vpn_basic');
        $this->load->library('static_vpn_basic/Openswan');
        $this->load->library('network/Iface_Manager');

        // Set validation rules
        //---------------------
        $checkname = ($form_type === 'add') ? TRUE : FALSE;

        $this->form_validation->set_policy('name', 'static_vpn_basic/Openswan', 'validate_name', TRUE, $checkname);
        $this->form_validation->set_policy('auto');
        $this->form_validation->set_policy('left', 'static_vpn_basic/Openswan', 'validate_left', TRUE);
        $this->form_validation->set_policy('leftnexthop', 'static_vpn_basic/Openswan', 'validate_ip');
        $this->form_validation->set_policy('leftsourceip', 'static_vpn_basic/Openswan', 'validate_left_source_ip');
        $this->form_validation->set_policy('leftsubnet', 'static_vpn_basic/Openswan', 'validate_subnet');

        $this->form_validation->set_policy('right', 'static_vpn_basic/Openswan', 'validate_right', TRUE);
        $this->form_validation->set_policy('rightnexthop', 'static_vpn_basic/Openswan', 'validate_ip');
        $this->form_validation->set_policy('rightsourceip', 'static_vpn_basic/Openswan', 'validate_right_source_ip');
        $this->form_validation->set_policy('rightsubnet', 'static_vpn_basic/Openswan', 'validate_subnet');

        $this->form_validation->set_policy('psk', 'static_vpn_basic/Openswan', 'validate_psk', TRUE);

        $form_ok = $this->form_validation->run();        

        //debug only
        //$form_ok = TRUE;

        // Handle form submit
        //-------------------

        if ($this->input->post('submit') && ($form_ok === TRUE)) {
            try {
                $data['name'] = $this->input->post('name');
                $data['type'] = 'tunnel';
                $data['authby'] = 'secret';
                $data['auto'] = $this->input->post('auto');
                //Force tunnel auto to listen if combined with right=%any
                if ($data['auto'] == 'start' && preg_match('/%any/', $this->input->post('right'))) {
                    $data['auto'] = 'add';
                } 
                //HACK - permit %defaultroute - prevent conversion of %de to specialchars
                if (preg_match('/faultroute/', $this->input->post('left'))) {
                    $leftstring = '%defaultroute';
                } else {
                    $leftstring = $this->input->post('left');
                }
                $data['left'] = $leftstring;
                $data['leftnexthop'] = $this->input->post('leftnexthop');
                $data['leftsourceip'] = $this->input->post('leftsourceip');
                $data['leftsubnet'] = $this->input->post('leftsubnet');

                $data['right'] = $this->input->post('right');
                $data['rightnexthop'] = $this->input->post('rightnexthop');
                $data['rightsourceip'] = $this->input->post('rightsourceip');
                $data['rightsubnet'] = $this->input->post('rightsubnet');

                $data['psk'] = $this->input->post('psk');
                $data['leftpskid'] = $data['left'];
                $data['rightpskid'] = $data['right'];

                //finally pass tunnel name
                $name = $data['name'];

                $this->openswan->set_config($name, $data);
                //reload but only bring up auto add conns, webconfig can bring up auto ignore conns
                $this->openswan->reload($name, FALSE);
                $this->page->set_status_updated();

                //for now redirect back to config view instead of summary - debug
                redirect('/static_vpn_basic/view/'.$name);
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }


        // Load view data
        //---------------

        try {
            $data = $this->openswan->get_config($name);
            $ethlist = $this->iface_manager->get_interface_details();
            $gateways = $this->openswan->get_gateways();

            foreach ($ethlist as $eth => $info) {
                if ($info['role'] == Role::ROLE_EXTERNAL)
                    $data['extips'][$info['address']] = $info['address'] .' - '.$eth;
                if ($info['role'] == Role::ROLE_LAN || $info['role'] == Role::ROLE_HOT_LAN)
                    $data['lanips'][$info['address']] = $info['address'] .' - '.$eth;
            }
            foreach ($gateways as $ip => $eth) {
                $data['gateways'][$ip] = $ip .' - '. $eth;
            }

            $data['form_type'] = $form_type;
            $data['name'] = $name;
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        $this->page->view_form('static_vpn_basic/add_edit', $data, lang('base_settings'));
    }


}
?>
