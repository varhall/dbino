<?php

function dump(...$args)
{
    foreach ($args as $arg) {
        var_dump($arg);
    }
}

function dumpe(...$args) {
    dump(...$args);
    die();
}
