<?php

namespace Varhall\Dbino\Plugins;

use Varhall\Dbino\Model;

abstract class Plugin
{
    public abstract function register(Model $model);

}