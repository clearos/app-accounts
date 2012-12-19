
Name: app-accounts
Epoch: 1
Version: 1.4.8
Release: 1%{dist}
Summary: Account Manager
License: GPLv3
Group: ClearOS/Apps
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base
Requires: app-mode-core

%description
The Account Manager manages the underlying accounts system system, as well as provides an overview of installed plugins and extensions for users and groups.

%package core
Summary: Account Manager - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: app-events-core
Requires: app-mode-core
Requires: csplugin-filewatch

%description core
The Account Manager manages the underlying accounts system system, as well as provides an overview of installed plugins and extensions for users and groups.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/accounts
cp -r * %{buildroot}/usr/clearos/apps/accounts/

install -d -m 0755 %{buildroot}/var/clearos/accounts
install -d -m 0755 %{buildroot}/var/clearos/accounts/drivers
install -d -m 0755 %{buildroot}/var/clearos/accounts/plugins
install -d -m 0755 %{buildroot}/var/clearos/events/accounts
install -D -m 0755 packaging/accounts %{buildroot}/var/clearos/events/accounts/accounts
install -D -m 0755 packaging/accounts-init %{buildroot}/usr/sbin/accounts-init
install -D -m 0644 packaging/filewatch-accounts-event.conf %{buildroot}/etc/clearsync.d/filewatch-accounts-event.conf
install -D -m 0644 packaging/nscd.php %{buildroot}/var/clearos/base/daemon/nscd.php

%post
logger -p local6.notice -t installer 'app-accounts - installing'

%post core
logger -p local6.notice -t installer 'app-accounts-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/accounts/deploy/install ] && /usr/clearos/apps/accounts/deploy/install
fi

[ -x /usr/clearos/apps/accounts/deploy/upgrade ] && /usr/clearos/apps/accounts/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-accounts - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-accounts-core - uninstalling'
    [ -x /usr/clearos/apps/accounts/deploy/uninstall ] && /usr/clearos/apps/accounts/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/accounts/controllers
/usr/clearos/apps/accounts/htdocs
/usr/clearos/apps/accounts/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/accounts/packaging
%exclude /usr/clearos/apps/accounts/tests
%dir /usr/clearos/apps/accounts
%dir /var/clearos/accounts
%dir /var/clearos/accounts/drivers
%dir /var/clearos/accounts/plugins
%dir /var/clearos/events/accounts
/usr/clearos/apps/accounts/deploy
/usr/clearos/apps/accounts/language
/usr/clearos/apps/accounts/libraries
/var/clearos/events/accounts/accounts
/usr/sbin/accounts-init
/etc/clearsync.d/filewatch-accounts-event.conf
/var/clearos/base/daemon/nscd.php
