<?php

/**
 * Openswan class.
 *
 * @category   apps
 * @package    static-vpn-basic
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/static_vpn_basic/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\static_vpn_basic;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('static_vpn_basic');
clearos_load_language('base');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\Configuration_File as Configuration_File;
use \clearos\apps\base\Daemon as Daemon;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\base\Shell as Shell;
use \clearos\apps\network\Network_Utils as Network_Utils;


clearos_load_library('base/Configuration_File');
clearos_load_library('base/Daemon');
clearos_load_library('base/File');
clearos_load_library('base/Folder');
clearos_load_library('base/Shell');
clearos_load_library('network/Network_Utils');

// Exceptions
//-----------

use \clearos\apps\base\File_Not_Found_Exception as File_Not_Found_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;
use \clearos\apps\base\Engine_Exception as Engine_Exception;

clearos_load_library('base/File_Not_Found_Exception');
clearos_load_library('base/Validation_Exception');
clearos_load_library('base/Engine_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Openswan class.
 *
 * @category   apps
 * @package    static-vpn-basic
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/static_vpn_basic/
 */

class Openswan extends Daemon
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const DIR_IPSEC = '/etc/ipsec.d';
    const FILE_IPSECLOG = '/var/log/ipsec';
    const CMD_IPSEC = '/usr/sbin/ipsec';
    const CMD_IP = '/sbin/ip';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $is_loaded = FALSE;
    protected $config = array();

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Openswan constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);

        parent::__construct('ipsec');
    }

    /**
     * Return configuration summary
     *
     * @return array config
     */
    public function get_tunnels()
    {
        clearos_profile(__METHOD__, __LINE__);

        $folder = new Folder(self::DIR_IPSEC, TRUE);

        try {
            $listing = $folder->get_listing();
          
            $name = array();

            foreach ($listing as $file) {
                if (preg_match("/ipsec\.managed\./", $file)) {
                    continue;
                } else if (preg_match("/ipsec.empty/", $file)) {
                    continue;
                } else if (preg_match("/ipsec\.unmanaged\.(.*)\.conf/", $file, $name)) {
                    $cfg = new File(self::DIR_IPSEC . "/ipsec.unmanaged.$name[1].conf", TRUE);
                    $detail['name'] = $name[1];
                    $detail['left'] = $cfg->lookup_value("/\s*left=\s*/");
                    $detail['right'] = $cfg->lookup_value("/\s*right=\s*/");
                    $detail['auto'] = $cfg->lookup_value("/\s*auto=\s*/");
                    $details[] = $detail;
                }
            }
        } catch (Engine_Exception $e) {
            // Ignore
        }
            
        return $details;
       
    }


    /**
     * Get all gateways.
     *
     * On multi-WAN systems, you can have more than one default route.
     * This method returns a hash array of interfaces keyed on gateway IP addresses. 
     * Modified from Routes.php API to return all existing gateways, not just default
     *
     * @return  array  gateway route information
     * @throws Engine_Exception
     */

    public function get_gateways()
    {
        clearos_profile(__METHOD__, __LINE__);

        $routeinfo = array();
        $shell = new Shell();

        // Try multi-WAN tables first
        //--------------------------

        $shell->execute(self::CMD_IP, 'route show table 250');
        $output = $shell->get_output();

        if (! empty($output)) {
            foreach ($output as $line) {
                if (preg_match('/^\s*nexthop/', $line)) {
                    $line = preg_replace('/\s+/', ' ', $line);
                    $parts = explode(' ', trim($line));
                    if ($parts[5]) {
                        $routeinfo[$parts[2]] = $parts[4];
                    }
                }
            }
        }

        // Fallback to single WAN route table
        //----------------------------------

        $shell->execute(self::CMD_IP, 'route');
        $output = $shell->get_output();

        if (! empty($output)) {
            foreach ($output as $line) {
                $matches = array();
                if (preg_match('/^default\s+via\s+([0-9\.]*).*dev\s+([\w]*)/', $line, $matches)) {
                    $routeinfo[$matches[1]] = $matches[2];
                } elseif (preg_match('/^([0-9\.]*)\s+via\s+([0-9\.]*).*dev\s+([\w+]*)/', $line, $matches)) {
                    $routeinfo[$matches[2]] = $matches[3];
                }
            }
        }

        return $routeinfo;
    }



    /**
     * Returns preshared key configuration array.
     *
     * @param string $name tunnel name
     *
     * @return array config
     *
     * @throws Engine_Exception
     */

    public function get_config($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config($name);

        $this->psk = $this->_load_psk($name);
        $this->config['psk'] = $this->psk['psk'];
        $this->config['leftpskid'] = $this->psk['leftpskid'];
        $this->config['rightpskid'] = $this->psk['rightpskid'];

        return $this->config;
    }


    /**
    * Sets configuration
     *
     * @param string $name   tunnel name
     * @param array  $config tunnel configuration array
     *
     * @return none
     * @throws Engine_Exception
     */

    public function set_config($name,$config)
    {
        clearos_profile(__METHOD__, __LINE__);

        foreach ($config as $id => $value) {
            //skip on password fields as in their own conf.secrets
            if ($id == "psk") {
                $this->_set_psk($name, $config['leftpskid'], $config['rightpskid'], $value);
            } else {
                if($id == 'leftpskid')
                    continue;
                if($id == 'rightpskid')
                    continue;
                $this->_set_parameter($name, $id, $value);
            }
        }
    }


    /**
     * Deletes configuration
     *
     * @param string $name tunnel name
     *
     * @return none
     * @throws Engine_Exception
     */

    public function delete_entry($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $options = array();
            $options['env'] = 'LANG=en_US';
            $shell = new Shell();
            $args = 'auto --delete ' . $name;
            $shell->Execute(
                self::CMD_IPSEC, $args, TRUE, $options
            );
            $output = $shell->get_output();
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e);
        }
        //delete and tidy up config files even if tunnel deletion fails
        $this->_delete_config($name);

        return $output;
        
    }

    /**
     * Reload tunnel configuration based on auto config
     *
     * @param string $name tunnel name
     * @param boolean $manual bring up manual connection defaults true
     *
     * @return none
     * @throws Engine_Exception
     */

    public function reload($name, $manual = TRUE)
    {
        clearos_profile(__METHOD__, __LINE__);

        $data = $this->get_config($name);
        if ($data['auto'] == 'add'){
            $reload = TRUE;
            $bringup = FALSE;
        }
        if ($data['auto'] == 'start'){
            $reload = TRUE;
            $bringup = FALSE;
        }
        if ($data['auto'] == 'ignore'){
            $reload = FALSE;
            $bringup = FALSE;
        }
        if ($manual) {
            $reload = TRUE;
            $bringup = TRUE;
            if ($data['auto'] == 'add')
                $bringup = FALSE;
            if ($data['right'] == '%any')
                $bringup = FALSE;
        }
        //full reload is rereadsecrets, replace, up
        try {
            $options = array();
            $options['env'] = 'LANG=en_US';

            $shell = new Shell();

            //reload secrets first for all conns
            $argssecret = 'auto --rereadsecrets';
            $shell->Execute(
                self::CMD_IPSEC, $argssecret, TRUE, $options
            );

            if ($reload) {
                //replace -will tear down tunnel and delete first, and then add to pluto
                $argsdown = 'auto --replace ' . $name;
                $shell->Execute(
                    self::CMD_IPSEC, $argsdown, TRUE, $options
                );
            } else {
                //delete only for auto ignore setting
                $argsdown = 'auto --delete ' . $name;
                $shell->Execute(
                    self::CMD_IPSEC, $argsdown, TRUE, $options
                );
            }

            //bring up the tunnel again only for automatic connections
            if ($bringup) {
                $argsup = 'auto --up ' . $name;
                $options['validate_exit_code'] = FALSE;
                $options['background'] = TRUE;
                $shell->Execute(
                    self::CMD_IPSEC, $argsup, TRUE, $options
                );
            }
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e);
        }

    }


    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////


    /**
     * Validation routine for name.
     *
     * @param string  $entry        tunnel name
     * @param boolean $check_exists set to TRUE to check for pre-existing tunnel entry
     *
     * @return string error message if tunnel name already exists
     */

    public function validate_name($entry, $check_exists = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($check_exists) {
            $tunnels = $this->get_tunnels();

            foreach ($tunnels as $tunnel) {
                if ($tunnel['name'] == $entry)
                    return lang('network_entry_already_exists');
            }
        }      
        //check for spaces in name
        if (preg_match("/\\s/", $entry))
            return lang('static_vpn_nospaces');
    }

    /**
     * Validation routine for left entry.
     *
     * @param string $entry IP address or %defaulroute, or %any
     *
     * @return string error message if left input is invalid
     */

    public function validate_left($entry)
    {
        clearos_profile(__METHOD__, __LINE__);
        
        $countdots = substr_count($entry, '.');

        if (! Network_Utils::is_valid_ip($entry)) {
            //work around to pick up malformed IP addresses incorrectly identified as FQDN
            if (Network_Utils::is_valid_domain($entry) && $countdots<3 && $countdots>=1) {
                //OK
            } elseif ( preg_match('/faultroute$/', $entry) || preg_match('/^\%any$/', $entry) ) {
                //hack to work around browser encoding of %de, also OK
            } else {
                return lang('static_vpn_validate_left');
            }
        } else {
            if (! Network_Utils::is_valid_local_ip($entry))
                return lang('static_vpn_invalid_local_ip');
        }
    }

    /**
     * Validation routine for right entry.
     *
     * @param string $entry IP address or %any
     *
     * @return string error message if right input is invalid
     */

    public function validate_right($entry)
    {
        clearos_profile(__METHOD__, __LINE__);

        $countdots = substr_count($entry, '.');

        if (! Network_Utils::is_valid_ip($entry)) {
            //work around to pick up malformed IP addresses incorrectly identified as FQDN
            if (Network_Utils::is_valid_domain($entry) && $countdots<3 && $countdots>=1) {
                //OK
            } elseif ( preg_match('/^\%any$/', $entry) ) {
                //also OK for right
            } else {
                return lang('static_vpn_validate_right');
            }
        }

    }

    /**
     * Validation routine for left source IP entry.
     *
     * @param string $entry IP address 
     *
     * @return string error message if left source IP input is invalid
     */

    public function validate_left_source_ip($entry)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! Network_Utils::is_valid_ip($entry)) 
            return lang('network_ip_invalid');
        if (! Network_Utils::is_private_ip($entry))
            return lang('network_ip_invalid');
        if (! Network_Utils::is_valid_local_ip($entry))
            return lang('static_vpn_invalid_local_ip');


    }

    /**
     * Validation routine for right source IP entry.
     *
     * @param string $entry IP address
     *
     * @return string error message if right source IP input is invalid
     */

    public function validate_right_source_ip($entry)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! Network_Utils::is_valid_ip($entry))
            return lang('network_ip_invalid');
        if (! Network_Utils::is_private_ip($entry))
            return lang('network_ip_invalid');

    }

    /**
     * Validation routine for IP.
     *
     * @param string $entry IP address or %defaulroute, or %any or %opportunistic
     *
     * @return string error message if left input is invalid
     */

    public function validate_ip($entry)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! Network_Utils::is_valid_ip($entry))
            return lang('network_ip_invalid');
    }


    /**
     * Validation routine for Local subnet.
     *
     * @param string $entry IP address
     *
     * @return string error message if left input is invalid
     */

    public function validate_subnet($entry)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! Network_Utils::is_valid_network($entry))
            return lang('network_ip_range_invalid');
        $pieces = explode('/', $entry);
        $ipsegment = $pieces[0];
        $subnet = $pieces[1];
        if (! Network_Utils::is_private_ip($ipsegment))
            return lang('static_vpn_not_private_subnet');
    }


    /**
     * Validation routine for ID
     *
     * @param string $entry IP address
     *
     * @return string error message if left input is invalid
     */

    public function validate_id($entry)
    {
        clearos_profile(__METHOD__, __LINE__);
        // not much to do here now, @prefix handled by controller
        //check for spaces in name
        if (preg_match("/\\s/", $entry))
            return lang('static_vpn_nospaces');

    }

    /**
     * Validation routine for FQDN
     *
     * @param string $entry domain
     *
     * @return string error message if left input is invalid
     */

    public function validate_domain($entry)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (! Network_Utils::is_valid_domain($entry)) 
            return lang('static_vpn_domainfield');
    }


    /**
     * Validation routine for password.
     *
     * @param string $entry password
     *
     * @return string error message if password is invalid
     */

    public function validate_psk($entry)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (strlen($entry) < 8)
            return lang('static_vpn_weak_password');
        /* not used...
        $r1 = '/[A-Z]/';
        $r2 = '/[a-z]/';
        $r3 = '/[0-9]/';
        if (! preg_match($r1, $entry))
            return lang('static_vpn_weak_password');
        if (! preg_match($r2, $entry))
            return lang('static_vpn_weak_password');
        if (! preg_match($r3, $entry))
            return lang('static_vpn_weak_password');
        */
    }



    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E  M E T H O D S 
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Loads configuration files.
     *
     * @param string $name tunnel name
     *
     * @access private
     * @return void
     * @throws Engine_Exception
     */

    protected function _load_config($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $filename = self::DIR_IPSEC . "/ipsec.unmanaged.$name.conf";
            $file = new File($filename, TRUE);
            $lines = $file->get_contents_as_array();

            $config_file = array();
            $n = 0;
            $match = '';
            $token = '=';
            $limit = 2;

            foreach ($lines as $line) {
                $n++;

                if (preg_match('/^\/\/.*$/', $line))
                    continue;
                if (preg_match('/^\#.*$/', $line)) {
                    continue;
                } elseif (preg_match('/^\s*$/', $line)) {
                    // a blank line
                    continue;
                } else {
                    $match = array_map('trim', explode($token, $line, $limit));

                    if ($match[0] == $line) {
                        if (preg_match('/^conn.*$/', $match[0])) {
                            $match = array_map('trim', explode(' ', $line, $limit));
                        } else {
                            throw new Engine_Exception(lang('base_file_parse_error'), CLEAROS_ERROR);
                        }
                    } else {
                        if ($limit == 2) {
                            $config_file[$match[0]] = $match[1];
                        } else {
                            $config_file[$match[0]] = array_slice($match, 1);
                        }
                    }
                }
            }
            $this->config = $config_file;
            $this->loaded = TRUE;

            return $this->config;

        } catch (File_Not_Found_Exception $e) {
            // Not fatal
        }

    }


    /**
     * Deletes configuration files.
     *
     * @param string $name tunnel name
     *
     * @access private
     * @return void
     * @throws Engine_Exception
     */

    protected function _delete_config($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $config_file = self::DIR_IPSEC . "/ipsec.unmanaged.$name.conf";
            $config_psk_file = self::DIR_IPSEC . "/ipsec.unmanaged.$name.secrets";

            $file = new File($config_file, TRUE);
            $result = $file->delete();
            $pskfile = new File($config_psk_file, TRUE);
            $result = $pskfile->delete();
        } catch (File_Not_Found_Exception $e) {
            // Not fatal
        }

    }



    /**
     * Loads preshared key.
     *
     * @param string $name tunnel name
     *
     * @access private
     * @return void
     * @throws Engine_Exception
     */

    protected function _load_psk($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $values = array();
        try {
            $filename = self::DIR_IPSEC . "/ipsec.unmanaged.$name.secrets";
            $file = new File($filename, TRUE);
            $lines = $file->get_contents_as_array();
            $line = $lines[0];

            $pieces = explode(" ", $line);
            $psk['psk'] = trim($pieces[4], "'\"");  
            $psk['leftpskid'] = $pieces[0];
            $psk['rightpskid'] = $pieces[1];

        } catch (File_Not_Found_Exception $e) {
            // Not fatal but unusual...file not found error
        }

        return $psk;
    }

    /**
     * Sets a parameter in the config file.
     *
     * @param string $name  name of the tunnel
     * @param string $key   name of the key in the config file
     * @param string $value value for the key
     *
     * @access private
     * @return void
     * @throws Engine_Exception
     */

    protected function _set_parameter($name, $key, $value)
    {
        clearos_profile(__METHOD__, __LINE__);

        $filename = self::DIR_IPSEC . "/ipsec.unmanaged.$name.conf";
        $file = new File($filename, TRUE);

        if (! $file->exists())
            $file->create("root", "root", 664);

            //todo: configuration file loading doesn't appear to support elevation of rights...stick with 664 for now

        if ($value === '') {
            $file->delete_lines("/\s+$key\s*=\s*/");
        } elseif ($key == 'name') {
            //tunnel name is notated in config file
            $match1 = $file->replace_lines("/conn\s*/", "conn $name\n");
            if (!$match1)
                $file->add_lines("conn $name\n");
        } else {
            //leading space is important
            $match2 = $file->replace_lines("/\s+$key\s*=\s*/", " $key=$value\n");
            if (!$match2)
                $file->add_lines(" $key=$value\n");
        }
    }

    /**
     * Sets a password in the secrets file
     *
     * @param string $name       name of the tunnel
     * @param string $leftpskid  value of the leftip
     * @param string $rightpskid value of the rightip
     * @param string $psk        value of the pre shared key
     *
     * @access private
     * @return void
     * @throws Engine_Exception
     */

    protected function _set_psk($name, $leftpskid, $rightpskid, $psk)
    {
        clearos_profile(__METHOD__, __LINE__);


        $filename = self::DIR_IPSEC ."/ipsec.unmanaged.$name.secrets";
        $file = new File($filename, TRUE);
        
        if(! $file->exists())
            $file->create("root", "root", 600);
           
        $match = $file->replace_lines("/PSK/", "$leftpskid $rightpskid : PSK \"$psk\"\n");

        if (!$match)
            $file->add_lines("$leftpskid $rightpskid : PSK \"$psk\"\n");
    
    }
}
