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
 * @package     Extension.php
 * @author      Michael Lämmlein <m.laemmlein@wdv.de>
 * @copyright   ©2007-2015 Andreas Heigl/wdv Gesellschaft für Medien & Kommunikation mbH & Co. OHG
 * @license     http://www.opensource.org/licenses/mit-license.php MIT-License
 * @version     2.7.0
 * @since       10.11.15
 */

/**
 * Namespace
 */
namespace Laemmi\Yourls\Plugin\DefaultTools\Template\Twig;

class Extension extends \Twig_Extension
{
    private $_options = [];

    public function __construct(array $options)
    {
        $this->_options = $options;
    }

    public function getName()
    {
        return 'yourls_functions';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('yourls__', function($text, $domain = '') {
                if(! $domain) {
                    $domain = $this->_options['namespace'];
                }
                return yourls__($text, $domain);
            }),
        );
    }
}