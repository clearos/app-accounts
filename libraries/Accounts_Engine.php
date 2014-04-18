<?php

/**
 * Accounts engine class.
 *
 * @category   apps
 * @package    accounts
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011-2013 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/accounts/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\accounts;

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
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\accounts\Accounts_Configuration as Accounts_Configuration;
use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\groups\Group_Factory as Group_Factory;
use \clearos\apps\mode\Mode_Engine as Mode_Engine;
use \clearos\apps\mode\Mode_Factory as Mode_Factory;

clearos_load_library('accounts/Accounts_Configuration');
clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('base/Folder');
clearos_load_library('groups/Group_Factory');
clearos_load_library('mode/Mode_Engine');
clearos_load_library('mode/Mode_Factory');

// Exceptions
//-----------

use \Exception as Exception;
use \clearos\apps\accounts\Accounts_Driver_Not_Set_Exception as Accounts_Driver_Not_Set_Exception;

clearos_load_library('accounts/Accounts_Driver_Not_Set_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Accounts engine class.
 *
 * @category   apps
 * @package    accounts
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011-2013 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/accounts/
 */

class Accounts_Engine extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const FILE_TRANSACTION_LOG = '/var/clearos/accounts/transaction.log';
    const FILE_TRANSACTION_STATE = '/var/clearos/accounts/transaction.state';
    const FILE_INITIALIZED = '/var/clearos/accounts/initialized';
    const FILE_READY = '/var/clearos/accounts/ready';
    const PATH_PLUGINS = '/var/clearos/accounts/plugins';

    const MODE_CONNECTOR = 'connector';
    const MODE_MASTER = 'master';
    const MODE_SLAVE = 'slave';
    const MODE_STANDALONE = 'standalone';

    const STATUS_INITIALIZING = 'initializing';
    const STATUS_UNINITIALIZED = 'uninitialized';
    const STATUS_OFFLINE = 'offline';
    const STATUS_ONLINE = 'online';
    const STATUS_BUSY = 'busy';

    const DRIVER_UNSET = 'unset';
    const DRIVER_OK = 'ok';
    const DRIVER_OTHER = 'other';

    // Capabilities
    //-------------

    const CAPABILITY_READ_ONLY = 'read_only';
    const CAPABILITY_READ_WRITE = 'read_write';

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Accounts engine constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->modes = array(
            self::MODE_CONNECTOR => lang('accounts_connector'),
            self::MODE_MASTER => lang('accounts_master'),
            self::MODE_SLAVE => lang('accounts_slave'),
            self::MODE_STANDALONE => lang('accounts_standalone')
        );
    }

    /**
     * Returns a list of installed plugins.
     *
     * @return array plugin list
     * @throws Engine_Exception
     */

    public function get_plugins()
    {
        clearos_profile(__METHOD__, __LINE__);

        $folder = new Folder(self::PATH_PLUGINS);

        $list = $folder->get_listing();

        $plugins = array();

        foreach ($list as $plugin_file) {
            if (! preg_match('/\.php$/', $plugin_file))
                continue;

            $plugin = array();
            $plugin_basename = preg_replace('/\.php/', '', $plugin_file);

            include self::PATH_PLUGINS . '/' . $plugin_file;

            $plugins[$plugin_basename] = $plugin;
        }

        return $plugins;
    }

    /**
     * Initializes plugin groups.
     *
     * During the initialization() method, there's a good time to 
     * get the plugin groups added just before the initialization
     * process is complete.  The "check_init" flag allows us to skip the
     * initialization check for this particular case.
     *
     * @param boolean $check_init flag to check initialization
     *
     * @return void
     * @throws Engine_Exception
     */

    public function initialize_plugin_groups($check_init = TRUE)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Bail if not initialized
        //------------------------

        if ($check_init && !$this->is_initialized())
            return;

        // Set initializing
        // Bail if we are slave... not necessary
        //--------------------------------------

        $sysmode = Mode_Factory::create();
        $mode = $sysmode->get_mode();

        if ($mode === Mode_Engine::MODE_SLAVE)
            return;

        // Load plugin info and initialize
        //--------------------------------

        $plugins = $this->get_plugins();
        $last_exception = NULL;

        try {
            foreach ($plugins as $plugin => $details) {
                $plugin_group = $plugin . '_plugin'; // TODO: hard coded value
                $group = Group_Factory::create($plugin_group);

                if (! $group->exists()) {
                    $info['core']['description'] = $details['name'];
                    $group->add($info);
                }
            }
        } catch (Exception $e) {
            $last_exception = $e;
        }
    }

    /**
     * Returns state of initialization.
     *
     * @return boolean TRUE if accounts have been initialized
     * @throws Engine_Exception
     */

    public function is_initialized()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_INITIALIZED);

        if ($file->exists())
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Returns state of readiness.
     *
     * This will return true if accounts is ready or initialized.
     *
     * @return boolean TRUE if accounts system is ready
     * @throws Engine_Exception
     */

    public function is_ready()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_READY);

        if ($file->exists())
            return TRUE;

        return $this->is_initialized();
    }

    /**
     * Logs an account transaction.
     *
     * When user or group information is changed, the event is sent to a log
     *
     * @param string $log_message log message
     * @return void
     */

    public function log_transaction($log_message)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Log the message
        //----------------

        $file = new File(self::FILE_TRANSACTION_LOG);

        if (!$file->exists())
            $file->create('root', 'webconfig', '0664');

        $timestamp = date('r');
        $file->add_lines("$timestamp - $log_message\n");

        // Put timestamp in state file
        //----------------------------

        $file = new File(self::FILE_TRANSACTION_STATE);

        if ($file->exists())
            $file->delete();

        $file->create('root', 'root', '0644');
        $file->add_lines("$timestamp\n");
    }

    /**
     * Sets initialized flag.
     *
     * @param boolean $state state
     *
     * @return void
     * @throws Engine_Exception
     */

    public function set_initialized($state = TRUE)
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_INITIALIZED);

        if ($state && !$file->exists()) {
            $file->create('root', 'root', '0644');

            // Call initalization hooks for central management
            if (clearos_library_installed('central_management/Accounts_Event')) {
                clearos_load_library('central_management/Accounts_Event');

                $accounts = new \clearos\apps\central_management\Accounts_Event();
                $accounts->initialize();
            }
        } else if (!$state && $file->exists()) {
            $file->delete();
        }
    }

    /**
     * Sets ready flag.
     *
     * The ready flag is set to notify account plugins/extensions that the
     * accounts system ready.  This gives plugins/extensions a chance to take
     * action before the "initialized" flag is set.  Samba, I'm looking at you.
     *
     * @param boolean $state state
     *
     * @return void
     * @throws Engine_Exception
     */

    public function set_ready($state = TRUE)
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_READY);

        if ($state && !$file->exists()) {
            $file->create('root', 'root', '0644');
        } else if (!$state && $file->exists()) {
            $file->delete();
        }
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E  M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Returns state of driver.
     *
     * @param string $driver_to_check driver to check
     *
     * @return integer state of driver
     * @throws Engine_Exception
     */

    protected function _get_driver_status($driver_to_check)
    {
        clearos_profile(__METHOD__, __LINE__);

        $accounts = new Accounts_Configuration();

        try {
            $driver_info = $accounts->get_driver_info();
        } catch (Accounts_Driver_Not_Set_Exception $e) {
            return self::DRIVER_UNSET;
        }

        if ($driver_info['driver'] === $driver_to_check)
            return self::DRIVER_OK;
        else
            return self::DRIVER_OTHER;
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N   R O U T I N E S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Validation routine for plugin state.
     *
     * @param boolean $state state of plugin
     *
     * @return boolean error message if state is invalid
     */

    public function validate_plugin_state($state)
    {
        clearos_profile(__METHOD__, __LINE__);
    }
}
