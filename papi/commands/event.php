<?php

namespace Papi\Commands;

use Papi\Database;

class Event extends Database
{
    /**
     * Construct a new event
     *
     * @return void
     */
    public function __construct(string $model)
    {
        if (! is_dir('events/')) {
            mkdir('events');
        }

        copy('papi/formats/event', "events/$model".'Event'.'.php');

        $contents = file_get_contents("events/$model".'Event'.'.php');
        $contents = str_replace('TempEvent', $model.'Event', $contents);
        $contents = str_replace('TempController', $model.'Controller', $contents);

        file_put_contents("events/$model".'Event'.'.php', $contents);
    }
}
