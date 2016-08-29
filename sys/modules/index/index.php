<?php

namespace php7bp\Zend\Modules\Impl;

class Module extends AbstractModule {
    public function get() {
        // echo 'GETT';
        // return [1, 2, 3];

        echo get_class($this->getContext()) . ': ' . $this->getContext()->time->format('d.m.Y H:i:s');
    }

    public function post() {
        echo 'Post';
    }

    public function request() {
        echo 'Request';
    }

    public function add_Action() {
        echo 'Hello from Add_Action!';
    }
}
