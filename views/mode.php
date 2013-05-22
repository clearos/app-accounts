<?php

/**
 * Accounts synchnroniation check view.
 *
 * @category   apps
 * @package    accounts
 * @subpackage views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
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
// Form handler
///////////////////////////////////////////////////////////////////////////////

$sync_logo = clearos_app_htdocs('accounts') . '/account_synchronization_50x50.png';

if ($account_synchronization_installed) {
    $blurb = lang('accounts_account_synchronization_help');
    $buttons = button_set(
        array(
            anchor_custom('/app/account_synchronization', lang('accounts_configure_account_synchronization')),
        )
    );
} else {
    $blurb = lang('accounts_account_synchronization_available_help');
    $buttons = button_set(
        array(
            anchor_custom('/app/accounts/system_mode/confirm', lang('accounts_set_standalone_mode')),
            anchor_custom('/app/marketplace/view/account_synchronization', lang('accounts_view_account_synchronization_in_marketplace'))
        )
    );
}

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////
// TODO: integrate custom layout into theme engine

echo form_open('accounts/info');
echo form_header(lang('accounts_account_synchronization'));
echo form_banner("
<table border='0' cellpadding='0' cellspacing='0' style='width: 100%'>
<tr>
    <td valign='top' align='center' width='50'><img src='$sync_logo' alt=''></td>
    <td valign='top'>
        <p>$blurb</p>
        <div align='center'>$buttons</div>
    </td>
</tr>
</table>
");
echo form_footer();
echo form_close();
