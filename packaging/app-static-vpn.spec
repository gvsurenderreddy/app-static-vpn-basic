
Name: app-static-vpn
Epoch: 1
Version: 1.3.4
Release: 1%{dist}
Summary: Static IPsec VPN
License: GPLv3
Group: ClearOS/Apps
Packager: Tim Burgess
Vendor: Tim Burgess
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base

%description
IPsec VPN allows users to establish secure encrypted connections between networks using Openswan. Openswan supports IPsec or IKE and connections from third party devices in host-to-host or site-to-site configurations. This app implements pre-shared key authentication for unmanaged static connections only.

%package core
Summary: Static IPsec VPN - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: app-network
Requires: openswan

%description core
IPsec VPN allows users to establish secure encrypted connections between networks using Openswan. Openswan supports IPsec or IKE and connections from third party devices in host-to-host or site-to-site configurations. This app implements pre-shared key authentication for unmanaged static connections only.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/static_vpn
cp -r * %{buildroot}/usr/clearos/apps/static_vpn/

install -D -m 0644 packaging/ipsec.php %{buildroot}/var/clearos/base/daemon/ipsec.php
install -D -m 0644 packaging/logrotate-ipsec %{buildroot}/etc/logrotate.d/ipsec
install -D -m 0644 packaging/rsyslog-ipsec.conf %{buildroot}/etc/rsyslog.d/ipsec.conf

if [ -d %{buildroot}/usr/clearos/apps/static_vpn/libraries_zendguard ]; then
    rm -rf %{buildroot}/usr/clearos/apps/static_vpn/libraries
    mv %{buildroot}/usr/clearos/apps/static_vpn/libraries_zendguard %{buildroot}/usr/clearos/apps/static_vpn/libraries
fi

%post
logger -p local6.notice -t installer 'app-static-vpn - installing'

%post core
logger -p local6.notice -t installer 'app-static-vpn-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/static_vpn/deploy/install ] && /usr/clearos/apps/static_vpn/deploy/install
fi

[ -x /usr/clearos/apps/static_vpn/deploy/upgrade ] && /usr/clearos/apps/static_vpn/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-static-vpn - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-static-vpn-core - uninstalling'
    [ -x /usr/clearos/apps/static_vpn/deploy/uninstall ] && /usr/clearos/apps/static_vpn/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/static_vpn/controllers
/usr/clearos/apps/static_vpn/htdocs
/usr/clearos/apps/static_vpn/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/static_vpn/packaging
%exclude /usr/clearos/apps/static_vpn/tests
%dir /usr/clearos/apps/static_vpn
/usr/clearos/apps/static_vpn/deploy
/usr/clearos/apps/static_vpn/language
/usr/clearos/apps/static_vpn/libraries
/var/clearos/base/daemon/ipsec.php
%config /etc/logrotate.d/ipsec
%config /etc/rsyslog.d/ipsec.conf
