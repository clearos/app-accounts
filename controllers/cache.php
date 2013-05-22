<?php

/**
 * Accounts cache controller.
 *
 * @category   apps
 * @package    accounts
 * @subpackage controllers
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
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Accounts cache controller.
 *
 * @category   apps
 * @package    accounts
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/accounts/
 */

class Cache extends ClearOS_Controller
{
    /**
     * Users overview.
     *
     ( @param string $app app name
     *
     * @return view
     */

    function index($app = 'users')
    {
        // Bail if we're not in a happy state
        //-----------------------------------

        $this->load->module('accounts/status');

        if ($this->status->unhappy())
            return;

        // Load libraries
        //---------------

        $this->lang->load('accounts');

        // Load views
        //-----------

        $data['app'] = $app;

        $this->page->view_form('accounts/cache', $data, lang('accounts_cache_manager'));
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

    function needs_reset()
    {
        // Load libraries
        //---------------

        $this->load->library('accounts/Accounts_Configuration');

        // In Active Directory mode, loading users might take time
        //--------------------------------------------------------

        try {
            $driver = $this->accounts_configuration->get_driver();
            $cache_loaded = ($this->session->userdata('directory_cache') == 'loaded') ? TRUE : FALSE;
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        if (($driver == 'active_directory') && !$cache_loaded)
            return TRUE;
        else
            return FALSe;
    }

    /**
     * Shows cache status widget
     *
     * @return view
     */

    function widget($app)
    {
        $this->lang->load('accounts');

        $data['app'] = $app;
        $options['javascript'] = array(clearos_app_htdocs('accounts') . '/cache.js.php');

        $this->page->view_form('accounts/cache', $data, lang('accounts_cache_manager'), $options);
    }

    /**
     * Loads user list in order to populate the cache.
     *
     * @return JSON status
     */

    function load()
    {
        // Load dependencies
        //------------------

        $this->load->factory('users/User_Manager_Factory');
        $this->load->factory('accounts/Accounts_Factory');

        // Handle cache
        //-------------

        try {
            // Do a cache reset on the first request or when user hits reload
            $cache_state = $this->session->userdata('directory_cache');
            if (empty($cache_state) || ($cache_state == 'reset'))
                $this->accounts->reset_cache();

            // Loading the user data loads the cache
            $not_used = $this->user_manager->get_core_details();

            // Set our session variable
            $this->session->set_userdata('directory_cache', 'loaded');
            $data['error_code'] = 0;
        } catch (Exception $e) {
            $data['error_code'] = clearos_exception_code($e);
            $data['error_message'] = clearos_exception_message($e);
        }

        // Return status message
        //----------------------

        $this->output->set_header("Content-Type: application/json");
        $this->output->set_output(json_encode($data));
    }

    /**
     * Reloads cache
     *
     * @return void
     */

    function reset()
    {
        // Set our session variable to indicate that cache is not loaded
        $this->session->set_userdata('directory_cache', 'reset');
    }
}
