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

defined('PHP7BP_INDEX') or die();

define('PHP7BP_DIR_ROOT', realpath(__DIR__)  . DIRECTORY_SEPARATOR);
define('PHP7BP_DIR_SYS', PHP7BP_DIR_ROOT . 'sys' . DIRECTORY_SEPARATOR);
define('PHP7BP_DIR_CLASSES', PHP7BP_DIR_SYS . 'classes' . DIRECTORY_SEPARATOR);
define('PHP7BP_DIR_CONFIG', PHP7BP_DIR_SYS . 'conf' . DIRECTORY_SEPARATOR);
define('PHP7BP_DIR_VENDOR', PHP7BP_DIR_SYS . 'vendor' . DIRECTORY_SEPARATOR);

// boilerplate classes
spl_autoload_register(function($clsName) {
    if ('php7bp' !== $clsName &&
        false === strpos($clsName, "php7bp\\")) {

        // no build in class
        //TODO: make composer compatible
        return;
    }

    $file = realpath(PHP7BP_DIR_CLASSES .
                     str_replace('\\', DIRECTORY_SEPARATOR, $clsName) .
                     '.php');

    if (false !== $file) {
        require_once $file;
    }
});

require(PHP7BP_DIR_VENDOR . 'autoload.php');
