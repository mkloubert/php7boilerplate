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

namespace php7bp\Modules;

/**
 * The (execution) context of a module.
 *
 * @package php7bp\Modules
 *
 * @author Marcel Joachim Kloubert <marcel.kloubert@gmx.net>
 */
class Context {
    /**
     * @var array
     */
    public $config;

    /**
     * @var string
     */
    public $dir;

    /**
     * Initializes the module context.
     *
     * @param mixed $app The application instance.
     * @param array $properties The list of initial properties.
     */
    public function init($app, $properties) {
        $cls = new \ReflectionClass(static::class);

        if (isset($properties)) {
            foreach ($properties as $propName => $propValue) {
                if ($cls->hasProperty($propName)) {
                    $cls->getProperty($propName)
                        ->setValue($this, $propValue);
                }
            }
        }
    }

    /**
     * @var string
     */
    public $method;

    /**
     * @var \DateTime
     */
    public $time;
}
