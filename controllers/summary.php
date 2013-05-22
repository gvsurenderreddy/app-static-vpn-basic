<?php
/**
 * Static VPN Summary
 *
 * @category   apps
 * @package    static-vpn-basic
 * @subpackage controllers
 * @author     Tim Burgess <trburgess@gmail.com>
 * @copyright  2011-2012 ClearFoundation
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
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \Exception as Exception; 

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Basic Static VPN Summary Controller.
 *
 * @category   apps
 * @package    static-vpn-basic
 * @subpackage controllers
 * @author     Tim Burgess <trburgess@gmail.com>
 * @copyright  2011-2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/static_vpn_basic/
 */

class Summary extends ClearOS_Controller
{

    /**
     * Main view
     *
     * @return view
     */

    function index()
    {
        // Load libraries
        //---------------

        $this->load->library('static_vpn_basic/Openswan');
        $this->lang->load('static_vpn_basic');

        // Load data
        //-----------

        try {
            $data['tunnels'] = $this->openswan->get_tunnels();
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------
        $this->page->view_form('static_vpn_basic/summary', $data, lang('ipsec_app_name'));
    }

    /**
     * Get json tunnel data - debug only
     *
     * @return view
     */

    function get_tunnel_data()
    {
        // Load libraries
        //---------------

        $this->load->library('static_vpn_basic/Openswan');
        $this->lang->load('static_vpn');

        // Load data
        //-----------

        try {
             $data['tunnels'] = $this->openswan->get_tunnels();
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        echo json_encode($data);
    }



    /**
     * Get json config data - debug only
     *
     * @param string $name Tunnel name
     *
     * @return view
     */
    function get_config_data($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Load dependencies
        //------------------

        $this->load->library('static_vpn_basic/Openswan');

        // Load data
        //----------
        try {
            $data = $this->openswan->get_config($name);
        } catch (Exception $e) {
            echo json_encode(array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }

        echo json_encode($data);
    }

}
?>
