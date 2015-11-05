<?php
/**
 * Copyright 2007-2015 Andreas Heigl/wdv Gesellschaft für Medien & Kommunikation mbH & Co. OHG
 *
 *
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
 * @package     Plugin.php
 * @author      Michael Lämmlein <m.laemmlein@wdv.de>
 * @copyright   ©2007-2015 Andreas Heigl/wdv Gesellschaft für Medien & Kommunikation mbH & Co. OHG
 * @license     http://www.opensource.org/licenses/mit-license.php MIT-License
 * @version     2.7.0
 * @since       03.11.15
 */

/**
 * Namespace
 */
namespace Laemmi\Yourls\DefaultTools;

require_once 'Plugin/AbstractDefault.php';

use Laemmi\Yourls\Plugin\AbstractDefault;

/**
 * Class Plugin
 *
 * @package Laemmi\Yourls\DefaultTools
 */
class Plugin extends AbstractDefault
{
    /**
     * Namespace
     */
    const APP_NAMESPACE = 'laemmi-yourls-default-tools';

    ####################################################################################################################

    /**
     * Action activated_plugin
     *
     * @param array $args
     * @throws \Exception
     */
    public function action_activated_plugin(array $args)
    {
        list($plugin) = $args;

        if (false === stripos($plugin, self::APP_NAMESPACE)) {
            return;
        }

        $plugins = $this->db()->plugins;

        $key = array_search($plugin, $plugins);
        unset($plugins[$key]);

        foreach($plugins as $k => $val) {
            if (false !== stripos($val, 'laemmi-')) {
                $key = $k;
                break;
            }
        }
        array_splice($plugins, $key, 0, array($plugin));

        yourls_update_option('active_plugins', $plugins);
    }
}