<?php
//minimum
$lang['static_vpn_basic_app_name'] = 'Static IPsec VPN Basic';
$lang['static_vpn_basic_app_description'] = 'IPsec VPN allows users to establish secure encrypted connections between networks using Openswan. The basic IPsec VPN app supports IPsec or IKE and connections in host-to-host or site-to-site configurations between ClearOS (Openswan) gateways. This app implements pre-shared key authentication for unmanaged static connections only.';
$lang['static_vpn_basic_tooltip'] = 'Open incoming firewall protcols ESP+UDP for port 500(IKE) or port 4500(NAT-T). Logs can be found at /var/log/ipsec';
//others
$lang['static_vpn_basic_tunnel_name'] = 'Name';
$lang['static_vpn_basic_ipsec_server'] = 'IPsec Server Connections';
$lang['static_vpn_basic_tunnel_local'] = 'Local';
$lang['static_vpn_basic_tunnel_remote'] = 'Remote';
$lang['static_vpn_basic_tunnel_type'] = 'Type';
//$lang['static_vpn_basic_warning'] = 'This app is for unmanaged static connections only. For connections on dynamic IP addresses see the ClearCenter Dynamic VPN app available via the Marketplace.';

//headers
$lang['static_vpn_basic_general'] = 'General Settings';
$lang['static_vpn_basic_local'] = 'Local Settings';
$lang['static_vpn_basic_remote'] = 'Remote Settings';
$lang['static_vpn_basic_ike_psk'] = 'Pre-shared Key';
$lang['static_vpn_basic_hidden'] = '************';

//fields
$lang['static_vpn_basic_name'] = 'Connection Name';
$lang['static_vpn_basic_policytype'] = 'Connection Mode';
$lang['static_vpn_basic_left'] = 'Local WAN IP';
$lang['static_vpn_basic_leftnexthop'] = 'Local Gateway IP (Optional)';
$lang['static_vpn_basic_leftsourceip'] = 'Local LAN IP (Optional)';
$lang['static_vpn_basic_leftsubnet'] = 'Local LAN Subnet (CIDR Form)';
$lang['static_vpn_basic_right'] = 'Remote WAN IP/FQDN';
$lang['static_vpn_basic_rightnexthop'] = 'Remote Gateway IP (Optional)';
$lang['static_vpn_basic_rightsourceip'] = 'Remote LAN IP (Optional)';
$lang['static_vpn_basic_rightsubnet'] = 'Remote LAN Subnet (CIDR Form)';
$lang['static_vpn_basic_password'] = 'Pre Shared Key';

//dropdowns
$lang['static_vpn_basic_listenonly'] = 'Listen';
$lang['static_vpn_basic_automatic'] = 'Automatic';
$lang['static_vpn_basic_manual'] = 'Manual';
$lang['static_vpn_basic_negotiate'] = 'Negotiate';
$lang['static_vpn_basic_notspecified'] = 'Not specified';
$lang['static_vpn_basic_localwanip'] = 'Local WAN IP';
$lang['static_vpn_basic_remotewanip'] = 'Remote WAN IP';
$lang['static_vpn_basic_manualstart'] = 'Manual Start';
$lang['static_vpn_basic_remote_peer_type'] = 'Remote Peer Type(XAUTH PSK)';
$lang['static_vpn_basic_any'] = 'Any';
$lang['static_vpn_basic_defaultroute'] = 'Default Route';

//validation
$lang['static_vpn_basic_duplicate_name'] = 'Tunnel name already exists';
$lang['static_vpn_basic_idfield'] = 'Must be string with @ prefix';
$lang['static_vpn_basic_domainfield'] = 'Invalid FQDN';
$lang['static_vpn_basic_validate_left'] = 'IP address, %%defaultroute, or %%any';
$lang['static_vpn_basic_validate_right'] = 'IP address or %%any';
$lang['static_vpn_basic_nospaces'] = 'Must not contain spaces';
$lang['static_vpn_basic_invalid_local_ip'] = 'IP address does not match any interface';
$lang['static_vpn_basic_not_private_subnet'] = 'Not a valid private subnet';
$lang['static_vpn_basic_weak_password'] = 'Must be 8 or more characters';
