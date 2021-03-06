<?php

/**
 * Accounts ajax helper.
 *
 * @category   apps
 * @package    accounts
 * @subpackage javascript
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011-2014 ClearFoundation
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
clearos_load_language('base');

///////////////////////////////////////////////////////////////////////////////
// J A V A S C R I P T
///////////////////////////////////////////////////////////////////////////////

header('Content-Type:application/x-javascript');
?>

$(document).ready(function() {
    lang_initializing = '<?php echo lang("base_initializing..."); ?>';

    $("#accounts_wrapper").show();
    $("#accounts_configuration_widget").hide();
    $("#accounts_status_widget").hide();

    $("#initialize_openldap").click(function(){
        initializeDirectory('openldap');
    });

    $("#install_and_initialize_openldap").click(function(){
        initializeDirectory('openldap');
    });

    $("#install_and_configure_samba_directory").click(function(){
        initializeDirectory('samba_directory');
    });

    $("#install_and_configure_ad_connector").click(function(){
        initializeDirectory('ad');
    });


    getAccountsInfo();
});

function initializeDirectory(directory) {
    var loading_options = Array();
    loading_options.text = lang_initializing;

    $("#accounts_status").html(theme_loading(loading_options));
    $("#accounts_configuration_widget").hide();
    $("#accounts_status_widget").show();
    $('#accounts_driver').val(directory);

    $.ajax({
        url: '/app/accounts/bootstrap/index/' + directory,
        method: 'GET',
        dataType: 'json',
        success : function(payload) {
            $("#accounts_wrapper").show();
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            $("#accounts_wrapper").show();
        }
    });
}

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
    // An extra step is added to catch the transition between the
    // LDAP initialization and SambaLDAP initialization (if any)

    var accounts_status_lock = $('#accounts_status_lock').val();
    var accounts_driver = $('#accounts_driver').val();

    if (payload.status == 'install_failed') {
        $("#accounts_status").html('<div>' + payload.status_message + '</div>');
    } else {
        var loading_options = Array();
        loading_options.text = payload.status_message;
        $("#accounts_status").html(theme_loading(loading_options));
    }

    // Show / hide forms depending on state
    //-------------------------------------

    if (payload.status == 'uninitialized') {
        $("#accounts_configuration_widget").show();
        $("#accounts_status_widget").hide();
    } else if (payload.status == 'install_failed') {
        $("#accounts_status_widget").show();
        $("#accounts_configuration_widget").show();
    } else if (payload.status == 'ready_to_configure') {
        console.log('ready to configure dude: ' + accounts_driver);
        if (accounts_driver == 'samba_directory')
            window.location.href = '/app/samba_directory';
        else if (accounts_driver == 'ad')
            window.location.href = '/app/active_directory';
    } else if (payload.status == 'online') {
        if (accounts_status_lock == 'step0') {
            $('#accounts_status_lock').val('on');
        } else if (accounts_status_lock == 'on') {
            var app_redirect = $('#app_redirect').val();
            var redirect = (app_redirect) ? '/app/' + app_redirect : '/app/accounts';

            // add /index since the browser won't redirect to itself (bug?)
            if (redirect == '/app/accounts')
                redirect = '/app/accounts/index';

            window.location.href = redirect;
        } else {
            $("#accounts_status_widget").hide();
            $("#accounts_configuration_widget").hide();
        }
    } else {
        $('#accounts_status_lock').val('step0');
        $("#accounts_status_widget").show();
        $("#accounts_configuration_widget").hide();
    }

    // Account Configuration
    //----------------------

    if (payload.openldap_directory_installed) {
        $("#openldap_directory_configure").show();
        $("#openldap_directory_install").hide();
        $("#openldap_driver_initialize").hide();
    } else if (payload.openldap_driver_installed) {
        $("#openldap_directory_configure").hide();
        $("#openldap_directory_install").hide();
        $("#openldap_driver_initialize").show();
    } else if (payload.marketplace_installed) {
        $("#openldap_directory_configure").hide();
        $("#openldap_directory_install").show();
        $("#openldap_driver_initialize").hide();
    } else {
        $("#openldap_directory_object").hide();
    }

    if (payload.samba_directory_not_available) {
        $("#samba_directory_configure").hide();
        $("#samba_directory_install").hide();
        $("#samba_directory_not_available").show();
    } else if (payload.samba_directory_installed) {
        $("#samba_directory_configure").show();
        $("#samba_directory_install").hide();
        $("#samba_directory_not_available").hide();
    } else if (payload.marketplace_installed) {
        $("#samba_directory_configure").hide();
        $("#samba_directory_install").show();
        $("#samba_directory_not_available").hide();
    } else {
        $("#samba_directory_object").hide();
    }

    if (payload.ad_not_available) {
        $("#ad_configure").hide();
        $("#ad_install").hide();
        $("#ad_not_available").show();
    } else if (payload.ad_installed) {
        $("#ad_configure").show();
        $("#ad_install").hide();
        $("#ad_not_available").hide();
    } else if (payload.marketplace_installed) {
        $("#ad_configure").hide();
        $("#ad_install").show();
        $("#ad_not_available").hide();
    } else {
        $("#ad_object").hide();
    }

    // Account widgets
    //----------------

    if (payload.devel) {
        // Show all in devel mode
    } else if (payload.samba_directory_installed) {
        $("#openldap_directory_object").hide();
        $("#ad_object").hide();
    } else if (payload.openldap_directory_installed) {
        $("#samba_directory_object").hide();
        $("#ad_object").hide();
    } else if (payload.ad_installed) {
        $("#samba_directory_object").hide();
        $("#openldap_directory_object").hide();
    }
}

// vim: ts=4 syntax=javascript
