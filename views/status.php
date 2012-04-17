<?php

/**
 * Accounts initialization view.
 *
 * @category   ClearOS
 * @package    Accounts
 * @subpackage Views
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
$openldap_logo = clearos_app_htdocs('accounts') . '/openldap_logo.gif';

$ad_installed_action = anchor_custom('/app/active_directory', lang('accounts_configure_active_directory_connector'));
$ad_marketplace_action = anchor_custom('/app/marketplace/view/active_directory', lang('accounts_install_active_directory_connector'));
$ad_not_available = lang('accounts_active_directory_not_available_in_this_edition');

$openldap_directory_installed = anchor_custom('/app/openldap_directory', lang('accounts_configure_builtin_directory'));
$openldap_directory_marketplace = anchor_custom('/app/marketplace/view/openldap_directory', lang('accounts_install_builtin_directory'));
$openldap_driver_installed = anchor_javascript('initialize_openldap', lang('accounts_initialize_builtin_directory'));

echo "<input type='hidden' id='accounts_status_lock' value='off'>\n";
echo "<input type='hidden' id='app_redirect' value='$app_redirect'>";

echo "<div id='accounts_configuration_widget'>";

// TODO: implement this widget in the theme
echo form_open('accounts/info');
echo infobox_highlight(lang('accounts_account_manager_configuration'), "
<table cellpading='3' cellspacing='3'>
<tr>
    <td align='center' width='250'><img src='$ad_logo' alt='Active Directory'><br><br></td>
    <td>
        <p>" . lang('accounts_active_directory_connector_tip') . "</p>
        <div id='ad_installed'>$ad_installed_action</div>
        <div id='ad_marketplace'>$ad_marketplace_action</div>
        <div id='ad_not_available'>$ad_not_available</div>
    </td>
</tr>
<tr>
    <td colspan='2'>&nbsp <hr style='color: #EEEEEE'><br></td>
</tr>
<tr>
    <td align='center'><img src='$openldap_logo' alt='OpenLDAP'><br><br></td>
    <td>
        <p>" . lang('accounts_openldap_directory_tip') . "</p>
        <div id='openldap_directory_installed'>$openldap_directory_installed</div>
        <div id='openldap_directory_marketplace'>$openldap_directory_marketplace</div>
        <div id='openldap_driver_installed'>$openldap_driver_installed</div>
    </td>
</tr>
</table>
");
echo form_close();

echo "</div>";


///////////////////////////////////////////////////////////////////////////////
// Accounts Status
///////////////////////////////////////////////////////////////////////////////

echo "<div id='accounts_status_widget'>";
echo infobox_highlight(lang('accounts_account_manager_status'), '<div id="accounts_status"></div>');
echo "</div>";

