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

    public $defaultModuleMethod = '';
    public $defaultModuleName = '';
    public $moduleClass = '';
    public $moduleContextClass = '';
    public $moduleNameSeparator = '';
    public $moduleScriptFile = '';
    public $moduleRootDir = '';
    public $moduleVar = '';
    public $setContextMethod = '';

    /**
     * Cleans up a module name.
     *
     * @param string $module The input value.
     *
     * @return string The output value.
     */
    protected function cleanupModuleName($module) {
        $module = \trim(\strtolower($module));

        $module = \str_ireplace(\DIRECTORY_SEPARATOR, $this->moduleNameSeparator, $module);

        $newName = '';

        $strLen = \strlen($module);
        for ($i = 0; $i < $strLen; $i++) {
            $c = $module[$i];
            if (false === \strpos(self::VALID_MODULE_CHARS, $c)) {
                $c = '_';
            }

            $newName .= $c;
        }

        return \trim($newName, $this->moduleNameSeparator);
    }

    /**
     * Extracts the meta data from a module name.
     *
     * @param string $moduleName The cleaned up module name submitted by the remote client.
     *
     * @return array The extracted data.
     */
    protected function getModuleMeta($moduleName) {
        $parts = \explode($this->moduleNameSeparator, $moduleName);
        $parts = \array_map(function($x) {
            return \trim($x);
        }, $parts);
        $parts = \array_filter($parts, function($x) {
            return '' !== $x;
        });
        $partCount = \count($parts);

        $moduleDir = \realpath($this->moduleRootDir . \DIRECTORY_SEPARATOR .
                               \implode(\DIRECTORY_SEPARATOR, $parts));
        if (!\is_dir($moduleDir)) {
            if ($partCount > 0) {
                $moduleAction = \trim(\strtolower($parts[$partCount - 1]));

                $moduleDir = \realpath($this->moduleRootDir . \DIRECTORY_SEPARATOR .
                                       \implode(\DIRECTORY_SEPARATOR,
                                                \array_slice($parts, 0, $partCount - 1)));
            }
        }

        $meta = [
            'dir' => $moduleDir,
        ];

        if (!empty($moduleAction)) {
            $meta['action'] = $moduleAction;
        }

        return $meta;
    }

    /**
     * Returns the unhandled module name submitted by the remote client.
     *
     * @return string|null The module name or (null) if not available.
     */
    protected function getModuleName() {
        return !empty($_REQUEST[$this->moduleVar]) ? $_REQUEST[$this->moduleVar]
                                                   : null;
    }

    /**
     * Initializes the application.
     *
     * @return mixed|bool (false) if failed
     */
    public function init() {
        $appConf = \php7bp::appConf();

        if (isset($appConf['modules'])) {
            // variable that contains the module name
            if (isset($appConf['modules']['var'])) {
                $this->moduleVar = \trim($appConf['modules']['var']);
            }

            // name of default module
            if (isset($appConf['modules']['default'])) {
                $this->defaultModuleName = \trim($appConf['modules']['default']);
            }

            // name of default method
            if (isset($appConf['modules']['method'])) {
                $this->defaultModuleMethod = \trim($appConf['modules']['method']);
            }

            // name of method for setting the instance of the module (execution) context
            if (isset($appConf['modules']['setContext'])) {
                $this->setContextMethod = \trim($appConf['modules']['setContext']);
            }

            // name of the module (execution) context class
            if (isset($appConf['modules']['context'])) {
                if (isset($appConf['modules']['context']['class'])) {
                    $this->moduleContextClass = \trim($appConf['modules']['context']['class']);
                }
            }

            // name of the entry script of a module
            if (isset($appConf['modules']['script'])) {
                $this->moduleScriptFile = \trim($appConf['modules']['script']);
            }
        }

        // set defaults if needed
        if (empty($this->moduleVar)) {
            $this->moduleVar = 'm';
        }
        if (empty($this->defaultModuleName)) {
            $this->defaultModuleName = 'index';
        }
        if (empty($this->defaultModuleMethod)) {
            $this->defaultModuleMethod = 'request';
        }
        if (empty($this->setContextMethod)) {
            $this->setContextMethod = 'setContext';
        }
        if (empty($this->moduleClass)) {
            $this->moduleClass = "\\php7bp\\Modules\\Impl\\Module";
        }
        if (empty($this->moduleScriptFile)) {
            $this->moduleScriptFile = 'index.php';
        }
        if (empty($this->moduleContextClass)) {
            $this->moduleContextClass = "\\php7bp\\Modules\\Context";
        }

        $this->moduleNameSeparator = '/';
        $this->moduleRootDir = \PHP7BP_DIR_SYS . 'modules';
    }

    /**
     * Runs the application.
     */
    public function run() {
        $appConf = \php7bp::appConf();

        // module name
        $moduleName = $this->getModuleName();
        if (empty($moduleName)) {
            // use default
            $moduleName = $this->defaultModuleName;
        }

        $moduleName = $this->cleanupModuleName($moduleName);
        $moduleMeta = $this->getModuleMeta($moduleName);

        $moduleRootDir = $this->moduleRootDir;
        $moduleClassName = $this->moduleClass;
        $moduleContextClassName = $this->moduleContextClass;
        $moduleScriptFile = $this->moduleScriptFile;
        $setContextMethodName = $this->setContextMethod;

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
            $moduleDir = $moduleMeta['dir'];
            if (\is_dir($moduleDir)) {
                $metaFile = \realpath($moduleDir . \DIRECTORY_SEPARATOR . 'meta.json');
                if (\is_file($metaFile)) {
                    $meta = \json_decode($metaFile, true);
                }

                if (!isset($meta)) {
                    $meta = [];
                }

                $moduleConfig = !isset($meta['config']) ? [] : $meta['config'];

                // custom module class name from meta file?
                if (isset($meta['class'])) {
                    $metaModuleClassName = \trim($meta['class']);
                }
                if (!empty($metaModuleClassName)) {
                    $moduleClassName = $metaModuleClassName;
                }

                // custom module class name from meta file?
                if (isset($meta['script'])) {
                    $metaModuleScriptFile = \trim($meta['script']);
                }
                if (!empty($metaModuleScriptFile)) {
                    $moduleScriptFile = $metaModuleScriptFile;
                }

                // custom module context class name from meta file?
                if (isset($meta['context'])) {
                    if (isset($meta['context']['class'])) {
                        $metaModuleContextClass = \trim($meta['context']['class']);
                    }
                }
                if (!empty($metaModuleContextClass)) {
                    $moduleContextClassName = $metaModuleContextClass;
                }

                // custom setContext method from meta file?
                if (isset($meta['methods'])) {
                    if (isset($meta['methods']['setContext'])) {
                        $metaSetContextMethod = \trim($meta['methods']['setContext']);
                    }
                }
                if (!empty($metaSetContextMethod)) {
                    $setContextMethodName = $metaSetContextMethod;
                }

                $moduleFile = \realpath($moduleDir . \DIRECTORY_SEPARATOR . $moduleScriptFile);
                if (\is_file($moduleFile)) {
                    require_once $moduleFile;

                    $resultAction = function() {
                        \header(':', true, 501);
                    };

                    if (\class_exists($moduleClassName)) {
                        $moduleClass = new \ReflectionClass($moduleClassName);

                        if (\class_exists($moduleContextClassName)) {
                            $moduleCtx = new $moduleContextClassName();
                            if (\method_exists($moduleCtx, 'init')) {
                                $moduleCtxProperties = [
                                    'config' => $moduleConfig,
                                    'dir' => $moduleDir . \DIRECTORY_SEPARATOR,
                                    'method' => $httpRequestMethod,
                                    'time' => new \DateTime(),
                                ];

                                if (false === $moduleCtx->init($this, $moduleCtxProperties)) {
                                    throw new \Exception('Module context could not be initialized!');
                                }
                            }
                        }

                        if (!empty($moduleMeta['action'])) {
                            // first search for public action method
                            // {lower-case-action-name}_Action()
                            $actionMethodName = $moduleMeta['action'] . '_Action';

                            if ($moduleClass->hasMethod($actionMethodName)) {
                                $actionMethod = $moduleClass->getMethod($actionMethodName);
                                if ($actionMethod->isPublic()) {
                                    // found
                                    $methodNameToExecute = $actionMethodName;
                                }
                            }
                        }

                        if (empty($methodNameToExecute)) {
                            // now try to find
                            // method that has the same name as
                            // the current HTTP request method name (get, post, put, etc.)

                            $httpRequestMethodName = $httpRequestMethod;
                            if ($moduleClass->hasMethod($httpRequestMethodName)) {
                                $httpMethod = $moduleClass->getMethod($httpRequestMethodName);
                                if ($httpMethod->isPublic()) {
                                    $methodNameToExecute = $httpRequestMethodName;
                                }
                            }
                        }

                        if (empty($methodNameToExecute)) {
                            // use default
                            $methodNameToExecute = $this->defaultModuleMethod;
                        }
                        if ($moduleClass->hasMethod($methodNameToExecute)) {
                            $methodToExecute = $moduleClass->getMethod($methodNameToExecute);

                            $module = $moduleClass->newInstance();

                            // non-public
                            // setContext() method
                            if (isset($moduleCtx)) {
                                if ($moduleClass->hasMethod($setContextMethodName)) {
                                    $setContextMethod = $moduleClass->getMethod($setContextMethodName);
                                    if (!$setContextMethod->isPublic()) {
                                        $setContextMethod->setAccessible(true);

                                        $setContextMethod->invoke($module,
                                                                  $moduleCtx);
                                    }
                                }
                            }

                            // $moduleMethod = $moduleClass->getMethod($methodToExecute);

                            $resultAction = function() use ($methodToExecute, $module) {
                                \ob_start();
                                try {
                                    $moduleArgs = [];  //TODO

                                    $moduleResult = $methodToExecute->invokeArgs($module,
                                                                                 $moduleArgs);

                                    $content = \ob_get_contents();
                                }
                                finally {
                                    \ob_end_clean();
                                }

                                // define result content
                                if (!empty($content)) {
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
