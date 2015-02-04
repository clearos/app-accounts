<?php

/**
 * Accounts mode check.
 *
 * @category   apps
 * @package    accounts
 * @subpackage controllers
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
//
// TODO: this is a usability and coding circle.  The decision to make
// Account Synchronization and app instead of integrating it creates this
// headache.  There has to be a better way.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \Exception as Exception;
use \clearos\apps\mode\Mode_Engine as Mode_Engine;
use \clearos\apps\mode\Mode_Driver_Not_Set_Exception as Mode_Driver_Not_Set_Exception;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Accounts mode check.
 *
 * @category   apps
 * @package    accounts
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/accounts/
 */

class System_Mode extends ClearOS_Controller
{
    /**
     * Default controller.
     *
     * @return view
     */

    function index()
    {
        if (! $this->initialized())
            $this->widget();
    }

    /**
     * Confirm standalone mode view.
     *
     * @param string $username username
     *
     * @return view
     */

    function confirm()
    {
        $this->page->view_form('accounts/mode_confirm', NULL, lang('base_confirm'));
    }


    /**
     * Returns state of account mode.
     *
     * @return boolean state of account mode
     */

    function initialized()
    {
        try {
            $this->load->factory('mode/Mode_Factory');
            $this->load->library('base/OS');

            $mode = $this->mode->get_mode();
            $os = $this->os->get_name();

            // TODO: use a policy instead
            if (!preg_match('/ClearOS Business/i', $os)) {
                $this->mode->set_mode(Mode_Engine::MODE_STANDALONE);
                $initialized = TRUE;
            } else {
                $initialized = (empty($mode)) ? FALSE : TRUE;
            }
        } catch (Mode_Driver_Not_Set_Exception $e) {
            $initialized = FALSE;
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return FALSE;
        }

        return $initialized;
    }

    /**
     * Sets system mode to standalone.
     *
     * @return view summary view
     */

    function set()
    {
        try {
            $this->load->factory('mode/Mode_Factory');

            $this->mode->set_mode(Mode_Engine::MODE_STANDALONE);
            redirect('/accounts');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }

    /**
     * Status widget.
     *
     * @return view system mode status view
     */

    function widget()
    {
        $data['account_synchronization_installed'] = clearos_app_installed('account_synchronization');

        $this->page->view_form('accounts/mode', $data, lang('base_server_status'));
    }
}
