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

chdir(__DIR__);

define('PHP7BP_INDEX', 1);

require_once './bootstrap.php';

$appConf = php7bp::appConf();

$appClass = '';
if (isset($appConf['class'])) {
    $appClass = trim($appConf['class']);
}

if ('' === $appClass) {
    $appClass = '\php7bp\App';
}

if (class_exists($appClass)) {
    $app = new $appClass();

    $handleException = function(\Exception $ex, string $ctx) use ($app) {
        $errorHandled = false;

        if (method_exists($app, 'onError')) {
            $errorHandled = false !== $app->onError($ex, $ctx);
        }

        if (!$errorHandled) {
            throw $ex;
        }
    };

    $appIsInitialized = true;
    if (method_exists($app, 'init')) {
        try {
            $appIsInitialized = false !== $app->init();
        }
        catch (\Exception $ex) {
            $appIsInitialized = false;

            $handleException($ex, 'init');
        }
    }

    if (!$appIsInitialized) {

    }

    if (method_exists($app, 'run')) {
        try {
            $app->run();
        }
        catch (\Exception $ex) {
            $handleException($ex, 'run');
        }
    }
    else {
        header(':', true, 501);
    }
}
else {
    header(':', true, 501);
}
