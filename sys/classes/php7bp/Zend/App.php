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

namespace php7bp\Zend;

/**
 * App class optimized for Zend Framework.
 *
 * @package php7bp\Zend
 *
 * @author Marcel Joachim Kloubert <marcel.kloubert@gmx.net>
 */
class App extends \php7bp\App {
    public function init() {
        $this->moduleClass = "\\php7bp\\Zend\\Modules\\Impl\\Module";
        $this->moduleContextClass = "\\php7bp\\Zend\\Modules\\Context";

        return parent::init();
    }
}
