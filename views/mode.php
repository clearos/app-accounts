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

if ($master_slave_synchronization_installed) {
    $blurb = lang('accounts_master_slave_synchronization_help');
    $buttons = array(
        anchor_custom('/app/master_slave', lang('accounts_configure_master_slave_synchronization')),
    );
} else {
    $blurb = lang('accounts_master_slave_synchronization_available_help');
    $buttons = array(
        anchor_custom('/app/accounts/system_mode/confirm', lang('accounts_set_standalone_mode')),
        anchor_custom('/app/marketplace/view/master_slave', lang('accounts_view_master_slave_synchronization_in_marketplace'))
    );
}

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

$options['buttons']  = $buttons;

echo infobox_highlight(
    lang('accounts_master_slave_synchronization'),
    $blurb,
    $options
);
