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
 * @author      Michael Lämmlein <ml@spacerabbit.de>
 * @copyright   ©2015 laemmi
 * @license     http://www.opensource.org/licenses/mit-license.php MIT-License
 * @version     1.1.0
 * @since       10.11.15
 */

declare(strict_types=1);

namespace Laemmi\Yourls\Plugin\DefaultTools\Template;

use Twig_Environment;
use Twig_Loader_Filesystem;

class Twig implements TemplateInterface
{
    /**
     * Environment
     *
     * @var null|Twig_Environment
     */
    protected $twig = null;

    /**
     * Assign values
     *
     * @var array
     */
    protected $assign = [];

    /**
     * Init
     *
     * @param array $options
     */
    public function init(array $options)
    {
        $loader = new Twig_Loader_Filesystem($options['path_template']);
        $this->twig = new Twig_Environment($loader, [
            'cache' => $options['path_cache'],
            'auto_reload' => true
        ]);

        $this->twig->addExtension(new Twig\Extension([
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
    public function render($name = '', array $context = [])
    {
        $context = array_merge($context, $this->assign);
        $this->assign = [];
        return $this->twig->render($this->getName($name), $context);
    }

    /**
     * Assign
     *
     * @param $key
     * @param $value
     */
    public function assign($key, $value)
    {
        $this->assign[$key] = $value;
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
