<?php

/**
 * Accounts cache javascript helper.
 *
 * @category   apps
 * @package    accounts
 * @subpackage javascript
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
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// J A V A S C R I P T
///////////////////////////////////////////////////////////////////////////////

header('Content-Type:application/x-javascript');
?>

$(document).ready(function() {
	$("#reload_users_cache").click(function(){
        reloadCache('users');
	});

	$("#reload_groups_cache").click(function(){
        reloadCache('groups');
	});

    if ($("#load_cache").val() == 1) {
        loadCache();
    }
});

function loadCache() {
    $.ajax({
        url: '/app/accounts/cache/load',
        method: 'GET',
        dataType: 'json',
        success : function(payload) {
            window.location = '/app/' + $('#app_name').val();
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            // loadCache();
        }
    });
}

function reloadCache(app) {
    $.ajax({
        url: '/app/accounts/cache/reset',
        method: 'GET',
        dataType: 'json',
        success : function(payload) {
            window.location = '/app/' + app;
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
        }
    });
}

// vim: ts=4 syntax=javascript
