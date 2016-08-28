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

namespace php7bp;

/**
 * App class.
 *
 * @package php7bp
 *
 * @author Marcel Joachim Kloubert <marcel.kloubert@gmx.net>
 */
class App {
    /**
     * Contains the list of valid chars for a module name.
     */
    const VALID_MODULE_CHARS = 'abcdefghijklmnopqrstuvwxyz0123456789/';

    protected $_defaultModuleMethod;
    protected $_defaultModuleName;
    protected $_moduleClass;
    protected $_moduleContextClass;
    protected $_moduleScriptFile;
    protected $_moduleRootDir;
    protected $_moduleVar;
    protected $_setContextMethod;

    /**
     * Cleans up a module name.
     *
     * @param string $module The input value.
     *
     * @return string The output value.
     */
    protected static function cleanupModuleName($module) {
        $module = \trim(\strtolower($module));

        $module = \str_ireplace(\DIRECTORY_SEPARATOR, '/', $module);

        $newName = '';

        $strLen = \strlen($module);
        for ($i = 0; $i < $strLen; $i++) {
            $c = $module[$i];
            if (false === strpos(self::VALID_MODULE_CHARS, $c)) {
                $c = '_';
            }

            $newName .= $c;
        }

        return \trim($newName, '/');
    }

    protected function getModuleMeta($moduleName) {

    }

    /**
     * Initializes the application.
     *
     * @return mixed|bool (false) if failed
     */
    public function init() {
        $appConf = \php7bp::appConf();

        // variable that contains the module name
        $this->_moduleVar = '';
        if (isset($appConf['modules'])) {
            if (isset($appConf['modules']['var'])) {
                $this->_moduleVar = \trim($appConf['modules']['var']);
            }
        }
        if ('' === $this->_moduleVar) {
            $this->_moduleVar = 'm';
        }

        // default module name
        $this->_defaultModuleName = '';
        if (isset($appConf['modules'])) {
            if (isset($appConf['modules']['default'])) {
                $this->_defaultModuleName = \trim($appConf['modules']['default']);
            }
        }
        if ('' === $this->_defaultModuleName) {
            $this->_defaultModuleName = 'index';
        }

        // default module method to execute
        $this->_defaultModuleMethod = '';
        if (isset($appConf['modules'])) {
            if (isset($appConf['modules']['method'])) {
                $this->_defaultModuleMethod = \trim($appConf['modules']['method']);
            }
        }
        if ('' === $this->_defaultModuleMethod) {
            $this->_defaultModuleMethod = 'request';
        }

        $this->_moduleScriptFile = '';
        if ('' === $this->_moduleScriptFile) {
            $this->_moduleScriptFile = 'index.php';
        }

        // method for setting a module context
        $this->_setContextMethod = '';
        if (isset($appConf['modules'])) {
            if (isset($appConf['modules']['setContext'])) {
                $this->_setContextMethod = \trim($appConf['modules']['setContext']);
            }
        }
        if ('' === $this->_setContextMethod) {
            $this->_setContextMethod = 'setContext';
        }

        // name of a module class
        $this->_moduleClass = '';
        if (isset($appConf['modules'])) {
            if (isset($appConf['modules']['class'])) {
                $this->_moduleClass = \trim($appConf['modules']['class']);
            }
        }
        if ('' === $this->_moduleClass) {
            $this->_moduleClass = "\\php7bp\\Modules\\Impl\\Module";
        }

        // name of a module CONTEXT class
        $this->_moduleContextClass = '';
        if (isset($appConf['modules'])) {
            if (isset($appConf['modules']['context'])) {
                $this->_moduleContextClass = \trim($appConf['modules']['context']);
            }
        }
        if ('' === $this->_moduleContextClass) {
            $this->_moduleContextClass = "\\php7bp\\Modules\\Context";
        }

        $this->_moduleRootDir = \PHP7BP_DIR_SYS . 'modules';
    }

    /**
     * Runs the application.
     */
    public function run() {
        $appConf = \php7bp::appConf();

        // module name
        $moduleName = '';
        if (isset($_REQUEST[$this->_moduleVar])) {
            $moduleName = \trim($_REQUEST[$this->_moduleVar]);
        }
        if ('' === $moduleName) {
            // use default
            $moduleName = $this->_defaultModuleName;
        }

        $moduleName = static::cleanupModuleName($moduleName);

        // root directory with modules
        $moduleRootDir = '';
        if (isset($appConf['modules'])) {
            if (isset($appConf['modules']['dir'])) {
                $moduleRootDir = \trim($appConf['modules']['dir']);
            }
        }
        if ('' === $moduleRootDir) {
            $moduleRootDir = $this->_moduleRootDir;
        }

        $moduleClassName = $this->_moduleClass;
        $moduleContextClassName = $this->_moduleContextClass;
        $moduleScriptFile = $this->_moduleScriptFile;
        $setContextMethod = $this->_setContextMethod;

        $resultAction = function() {
            \header(':', true, 404);
        };

        // /ns1/module/action

        // HTTP request method
        $httpRequestMethod = '';
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $httpRequestMethod = \trim(\strtolower($_SERVER['REQUEST_METHOD']));
        }
        if ('' === $httpRequestMethod) {
            $httpRequestMethod = "get";
        }

        $moduleRootDir = \realpath($moduleRootDir);
        if (\is_dir($moduleRootDir)) {
            $moduleDir = \realpath($moduleRootDir . \DIRECTORY_SEPARATOR . $moduleName);
            if (\is_dir($moduleDir)) {
                $moduleFile = \realpath($moduleDir . \DIRECTORY_SEPARATOR . $moduleScriptFile);
                if (\is_file($moduleFile)) {
                    require_once $moduleFile;

                    $resultAction = function() {
                        \header(':', true, 501);
                    };

                    if (\class_exists($moduleClassName)) {
                        $moduleClass = new \ReflectionClass($moduleClassName);

                        if (\class_exists($moduleContextClassName)) {
                            $moduleContextClass = new \ReflectionClass($moduleContextClassName);

                            $moduleCtx = new $moduleContextClassName();

                            // properties of a module context
                            $moduleCtxProperties = [
                                'dir' => $moduleDir . \DIRECTORY_SEPARATOR,
                                'method' => $httpRequestMethod,
                                'time' => new \DateTime(),
                            ];
                            foreach ($moduleCtxProperties as $moduleCtxPropertyName => $moduleCtxPropertyValue) {
                                if ($moduleContextClass->hasProperty($moduleCtxPropertyName)) {
                                    $moduleContextClass->getProperty($moduleCtxPropertyName)
                                                       ->setValue($moduleCtx, $moduleCtxPropertyValue);
                                }
                            }
                        }

                        // check if there is a lower case
                        // method with the same name as the
                        // HTTP request method
                        $methodToExecute = $this->_defaultModuleMethod;
                        if ($moduleClass->hasMethod($httpRequestMethod)) {
                            $methodToExecute = $httpRequestMethod;
                        }

                        if ($moduleClass->hasMethod($methodToExecute)) {
                            $module = new $moduleClassName();

                            if (isset($moduleCtx)) {
                                if ($moduleClass->hasMethod($setContextMethod)) {
                                    $module->$setContextMethod($moduleCtx);
                                }
                            }

                            // $moduleMethod = $moduleClass->getMethod($methodToExecute);

                            $resultAction = function() use ($methodToExecute, $module) {
                                \ob_start();
                                try {
                                    $moduleResult = $module->$methodToExecute();

                                    $content = \ob_get_contents();
                                }
                                finally {
                                    \ob_end_clean();
                                }

                                // define result content
                                if ('' !== $content) {
                                    if (null !== $moduleResult) {
                                        $content .= $moduleResult;
                                    }
                                }
                                else {
                                    $content = $moduleResult;
                                }

                                echo $content;
                            };
                        }
                    }
                }
            }
        }

        if (null !== $resultAction) {
            $resultAction();
        }
    }
}
