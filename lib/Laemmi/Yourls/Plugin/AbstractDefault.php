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
 * @category    Laemmi\Yourls\Plugin
 * @author      Michael Lämmlein <laemmi@spacerabbit.de>
 * @copyright   ©2015 laemmi
 * @license     http://www.opensource.org/licenses/mit-license.php MIT-License
 * @version     1.0.0
 * @since       03.11.15
 */

namespace Laemmi\Yourls\Plugin;

use DateTime;
use DateTimeZone;
use Exception;
use Laemmi\Yourls\Plugin\DefaultTools\Template;
use PDO;

abstract class AbstractDefault
{
    /**
     * Namespace
     */
    const APP_NAMESPACE = 'laemmi-yourls-default-tools';

    /**
     * Options
     *
     * @var array
     */
    protected $_options = [];

    /**
     * Is bootstrap loaded
     *
     * @var bool
     */
    protected static $_isBootstrap = false;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);

        foreach (get_class_methods($this) as $key => $method) {
            switch (substr($method, 0, 7)) {
                case 'action_':
                    $this->addAction($method);
                    break;
                case 'filter_':
                    $this->addFilter($method);
                    break;
            }
        }
        // Init some stuff ...
        $this->init();
    }

    /**
     * Init function
     */
    protected function init() {}

    /**
     * Set options
     *
     * @param array $options
     * @return $this
     */
    protected function setOptions(array $options)
    {
        $options = array_filter($options);
        $this->_options = array_merge($this->_options, $options);

        return $this;
    }

    /**
     * Add action function
     *
     * @param $name
     */
    protected function addAction($name)
    {
//         $hook, $function_name, $priority = 10, $accepted_args = 1, $type = 'action'
//        yourls_add_filter(substr($name, 7), [$this, $name], 10, 1, 'action');
        yourls_add_action(substr($name, 7), [$this, $name]);
    }

    /**
     * Add filter function
     *
     * @param $name
     */
    protected function addFilter($name)
    {
//         $hook, $function_name, $priority = 10, $accepted_args = NULL, $type = 'filter'
//        yourls_add_filter(substr($name, 7), [$this, $name], 10, NULL, 'filter');
        yourls_add_filter(substr($name, 7), [$this, $name]);
    }

    /**
     * Get db connection
     *
     * @return mixed
     * @throws Exception
     */
    protected function db()
    {
        if (!isset($this->_options['db']) || !$this->_options['db'] instanceof PDO) {
            throw new Exception('No database instance available');
        }

        return $this->_options['db'];
    }

    /**
     * Return DateTime instance
     *
     * @param string $time
     * @return DateTime
     * @throws Exception
     */
    protected function getDateTime($time = 'now')
    {
        // Check if UNIX-Timestamp
        if (preg_match('/^\d{10}/', $time)) {
            $time = '@' . $time;
        }
        $date = new DateTime($time, new DateTimeZone('UTC'));

        return $date;
    }

    /**
     * Return DateTime with correction
     *
     * @param string $time
     * @return DateTime
     * @throws Exception
     */
    protected function getDateTimeDisplay($time = 'now')
    {
        $date = $this->getDateTime($time);
        $date->modify('+' . YOURLS_HOURS_OFFSET .' hour');
        return $date;
    }

    /**
     * Add setting column in url table
     *
     * @param $key
     * @param $val
     * @throws Exception
     */
    protected function addUrlSetting($key, $val)
    {
        $query = "SHOW COLUMNS FROM " . YOURLS_DB_TABLE_URL . " LIKE '%s'";
        $results = $this->db()->get_results(sprintf($query, $key));
        if (!$results) {
            $query = "ALTER TABLE " . YOURLS_DB_TABLE_URL . " ADD (%s)";
            $this->db()->query(sprintf($query, $key . " " . $val['field']));
        }
    }

    /**
     * Drop setting column in url table
     *
     * @param $key
     * @throws Exception
     */
    protected function dropUrlSetting($key)
    {
        $query = "SHOW COLUMNS FROM " . YOURLS_DB_TABLE_URL . " LIKE '%s'";
        $results = $this->db()->get_results(sprintf($query, $key));
        if($results) {
            $query = "ALTER TABLE " . YOURLS_DB_TABLE_URL . " DROP %s";
            $this->db()->query(sprintf($query, $key));
        }
    }

    /**
     * Update setting in url table
     *
     * @param array $values
     * @param $keyword
     * @return mixed
     * @throws Exception
     */
    protected function updateUrlSetting(array $values, $keyword)
    {
        $query = "UPDATE
            ".YOURLS_DB_TABLE_URL."
         SET
            %s
         WHERE
            keyword = '%s'
         LIMIT 1";

        $data = [];
        foreach($values as $key => $val) {
            $data[] = $key . "='" . $val ."'";
        }

        return $this->db()->query(sprintf($query, implode(',', $data), $keyword));
    }

    /**
     * Get css file with style tag
     *
     * @param string $name
     * @return bool|string
     */
    protected function getCssStyle($name = 'assets/style.css')
    {
        $file = YOURLS_PLUGINDIR . '/' . static::APP_NAMESPACE . '/' . $name;
        if (!is_file($file)) {
            return false;
        }
        $css = file_get_contents($file);
        $css = preg_replace_callback("/url\((.*?)\)/", function($matches) {
            $file = YOURLS_PLUGINDIR . '/' . static::APP_NAMESPACE . '/' . $matches[1];
            if (!is_file($file)) {
                return;
            }
            return 'url(data:'.mime_content_type($file).';base64,'.base64_encode(file_get_contents($file)).')';
        }, $css);

        return '<style>' . $css . '</style>';
    }

    /**
     * Get js file with script tag
     *
     * @param string $name
     * @return string
     */
    public function getJsScript($name = 'assets/default.js')
    {
        $file = YOURLS_PLUGINDIR . '/' . static::APP_NAMESPACE . '/' . $name;
        if (!is_file($file)) {
            return false;
        }

        return '<script>' . file_get_contents($file) . '</script>';
    }

    /**
     * Get bootstrap js
     *
     * @return bool|string
     */
    public function getBootstrap()
    {
        if (false === self::$_isBootstrap) {
            self::$_isBootstrap = true;
            $path = yourls_site_url(false) . '/user/plugins/' . self::APP_NAMESPACE . '/assets/lib/bootstrap/dist';
            return trim('
            <link href="' . $path . '/css/bootstrap.min.css" rel="stylesheet">
            <link href="' . $path . '/css/bootstrap-theme.min.css" rel="stylesheet">
            <script src="' . $path . '/js/bootstrap.min.js"></script>
            ');
        }
        return false;
    }

    /**
     * Load textdomain for translations
     */
    public function loadTextdomain()
    {
        $file = YOURLS_PLUGINDIR . '/' . static::APP_NAMESPACE . '/translations';
        yourls_load_custom_textdomain(static::APP_NAMESPACE, $file);
    }

    ####################################################################################################################

    /**
     * Start session
     */
    protected function startSession()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Set session value
     *
     * @param $key
     * @param $value
     * @param null $namespace
     */
    protected function setSession($key, $value, $namespace = null)
    {
        $namespace = $this->getSessionNamespace($namespace);

        $_SESSION['laemmi'][$namespace][$key] = $value;
    }

    /**
     * Get session value
     *
     * @param $key
     * @param null $namespace
     * @return bool
     */
    protected function getSession($key, $namespace=null)
    {
        $namespace = $this->getSessionNamespace($namespace);

        return isset($_SESSION['laemmi'][$namespace][$key]) ? $_SESSION['laemmi'][$namespace][$key] : false;
    }

    /**
     * Reset session
     *
     * @param null $namespace
     */
    protected function resetSession($namespace=null)
    {
        $namespace = $this->getSessionNamespace($namespace);

        unset($_SESSION['laemmi'][$namespace]);
    }

    /**
     * Get session namespace
     *
     * @param null $namespace
     * @return null|string
     */
    protected function getSessionNamespace($namespace=null)
    {
        $namespace = !is_null($namespace) ? $namespace : static::APP_NAMESPACE;

        return $namespace;
    }

    ####################################################################################################################

    /**
     * Get request value
     *
     * @param $key
     * @return null
     */
    protected function getRequest($key)
    {
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
    }

    /**
     * Set request value
     *
     * @param $key
     * @param $val
     */
    protected function setRequest($key, $val)
    {
        $_REQUEST[$key] = $val;
    }

    ####################################################################################################################

    /**
     * Template
     *
     * @var null|TemplateInterface
     */
    private $_template = null;

    /**
     * Init Template
     *
     * @param array $options
     */
    protected function initTemplate(array $options = array())
    {
        $options['path_template'] = isset($options['path_template']) ? $options['path_template'] : 'templates';
        $options['path_template'] = YOURLS_PLUGINDIR . '/' . static::APP_NAMESPACE . '/' . $options['path_template'];
        $options['path_cache'] = isset($options['path_cache']) ? $options['path_cache'] : 'cache/twig';
        $options['path_cache'] = YOURLS_ABSPATH . '/' . $options['path_cache'];
        $options['namespace'] = static::APP_NAMESPACE;

        $this->_template = Template::factory('twig');
        $this->_template->init($options);
    }

    /**
     * Get Template
     *
     * @return TemplateInterface|null
     */
    protected function getTemplate()
    {
        return $this->_template;
    }

    ####################################################################################################################

    /**
     * Get allowed permissions
     *
     * @return array
     */
    protected function helperGetAllowedPermissions()
    {
        if ($this->getSession('login', 'laemmi-yourls-easy-ldap')) {
            $inter = array_intersect_key($this->_options['allowed_groups'], $this->getSession('groups', 'laemmi-yourls-easy-ldap'));
            $permissions = [];
            foreach ($inter as $val) {
                foreach ($val as $_val) {
                    $permissions[$_val] = $_val;
                }
            }
        } else {
            $permissions = array_combine($this->_adminpermission, $this->_adminpermission);
        }

        return $permissions;
    }

    /**
     * Has permission to right
     *
     * @param $permission
     * @return bool
     */
    protected function _hasPermission($permission)
    {
        $permissions = $this->helperGetAllowedPermissions();

        return isset($permissions[$permission]);
    }
}