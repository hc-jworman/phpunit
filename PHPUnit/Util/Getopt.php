<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2014, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

/**
 * Command-line options parsing class.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Andrei Zmievski <andrei@php.net>
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Util_Getopt
{
    public static function getopt(array $args, $short_options, $long_options = NULL)
    {
        if (empty($args)) {
            return array(array(), array());
        }

        $opts     = array();
        $non_opts = array();

        if ($long_options) {
            sort($long_options);
        }

        if (isset($args[0][0]) && $args[0][0] != '-') {
            array_shift($args);
        }

        reset($args);
        array_map('trim', $args);

        while (false !== $arg = current($args)) {
            $i = key($args);
            next($args);
            if ($arg == '') {
                continue;
            }

            if ($arg == '--') {
                $non_opts = array_merge($non_opts, array_slice($args, $i + 1));
                break;
            }

            if ($arg[0] != '-' ||
                (strlen($arg) > 1 && $arg[1] == '-' && !$long_options)
            ) {
                $non_opts[] = $args[$i];
                continue;
            } elseif (strlen($arg) > 1 && $arg[1] == '-') {
                self::parseLongOption(
                    substr($arg, 2),
                    $long_options,
                    $opts,
                    $args
                );
            } else {
                self::parseShortOption(
                    substr($arg, 1),
                    $short_options,
                    $opts,
                    $args
                );
            }
        }

        return array($opts, $non_opts);
    }

    protected static function parseShortOption($arg, $short_options, &$opts, &$args)
    {
        $argLen = strlen($arg);

        for ($i = 0; $i < $argLen; $i++) {
            $opt     = $arg[$i];
            $opt_arg = NULL;

            if (($spec = strstr($short_options, $opt)) === FALSE ||
                $arg[$i] == ':'
            ) {
                throw new PHPUnit_Framework_Exception(
                  "unrecognized option -- $opt"
                );
            }

            if (strlen($spec) > 1 && $spec[1] == ':') {
                if ($i + 1 < $argLen) {
                    $opts[] = array($opt, substr($arg, $i + 1));
                    break;
                }
                if (!(strlen($spec) > 2 && $spec[2] == ':')) {
                    if (false === $opt_arg = current($args)) {
                        throw new PHPUnit_Framework_Exception(
                          "option requires an argument -- $opt"
                        );
                    }
                    next($args);
                }
            }

            $opts[] = array($opt, $opt_arg);
        }
    }

    protected static function parseLongOption($arg, $long_options, &$opts, &$args)
    {
        $count   = count($long_options);
        $list    = explode('=', $arg);
        $opt     = $list[0];
        $opt_arg = NULL;

        if (count($list) > 1) {
            $opt_arg = $list[1];
        }

        $opt_len = strlen($opt);

        for ($i = 0; $i < $count; $i++) {
            $long_opt  = $long_options[$i];
            $opt_start = substr($long_opt, 0, $opt_len);

            if ($opt_start != $opt) {
                continue;
            }

            $opt_rest = substr($long_opt, $opt_len);

            if ($opt_rest != '' && $opt[0] != '=' && $i + 1 < $count &&
                $opt == substr($long_options[$i+1], 0, $opt_len)) {
                throw new PHPUnit_Framework_Exception(
                  "option --$opt is ambiguous"
                );
            }

            if (substr($long_opt, -1) == '=') {
                if (substr($long_opt, -2) != '==') {
                    if ($opt_arg === null || !strlen($opt_arg)) {
                        if (false === $opt_arg = current($args)) {
                            throw new PHPUnit_Framework_Exception(
                                "option --$opt requires an argument"
                            );
                        }
                        next($args);
                    }
                }
            }

            else if ($opt_arg) {
                throw new PHPUnit_Framework_Exception(
                  "option --$opt doesn't allow an argument"
                );
            }

            $full_option = '--' . preg_replace('/={1,2}$/', '', $long_opt);
            $opts[]      = array($full_option, $opt_arg);

            return;
        }

        throw new PHPUnit_Framework_Exception("unrecognized option --$opt");
    }
}
