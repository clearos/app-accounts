<?php

/**
 * Accounts initialization check.
 *
 * @category   Apps
 * @package    Accounts
 * @subpackage Controllers
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
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \clearos\apps\accounts\Accounts_Driver_Not_Set_Exception as Accounts_Driver_Not_Set_Exception;
use \clearos\apps\accounts\Accounts_Engine as Accounts_Engine;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Accounts initialization check.
 *
 * @category   Apps
 * @package    Accounts
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/accounts/
 */

class Status extends ClearOS_Controller
{
    /**
     * Default controller.
     *
     * @return view
     */

    function index()
    {
        if ($this->unhappy())
            $this->widget();
    }

    /**
     * Returns state of account system
     *
     * Some apps are not able to work when in Active Directory mode.  The
     * driver parameter can be passed to check for this type of incompatibility.
     *
     * @param string $driver driver requirement
     *
     * @return boolean state of accounts driver
     */

    function unhappy($driver = NULL)
    {
        // Load libraries and grab status information
        //-------------------------------------------

        try {
            $this->load->factory('accounts/Accounts_Factory');
            $this->load->library('accounts/Accounts_Configuration');

            $status = $this->accounts->get_system_status();
            $running_driver = $this->accounts_configuration->get_driver();

            if (!is_null($driver) && ($driver != $running_driver))
                $happy = FALSE;
            else
                $happy = TRUE;
        } catch (Accounts_Driver_Not_Set_Exception $e) {
            $happy = FALSE;
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return FALSE;
        }

        if ($happy && ($status === Accounts_Engine::STATUS_ONLINE))
            return FALSE;
        else
            return TRUE;
    }

    /**
     * Status widget.
     *
     * @param string $app_redirect redirect back to app
     * @param string $driver driver requirement
     *
     * @return view accounts status view
     */

    function widget($app_redirect, $driver = NULL)
    {
        // Show mode status widget if we're not initialized
        //-------------------------------------------------

        $this->load->module('accounts/system_mode');

        if (! $this->system_mode->initialized()) {
            $this->system_mode->widget();
            return;
        }

        // Load dependencies
        //------------------

        $this->lang->load('base');
        $this->load->library('accounts/Accounts_Configuration');

        // Load view data
        //---------------

        $driver_ok = TRUE;

        if (! is_null($driver)) {
            try {
                $running_driver = $this->accounts_configuration->get_driver();

                if ($running_driver != $driver)
                    $driver_ok = FALSE;
            } catch (Accounts_Driver_Not_Set_Exception $e) {
                // That's fine...
            }
        }

        if (! preg_match('/^([a-zA-Z0-9_])*/', $app_redirect))
            return;

        $options['javascript'] = array(clearos_app_htdocs('accounts') . '/status.js.php');
        $data['app_redirect'] = $app_redirect;

        // Load views
        //-----------

        if ($driver_ok)
            $this->page->view_form('accounts/status', $data, lang('base_server_status'), $options);
        else
            $this->page->view_form('accounts/driver_incompatible', $data, lang('base_server_status'));
    }

    /**
     * Returns accounts status.
     *
     * @return JSON accounts status information
     */

    function get_info()
    {
        // Get capabilities
        //-----------------

        $data['devel'] = ($_SERVER['SERVER_PORT'] == 1501) ? TRUE : FALSE;
        $data['marketplace_installed'] = (clearos_app_installed('marketplace')) ? TRUE : FALSE;
        $data['openldap_directory_installed'] = (clearos_app_installed('openldap_directory')) ? TRUE : FALSE;
        $data['openldap_driver_installed'] = (clearos_library_installed('openldap_directory/OpenLDAP')) ? TRUE : FALSE;
        $data['samba_directory_installed'] = (clearos_app_installed('samba_directory')) ? TRUE : FALSE;
        $data['ad_installed'] = (clearos_app_installed('active_directory')) ? TRUE : FALSE;

        try {
            $this->load->library('base/OS');

            $os_name = $this->os->get_name();

            // TODO: this should be generalized (e.g. if (os_type = business)
            if (preg_match('/Community/', $os_name)) {
                $data['ad_not_available'] = TRUE;
                $data['samba_directory_not_available'] = TRUE;
            } else {
                $data['ad_not_available'] = FALSE;
                $data['samba_directory_not_available'] = FALSE;
            }
        } catch (Exception $e) {
            $data['code'] = 1;
            $data['error_message'] = clearos_exception_message($e);
        }

        // Get account status
        //-------------------

        try {
            $this->load->factory('accounts/Accounts_Factory');

            $status = $this->accounts->get_system_status();

            if ($status == Accounts_Engine::STATUS_ONLINE) {
                $data['status_message'] = lang('accounts_account_information_is_online');
                $data['status'] = 'online';
            } else if ($status == Accounts_Engine::STATUS_OFFLINE) {
                $data['status_message'] = lang('accounts_account_information_is_offline');
                $data['status'] = 'offline';
            } else if ($status == Accounts_Engine::STATUS_UNINITIALIZED) {
                $data['status_message'] = lang('accounts_account_system_is_not_initialized');
                $data['status'] = 'uninitialized';
            } else if ($status == Accounts_Engine::STATUS_INITIALIZING) {
                $data['status_message'] = lang('accounts_account_system_is_initializing');
                $data['status'] = 'initializing';
            } else if ($status == Accounts_Engine::STATUS_BUSY) {
                $data['status_message'] = lang('accounts_account_system_initializing_extensions');
                $data['status'] = 'busy';
            }

            $data['code'] = 0;
        } catch (Accounts_Driver_Not_Set_Exception $e) {
            $data['status_message'] = lang('accounts_account_system_is_not_initialized');
            $data['status'] = 'uninitialized';
        } catch (Exception $e) {
            $data['code'] = 1;
            $data['error_message'] = clearos_exception_message($e);
        }

        // Return status message
        //----------------------

        $this->output->set_header("Content-Type: application/json");
        $this->output->set_output(json_encode($data));
    }
}
