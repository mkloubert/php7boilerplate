<?php

/**********************************************************************************************************************
 * php7boilerplate (https://github.com/mkloubert/php7boilerplate)                                                     *
 *                                                                                                                    *
 * Copyright (c) 2015, Marcel Joachim Kloubert <marcel.kloubert@gmx.net>                                              *
 * All rights reserved.                                                                                               *
 *                                                                                                                    *
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the   *
 * following conditions are met:                                                                                      *
 *                                                                                                                    *
 * 1. Redistributions of source code must retain the above copyright notice, this list of conditions and the          *
 *    following disclaimer.                                                                                           *
 *                                                                                                                    *
 * 2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the       *
 *    following disclaimer in the documentation and/or other materials provided with the distribution.                *
 *                                                                                                                    *
 * 3. Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote    *
 *    products derived from this software without specific prior written permission.                                  *
 *                                                                                                                    *
 *                                                                                                                    *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, *
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE  *
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, *
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR    *
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,  *
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE   *
 * USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.                                           *
 *                                                                                                                    *
 **********************************************************************************************************************/

defined('PHP7BP_INDEX') or die();

define('PHP7BP_BOOTSTRAP', true, false);


// directories
define('PHP7BP_DIR_ROOT'   , realpath(__DIR__) . DIRECTORY_SEPARATOR            , false);
define('PHP7BP_DIR_SYSTEM' , PHP7BP_DIR_ROOT . 'sys' . DIRECTORY_SEPARATOR      , false);
define('PHP7BP_DIR_CLASSES', PHP7BP_DIR_SYSTEM . 'classes' . DIRECTORY_SEPARATOR, false);


// update include paths
set_include_path(get_include_path() .
                 PATH_SEPARATOR . PHP7BP_DIR_CLASSES);


// autoloader
spl_autoload_register(function($className) {
    $classFile = realpath(PHP7BP_DIR_CLASSES .
                          str_replace("\\", DIRECTORY_SEPARATOR, $className) .
                          '.php');

    if (false !== $classFile) {
        require_once $classFile;
    }
});


// shutdown
register_shutdown_function(function() {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'shutdown.php';
});
