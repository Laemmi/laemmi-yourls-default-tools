<?php

/**
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @category    laemmi-yourls-default-tools
 * @author      Michael Lämmlein <laemmi@spacerabbit.de>
 * @copyright   ©2015 laemmi
 * @license     http://www.opensource.org/licenses/mit-license.php MIT-License
 * @version     1.0.0
 * @since       03.11.15
 */

declare(strict_types=1);

namespace Laemmi\Yourls\Plugin\DefaultTools;

use Exception;
use Laemmi\Yourls\Plugin\AbstractDefault;

class Plugin extends AbstractDefault
{
    /**
     * Namespace
     */
    protected const APP_NAMESPACE = 'laemmi-yourls-default-tools';

    /**
     * Action activated_plugin
     *
     * @param array $args
     * @throws Exception
     */
    public function action_activated_plugin(array $args)
    {
        list ($plugin) = $args;

        if (false === stripos($plugin, self::APP_NAMESPACE)) {
            return;
        }

        $plugins = $this->db()->get_plugins();

        $key = array_search($plugin, $plugins);
        unset($plugins[$key]);
        $key2 = $this->isLaemmiPlugins($plugins);
        $key = false !== $key2 ? $key2 : $key;
        array_splice($plugins, $key, 0, [$plugin]);

        yourls_update_option('active_plugins', $plugins);
    }


    /**
     * Action deactivated_plugin
     *
     * @param array $args
     * @throws Exception
     */
//    public function action_deactivated_plugin(array $args)
//    {
//        list($plugin) = $args;
//
//        if(false === stripos($plugin, self::APP_NAMESPACE)) {
//            return;
//        }
//
//        $plugins = $this->db()->get_plugins();
//
//        $key = $this->isLaemmiPlugins($plugins);
//
//        if(false !== $key) {
//            array_splice($plugins, $key, 0, array($plugin));
//            yourls_update_option('active_plugins', $plugins);
//            yourls_redirect(yourls_admin_url('plugins.php?success=notdeactivated' ), 302);
//        }
//    }

    ####################################################################################################################

    /**
     * Check if laemmi plugins installed
     * return key of array
     *
     * @param array $plugins
     * @return bool|int
     */
    private function isLaemmiPlugins(array $plugins)
    {
        foreach ($plugins as $key => $val) {
            if (false !== stripos($val, 'laemmi-')) {
                return $key;
            }
        }

        return false;
    }
}
