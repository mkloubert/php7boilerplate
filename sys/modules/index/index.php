<?php

namespace php7bp\Modules\Impl;

class Module extends AbstractModule {
    public function get() {
        // echo 'GETT';
        // return [1, 2, 3];

        echo $this->getContext()->time->format('d.m.Y H:i:s');
    }

    public function post() {
        echo 'Post';
    }



    public function request() {
        // echo 'Request';
    }
}
