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
 * @package     Twig.php
 * @author      Michael Lämmlein <m.laemmlein@wdv.de>
 * @copyright   ©2007-2015 Andreas Heigl/wdv Gesellschaft für Medien & Kommunikation mbH & Co. OHG
 * @license     http://www.opensource.org/licenses/mit-license.php MIT-License
 * @version     2.7.0
 * @since       10.11.15
 */

/**
 * Namespace
 */
namespace Laemmi\Yourls\DefaultTools\Template;

/**
 * Class Twig
 *
 * @package Laemmi\Yourls\DefaultTools\Template
 */
class Twig implements TemplateInterface
{
    /**
     * Environment
     *
     * @var null|\Twig_Environment
     */
    protected $_twig = null;

    /**
     * Assign values
     *
     * @var array
     */
    protected $_assign = array();

    /**
     * Init
     *
     * @param array $options
     */
    public function init(array $options)
    {
        $loader = new \Twig_Loader_Filesystem($options['path_template']);
        $this->_twig = new \Twig_Environment($loader,[
//            'cache' => '/path/to/compilation_cache',
        ]);

        $this->_twig->addExtension(new Twig\Extension([
            'namespace' => $options['namespace']
        ]));
    }

    /**
     * Render
     *
     * @param string $name
     * @param array $context
     * @return string
     */
    public function render($name = '', array $context = array())
    {
        $context = array_merge($context, $this->_assign);
        unset($this->_assign);
        return $this->_twig->render($this->getName($name), $context);
    }

    /**
     * Assign
     *
     * @param $key
     * @param $value
     */
    public function assign($key, $value)
    {
        $this->_assign[$key] = $value;
    }

    /**
     * Get template name
     * @param $name
     * @return string
     */
    protected function getName($name)
    {
        return $name . '.twig';
    }
}