<?php

namespace Papi\Traits;

trait Listenable
{
    public function listen($callback = null)
    {
        $function = debug_backtrace()[1]['function'];
        $model = explode('\\', debug_backtrace()[1]['class']);
        $model = end($model);
        $model = str_replace('Controller', '', $model);
        $event = '\\events\\'.$model.'Event';

        $event::$function(model: $model, function: $function, callFunction: $function, callback: $callback);
    }

    public function triggers(string $model, string $function = '', $callback = null)
    {
        $callFunction = debug_backtrace()[1]['function'];
        if ($function == '') {
            $function = $callFunction;
        }

        $model = explode('\\', $model);
        $model = end($model);
        $event = '\\events\\'.$model.'Event';

        $event::$function(model: $model, function: $function, callFunction: $function, callback: $callback);
    }
}
