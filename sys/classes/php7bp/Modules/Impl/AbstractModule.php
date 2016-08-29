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

namespace php7bp\Modules\Impl;

/**
 * A non required (but recommended) base class for a module.
 *
 * @package php7bp\Modules\Impl
 *
 * @author Marcel Joachim Kloubert <marcel.kloubert@gmx.net>
 */
abstract class AbstractModule {
    /**
     * @var \php7bp\Modules\Context
     */
    protected $_context;

    /**
     * Gets the current context.
     *
     * @return \php7bp\Modules\Context The context.
     */
    public function getContext() {
        return $this->_context;
    }

    /**
     * Sets the context.
     *
     * @param mixed $ctx The new context.
     */
    protected function setContext($ctx) {
        $this->_context = $ctx;
    }
}
