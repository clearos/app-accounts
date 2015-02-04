<?php

/**
 * Accounts initialization view.
 *
 * @category   apps
 * @package    accounts
 * @subpackage views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/accounts/
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
$this->lang->load('accounts');

///////////////////////////////////////////////////////////////////////////////
// Accounts Setup
///////////////////////////////////////////////////////////////////////////////

$ad_logo = clearos_app_htdocs('accounts') . '/ad_logo.png';
$samba_logo = clearos_app_htdocs('accounts') . '/samba_logo.png';
$openldap_logo = clearos_app_htdocs('accounts') . '/openldap_logo.gif';
$google_apps_logo = clearos_app_htdocs('accounts') . '/google_apps_logo.png';

$ad_installed_action = anchor_custom('/app/active_directory', lang('accounts_configure_active_directory_connector'));
$ad_marketplace_action = anchor_custom('/app/marketplace/view/active_directory', lang('accounts_install_active_directory_connector'));
$ad_not_available = lang('accounts_active_directory_not_available_in_this_edition');

$samba_directory_installed_action = anchor_custom('/app/samba_directory', lang('accounts_configure_samba_directory'));
$samba_directory_marketplace_action = anchor_custom('/app/marketplace/view/samba_directory', lang('accounts_install_samba_directory'));
$samba_directory_not_available = lang('accounts_samba_directory_not_available_in_this_edition');

$google_apps_connector_installed_action = anchor_custom('/app/google_apps_connector', lang('accounts_configure_google_apps_connector'));
$google_apps_connector_marketplace_action = anchor_custom('/app/marketplace/view/google_apps_connector', lang('accounts_install_google_apps_connector'));
$google_apps_connector_not_available = lang('accounts_google_apps_connector_not_available_in_this_edition');

$openldap_directory_installed = anchor_custom('/app/openldap_directory', lang('accounts_configure_builtin_directory'));
$openldap_directory_driver_install = anchor_javascript('install_and_initialize_openldap', lang('accounts_install_and_initialize_builtin_directory'));
$openldap_driver_installed = anchor_javascript('initialize_openldap', lang('accounts_initialize_builtin_directory'));

$drivers = '';

$drivers .= "
<div id='openldap_directory_object'>
<table style='margin: 30px 20px 50px 20px'>
<tr>
    <td style='width: 220px'><img src='$openldap_logo' alt='OpenLDAP'><br><br></td>
    <td>
        <p>" . lang('accounts_openldap_directory_tip') . "</p>
        <div id='openldap_directory_installed'>$openldap_directory_installed</div>
        <div id='openldap_directory_driver_install'>$openldap_directory_driver_install</div>
        <div id='openldap_driver_installed'>$openldap_driver_installed</div>
    </td>
</tr>
</table>
</div>



<div id='samba_directory_object'>
<table style='margin: 30px 20px 50px 20px'>
<tr>
    <td style='width: 220px'><img src='$samba_logo' alt='Samba Directory'><br><br></td>
    <td>
        <p>" . lang('accounts_samba_directory_tip') . " <strong><span style='color:red'>BETA</span></strong></p>
        <div id='samba_directory_installed'>$samba_directory_installed_action</div>
        <div id='samba_directory_marketplace'>$samba_directory_marketplace_action</div>
        <div id='samba_directory_not_available'><i>$samba_directory_not_available</i></div>
    </td>
</tr>
</table>
</div>


<div id='ad_object'>
<table style='margin: 30px 20px 50px 20px'>
<tr>
    <td style='width: 220px'><img src='$ad_logo' alt='Active Directory'><br><br></td>
    <td>
        <p>" . lang('accounts_active_directory_connector_tip') . "</p>
        <div id='ad_installed'>$ad_installed_action</div>
        <div id='ad_marketplace'>$ad_marketplace_action</div>
        <div id='ad_not_available'><i>$ad_not_available</i></div>
    </td>
</tr>
</table>
</div>
";

/*
<div id='google_apps_connector_object'>
<table style='margin: 30px 20px 50px 20px'>
<tr>
    <td style='width: 220px'><img src='$google_apps_logo' alt='Google Apps Connector'><br><br></td>
    <td>
        <p>" . lang('google_apps_connector_tip') . " <strong><span style='color:red'>BETA</span></strong></p>
        <div id='google_apps_connector_installed'>$google_apps_connector_installed_action</div>
        <div id='google_apps_connector_marketplace'>$google_apps_connector_marketplace_action</div>
        <div id='google_apps_connector_not_available'>$google_apps_connector_not_available</div>
    </td>
</tr>
</table>
</div>
*/

///////////////////////////////////////////////////////////////////////////////
// Main
///////////////////////////////////////////////////////////////////////////////

echo "<div id='accounts_wrapper'>";
echo "<div id='accounts_configuration_widget' style='display:none;'>";
echo "<input type='hidden' id='accounts_status_lock' value='off'>\n";
echo "<input type='hidden' id='app_redirect' value='$app_redirect'>";

echo form_open('accounts/info');
echo box_open(lang('accounts_account_manager_configuration'));
echo $drivers;
echo box_close();
echo form_close();

echo "</div>";
echo "</div>";

///////////////////////////////////////////////////////////////////////////////
// Accounts Status
///////////////////////////////////////////////////////////////////////////////

echo "<div id='accounts_status_widget' style='display:none;'>";
echo infobox_highlight(lang('accounts_account_manager_status'), '<div id="accounts_status"></div>');
echo "</div>";

