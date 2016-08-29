<?php

/**********************************************************************************************************************
 * php7boilerplate (https://github.com/mkloubert/php7boilerplate)                                                     *
 * Copyright (c) Marcel Joachim Kloubert <marcel.kloubert@gmx.net>, All rights reserved.                              *
 *                                                                                                                    *
 *                                                                                                                    *
 * This software is free software; you can redistribute it and/or                                                     *
 * modify it under the terms of the GNU Lesser General Public                                                         *
 * License as published by the Free Software Foundation; either                                                       *
 * version 3.0 of the License, or (at your option) any later version.                                                 *
 *                                                                                                                    *
 * This software is distributed in the hope that it will be useful,                                                   *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of                                                     *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU                                                  *
 * Lesser General Public License for more details.                                                                    *
 *                                                                                                                    *
 * You should have received a copy of the GNU Lesser General Public                                                   *
 * License along with this software.                                                                                  *
 **********************************************************************************************************************/

/**
 * Provides global services.
 *
 * @author Marcel Joachim Kloubert <marcel.kloubert@gmx.net>
 */
class php7bp {
    /**
     * Default name of a config storage.
     */
    const DEFAULT_CONFIG_NAME = 'main';

    /**
     * @var Zend\Cache\Storage\StorageInterface[]
     */
    // private static $_caches = [];
    /**
     * @var [][]
     */
    private static $_confs = [];
    /**
     * @var Zend\Db\Adapter\Adapter[]
     */
    // private static $_dbs = [];
    protected static $_mysql = [];

    // "static" class
    protected function __construct() { }

    /**
     * Returns a non null array with the application config.
     *
     * @return array|bool The application config or (false) on error.
     */
    public static function appConf() {
        $appConf = self::conf();
        if (null === $appConf) {
            $appConf = [];
        }

        return $appConf;
    }

    /**
     * Returns a cache storage.
     *
     * @param string $name The name of the cache.
     *
     * @return Zend\Cache\Storage\StorageInterface|bool The cache or (false) on error.
     */
    /* public static function cache($name = self::DEFAULT_CONFIG_NAME) {
        $name = 'cache.' . $name;

        if (!isset(self::$_caches[$name])) {
            $cache = false;

            $conf = self::conf($name);
            if (is_array($conf)) {
                $cache = Zend\Cache\StorageFactory::factory($conf);
            }

            self::$_caches[$name] = $cache;
        }

        return self::$_caches[$name];
    } */

    /**
     * Loads config data.
     *
     * @param string $name The name of the storage.
     *
     * @return array|null|bool
     */
    public static function conf(string $name = 'app') {
        $name = (string)$name;

        $file = realpath(PHP7BP_DIR_CONFIG .
                         str_ireplace('.', DIRECTORY_SEPARATOR, $name) .
                         '.json');
        if (false === $file) {
            return null;
        }

        if (!isset(self::$_confs[$file])) {
            self::$_confs[$file] = json_decode(file_get_contents($file),
                                               true);
        }

        return self::$_confs[$file];
    }

    /**
     * Returns a database connection.
     *
     * @param string $name
     *
     * @return Zend\Db\Adapter\Adapter|bool The adapter or (false) on error.
     */
    /* public static function db($name = self::DEFAULT_CONFIG_NAME) {
        $name = 'db.' . $name;

        if (!isset(self::$_dbs[$name])) {
            $db = false;

            $conf = self::conf($name);
            if (is_array($conf)) {
                $db = new Zend\Db\Adapter\Adapter($conf);
            }

            self::$_dbs[$name] = $db;
        }

        return self::$_dbs[$name];
    } */

    /**
     * Returns a Mysqli connection.
     *
     * @param string $name The name of the connection.
     *
     * @return mysqli|bool The connection or (false) on error.
     */
    public static function mysql(string $name = self::DEFAULT_CONFIG_NAME) {
        if (isset(static::$_mysql[$name])) {
            $conf = self::conf('mysql.' . $name);
            if (false === $conf) {
                return false;
            }

            $host = null;
            $username = null;
            $password = null;
            $db = null;
            $port = null;
            $socket = null;
            if (is_array($conf)) {
                if (isset($conf['host'])) {
                    $host = trim($conf['host']);
                }

                if (isset($conf['user'])) {
                    $username = trim($conf['user']);
                }

                if (isset($conf['password'])) {
                    $password = (string)$conf['password'];
                }

                if (isset($conf['db'])) {
                    $db = trim($conf['db']);
                }

                if (isset($conf['port'])) {
                    $port = trim($conf['port']);
                }

                if (isset($conf['socket'])) {
                    $socket = trim($conf['socket']);
                }
            }

            if (empty($host)) {
                $host = null;
            }
            if (empty($user)) {
                $user = null;
            }
            if (empty($password)) {
                $password = null;
            }
            if (empty($db)) {
                $db = null;
            }
            if (empty($port)) {
                $port = null;
            }
            if (empty($socket)) {
                $socket = null;
            }

            static::$_mysql[$name] = new \mysqli(
                $host,
                $username,
                $password,
                $db,
                $port,
                $socket);
        }

        return static::$_mysql[$name];
    }
}
