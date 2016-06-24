<?php

namespace Spatie\Activitylog\Traits;

use Illuminate\Support\Collection;

trait DetectsChanges
{
    protected $oldValues = [];

    protected $newValues = [];

    protected static function bootRemembersOldValues()
    {
        collect(['updated', 'deleted'])->each(function ($eventName) {

            return static::$eventName(function (Model $model) {
                $model->oldValues = $model->fresh()->toArray();

                $model->newValues = $model->getDirty();
            });
        });
    }

    public function getChangedAttributeNames(): array
    {
        return array_keys(array_intersect_key($this->oldValues, $this->newValues));
    }

    public function getChangedValues(): Collection
    {
        if (!isset(static::$logChangesOnAttributes)) {
            return collect();
        }

        return collect($this->getChangedAttributeNames())
            ->filter(function (string $attributeName) {
               return collect(static::$logChangesOnAttributes)->contains($attributeName);
            })
            ->map(function (string $changedAttributeName) {
                return [
                    'old' => $this->oldValues[$changedAttributeName],
                    'new' => $this->newValues[$changedAttributeName],
                ];
            });
    }
}