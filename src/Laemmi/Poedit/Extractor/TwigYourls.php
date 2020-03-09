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
 * @package     TwigYourls.php
 * @author      Michael Lämmlein <laemmi@spacerabbit.de>
 * @copyright   ©2015 laemmi
 * @license     http://www.opensource.org/licenses/mit-license.php MIT-License
 * @version     1.0.0
 * @since       16.11.15
 */

declare(strict_types=1);

namespace Laemmi\Poedit\Extractor;

/**
 * Class TwigYourls
 *
 * @package Laemmi\Poedit\Extractor
 */
class TwigYourls
{
    /**
     * Templates
     *
     * @var array
     */
    protected $templates = [];

    /**
     * Parameters
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Destruct
     */
    public function __destruct()
    {
        foreach ($this->templates as $val) {
            unlink($val);
        }

        $this->reset();
    }

    /**
     * Add template
     *
     * @param $file
     */
    public function addTemplate($file)
    {
        $content = file_get_contents($file);
        $content = preg_replace("/\{\{(.*?)\}\}/", "<?php$1?>", $content);

        $file_cache = tempnam(sys_get_temp_dir(), uniqid());

        if (file_put_contents($file_cache, $content)) {
            $this->templates[] = $file_cache;
        }
    }

    /**
     * Add parameter for gettext
     *
     * @param $value
     */
    public function addGettextParameter($value)
    {
        $this->parameters[] = $value;
    }

    /**
     * Extract
     */
    public function extract()
    {
//        $command = 'xgettext';
        $command = '/usr/local/opt/gettext/bin/xgettext';
        $command .= ' ' . implode(' ', $this->parameters);
        $command .= ' ' . implode(' ', $this->templates);

        $this->log($command);

        $error = 0;
        $output = system($command, $error);
        if (0 !== $error) {
            $m = sprintf(
                'Gettext command "%s" failed with error code %s and output: %s',
                $command,
                $error,
                $output
            );

            $this->log($m);

            throw new \RuntimeException($m);
        }
    }

    /**
     * Reset
     */
    public function reset()
    {
        $this->templates = [];
        $this->parameters = [];
    }

    /**
     * Log
     *
     * @param $value
     */
    private function log($value)
    {
        $value = "# " . $value . "\n";
//        error_log($value, 3, __DIR__ . '/../_cache/TwigYourls.log');
    }
}
