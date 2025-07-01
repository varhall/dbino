<?php

namespace Varhall\Dbino\Traits;

trait Events
{
    private array $events = [];

    public function on($event, callable $callback): self
    {
        if (!array_key_exists($event, $this->events)) {
            $this->events[$event] = [];
        }

        $this->events[$event][] = $callback;

        return $this;
    }


    protected function raise(string $event, ...$arguments): self
    {
        if (!array_key_exists($event, $this->events)) {
            return $this;
        }

        foreach ($this->events[$event] as $e) {
            if (is_callable($e)) {
                call_user_func_array($e, $arguments);
            }
        }

        return $this;
    }
}