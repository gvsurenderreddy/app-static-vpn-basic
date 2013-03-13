
Name: app-static-vpn-basic
Epoch: 1
Version: 1.3.8
Release: 1%{dist}
Summary: Static IPsec VPN Basic
License: GPLv3
Group: ClearOS/Apps
Packager: Tim Burgess
Vendor: Tim Burgess
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base

%description
IPsec VPN allows users to establish secure encrypted connections between networks using Openswan. The basic IPsec VPN app supports IPsec or IKE and connections in host-to-host or site-to-site configurations between ClearOS (Openswan) gateways. This app implements pre-shared key authentication for unmanaged static connections only.

%package core
Summary: Static IPsec VPN Basic - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: app-network
Requires: app-ipsec-core

%description core
IPsec VPN allows users to establish secure encrypted connections between networks using Openswan. The basic IPsec VPN app supports IPsec or IKE and connections in host-to-host or site-to-site configurations between ClearOS (Openswan) gateways. This app implements pre-shared key authentication for unmanaged static connections only.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/static_vpn_basic
cp -r * %{buildroot}/usr/clearos/apps/static_vpn_basic/

install -D -m 0644 packaging/logrotate-ipsec %{buildroot}/etc/logrotate.d/ipsec
install -D -m 0644 packaging/rsyslog-ipsec.conf %{buildroot}/etc/rsyslog.d/ipsec.conf

if [ -d %{buildroot}/usr/clearos/apps/static_vpn_basic/libraries_zendguard ]; then
    rm -rf %{buildroot}/usr/clearos/apps/static_vpn_basic/libraries
    mv %{buildroot}/usr/clearos/apps/static_vpn_basic/libraries_zendguard %{buildroot}/usr/clearos/apps/static_vpn_basic/libraries
fi

%post
logger -p local6.notice -t installer 'app-static-vpn-basic - installing'

%post core
logger -p local6.notice -t installer 'app-static-vpn-basic-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/static_vpn_basic/deploy/install ] && /usr/clearos/apps/static_vpn_basic/deploy/install
fi

[ -x /usr/clearos/apps/static_vpn_basic/deploy/upgrade ] && /usr/clearos/apps/static_vpn_basic/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-static-vpn-basic - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-static-vpn-basic-core - uninstalling'
    [ -x /usr/clearos/apps/static_vpn_basic/deploy/uninstall ] && /usr/clearos/apps/static_vpn_basic/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/static_vpn_basic/controllers
/usr/clearos/apps/static_vpn_basic/htdocs
/usr/clearos/apps/static_vpn_basic/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/static_vpn_basic/packaging
%exclude /usr/clearos/apps/static_vpn_basic/tests
%dir /usr/clearos/apps/static_vpn_basic
/usr/clearos/apps/static_vpn_basic/deploy
/usr/clearos/apps/static_vpn_basic/language
/usr/clearos/apps/static_vpn_basic/libraries
/etc/logrotate.d/ipsec
/etc/rsyslog.d/ipsec.conf
