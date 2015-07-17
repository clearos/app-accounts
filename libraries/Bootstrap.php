<?php

/**
 * Bootstrap class.
 *
 * @category   apps
 * @package    accounts
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
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

use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Shell as Shell;
use \clearos\apps\base\Yum as Yum;
use \clearos\apps\base\Yum_Busy_Exception as Yum_Busy_Exception;

clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('base/Shell');
clearos_load_library('base/Yum');
clearos_load_library('base/Yum_Busy_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Bootstrap class.
 *
 * @category   apps
 * @package    accounts
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/accounts/
 */

class Bootstrap extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const COMMAND_INITIALIZE = '/usr/sbin/initialize-accounts';
    const FILE_INITIALIZING = '/var/clearos/accounts/lock/initializing';
    const FILE_INSTALL_FAILED = '/var/clearos/accounts/lock/install_failed';
    const FILE_READY_TO_CONFIGURE = '/var/clearos/accounts/lock/ready';
    const STATUS_INITIALIZING = 'initializing';
    const STATUS_INITIALIZED = 'initialized';
    const STATUS_READY_TO_CONFIGURE = 'ready';
    const STATUS_INSTALL_FAILED = 'install_failed';
    const STATUS_UNINITIALIZED = 'uninitialized';

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Bootstrap constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Initializes the default accounts driver.
     *
     * @param string $directory directory driver
     *
     * @return string accounts driver
     * @throws Engine_Exception
     */

    public function initialize($directory)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Lock state file
        //----------------

        $file = new File(self::FILE_INITIALIZING);
        $initializing_lock = fopen(self::FILE_INITIALIZING, 'w');

        if (!flock($initializing_lock, LOCK_EX | LOCK_NB)) {
            clearos_log('accounts', 'local initialization is already running');
            return;
        }

        $ready_to_configure_file = new File(self::FILE_READY_TO_CONFIGURE);

        if ($ready_to_configure_file->exists())
            $ready_to_configure_file->delete();

        $install_file = new File(self::FILE_INSTALL_FAILED);

        if ($install_file->exists())
            $install_file->delete();

        // Install and initialize
        //-----------------------

        try {
            // TODO: adjust clearos_load_library for development mode.
            // Use file_exists() instead  in the interim
            if ($directory === 'openldap') {
                if (! file_exists('/usr/clearos/apps/openldap_directory/libraries/OpenLDAP.php'))
                    $this->_install_app('app-openldap-directory-core');

                clearos_load_library('openldap_directory/OpenLDAP');
                $openldap = new \clearos\apps\openldap_directory\OpenLDAP();
                $openldap->run_initialize(\clearos\apps\openldap_directory\OpenLDAP::DEFAULT_DOMAIN);
            } else if ($directory === 'samba_directory') {
                if (! file_exists('/usr/clearos/apps/samba_directory/libraries/Samba_Directory.php'))
                    $this->_install_app('app-samba-directory');
            } else if ($directory === 'ad') {
                if (! file_exists('/usr/clearos/apps/active_directory/libraries/Active_Directory.php'))
                    $this->_install_app('app-active-directory');
            }
        } catch (Exception $e) {
            $file->delete();
            throw new Engine_Exception(clearos_exception_message($e));
        }

        // Kludgy
        // - make sure the run_initialize() has a chance to set its status
        // - give the system a window to let admin know system is ready to configure
        //---------------------------------------------------------------------------

        try {
            $ready_to_configure_file->create('root', 'root', '0644');
            sleep(15);
            $ready_to_configure_file->delete();
        } catch (Exception $e) {
            // Not fatal
        }

        // Cleanup file / file lock
        //-------------------------

        flock($initializing_lock, LOCK_UN);
        fclose($initializing_lock);

        if ($file->exists())
            $file->delete();
    }

    /**
     * Returns status of account system.
     *
     * - Bootstrap::STATUS_INITIALIZING
     * - Bootstrap::STATUS_INITIALIZED
     *
     * @return string bootstrap system status
     * @throws Engine_Exception
     */

    public function get_status()
    {
        clearos_profile(__METHOD__, __LINE__);

        // Check initializing
        //-------------------

        $file = new File(self::FILE_INITIALIZING);
        $ready_to_configure = new File(self::FILE_READY_TO_CONFIGURE);

        if ($file->exists()) {
            $initializing_lock = fopen(self::FILE_INITIALIZING, 'r');

            if (!flock($initializing_lock, LOCK_SH | LOCK_NB)) {
                if ($ready_to_configure->exists())
                    return self::STATUS_READY_TO_CONFIGURE;
                else
                    return self::STATUS_INITIALIZING;
            }
        }

        $install_failed = new File(self::FILE_INSTALL_FAILED);

        if ($install_failed->exists())
            return self::STATUS_INSTALL_FAILED;
        else
            return self::STATUS_UNINITIALIZED;
    }

    /**
     * Initializes the OpenLDAP accounts system.
     *
     * @param string $directory directory driver
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception
     */

    public function run_initialize($directory)
    {
        clearos_profile(__METHOD__, __LINE__);

        $options['background'] = TRUE;

        $shell = new Shell();
        $shell->execute(self::COMMAND_INITIALIZE, "-d $directory", TRUE, $options);
    }

    /**
     * Installs app.
     *
     * @param string $app app to install
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception
     */

    private function _install_app($app)
    {
        clearos_profile(__METHOD__, __LINE__);

        $is_busy = TRUE;

        while ($is_busy) {
            try {
                clearos_log('accounts', lang('accounts_installing_accounts_driver'));

                $yum = new Yum();
                $yum->install(array($app), FALSE);

                $is_busy = FALSE;
            } catch (Yum_Busy_Exception $e) {
                clearos_log('accounts', lang('accounts_preparing_install'));

                sleep(10);
            } catch (\Exception $e) {
                clearos_log('accounts', lang('accounts_driver_installed_failed'));

                $install_file = new File(self::FILE_INSTALL_FAILED);
                $install_file->create('webconfig', 'webconfig', '0644');
                break;
            }
        }
    }
}
