<?php

namespace App\Managers;

use Illuminate\Database\Eloquent\Model;

abstract class Manager
{
    /**
     * Called when saving an instance of a method with the saved data to be used
     * in some action or mutated.
     */
    abstract public function onSaving(mixed $data): mixed;

    /**
     * Called when a model has been created with an instance of that model and
     * performs some action on it.
     */
    abstract public function onCreated(Model $model): void;
}
