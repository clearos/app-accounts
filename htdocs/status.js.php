<?php

/**
 * Accounts ajax helper.
 *
 * @category   Apps
 * @package    Accounts
 * @subpackage Ajax
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
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('accounts');

///////////////////////////////////////////////////////////////////////////////
// J A V A S C R I P T
///////////////////////////////////////////////////////////////////////////////

header('Content-Type:application/x-javascript');
?>

$(document).ready(function() {
    $("#accounts_configuration_widget").hide();
    $("#accounts_status_widget").hide();

    $("#initialize_openldap").click(function(){
        $("#accounts_status").html('<div class="theme-loading-normal"></div>');
        $("#accounts_configuration_widget").hide();
        $("#accounts_status_widget").show();

        $.ajax({
            url: '/app/accounts/bootstrap/index',
            method: 'GET',
            dataType: 'json',
            success : function(payload) {
                var app_redirect = $('#app_redirect').val();
                var redirect = (app_redirect) ? '/app/' + app_redirect : '/app/accounts';
                window.location.href = redirect;
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
            }

        });
    });

    getAccountsInfo();
});

function getAccountsInfo() {
    $.ajax({
        url: '/app/accounts/status/get_info',
        method: 'GET',
        dataType: 'json',
        success : function(payload) {
            window.setTimeout(getAccountsInfo, 2000);
            showAccountsInfo(payload);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            window.setTimeout(getAccountsInfo, 2000);
        }
    });
}

function showAccountsInfo(payload) {

    // When the status goes to "offline", the status lock is set.  When
    // status returns back to online, it will reload the page.

    var accounts_status_lock = $('#accounts_status_lock').val();

    // Add whirly when initializing
    //-----------------------------

    if (payload.status == 'initializing')
        $("#accounts_status").html('<div class="theme-loading-normal">' + payload.status_message + '</div>');
    else
        $("#accounts_status").html(payload.status_message);

    // Show / hide forms depending on state
    //-------------------------------------

    if (payload.status == 'uninitialized') {
        $("#accounts_status_widget").hide();
        $("#accounts_configuration_widget").show();
    } else if (payload.status == 'online') {
        if (accounts_status_lock == 'on') {
            var app_redirect = $('#app_redirect').val();
            var redirect = (app_redirect) ? '/app/' + app_redirect : '/app/accounts';
            window.location.href = redirect;
        } else {
            $("#accounts_status_widget").hide();
            $("#accounts_configuration_widget").hide();
        }
    } else {
        $('#accounts_status_lock').val('on');
        $("#accounts_status_widget").show();
        $("#accounts_configuration_widget").hide();
    }

    // Account Configuration
    //----------------------

    if (payload.ad_not_available) {
        $("#ad_installed").hide();
        $("#ad_marketplace").hide();
        $("#ad_not_available").show();
    } else if (payload.ad_installed) {
        $("#ad_installed").show();
        $("#ad_marketplace").hide();
        $("#ad_not_available").hide();
    } else if (payload.marketplace_installed) {
        $("#ad_installed").hide();
        $("#ad_marketplace").show();
        $("#ad_not_available").hide();
    } else {
        $("#ad_installed").hide();
        $("#ad_marketplace").hide();
        $("#ad_not_available").hide();
    }

    if (payload.openldap_directory_installed) {
        $("#openldap_directory_installed").show();
        $("#openldap_directory_marketplace").hide();
        $("#openldap_driver_installed").hide();
    } else if (payload.openldap_driver_installed) {
        $("#openldap_directory_installed").hide();
        $("#openldap_directory_marketplace").hide();
        $("#openldap_driver_installed").show();
    } else {
        $("#openldap_directory_installed").hide();
        $("#openldap_directory_marketplace").show();
        $("#openldap_driver_installed").hide();
    }
}

// vim: ts=4 syntax=javascript
