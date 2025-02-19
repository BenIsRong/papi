<?php

namespace Papi\Traits;

trait Listenable
{
    public function listen()
    {
        $function = debug_backtrace()[1]['function'];
        $model = explode("\\", debug_backtrace()[1]['class']);
        $model = end($model);
        $model = str_replace("Controller", "", $model);
        $event = "\\events\\" . $model . "Event";

        $event::$function($model, $function, $function);
    }

    public function triggers(string $model, string $function = "")
    {
        $callFunction = debug_backtrace()[1]['function'];
        if ($function == "") {
            $function = $callFunction;
        }

        $model = explode("\\", $model);
        $model = end($model);
        $event = "\\events\\" . $model . "Event";

        $event::$function($model, $function, $callFunction);
    }
}
