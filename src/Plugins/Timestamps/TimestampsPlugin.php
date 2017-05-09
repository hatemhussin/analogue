<?php

namespace Analogue\ORM\Plugins\Timestamps;

use Analogue\ORM\Plugins\AnaloguePlugin;
use Analogue\ORM\System\Wrappers\Factory;
use Carbon\Carbon;
use Analogue\ORM\System\InternallyMappable;

/**
 * Implements the Timestamps support on Analogue Entities.
 */
class TimestampsPlugin extends AnaloguePlugin
{
    /**
     * Register the plugin.
     *
     * @throws \Exception
     *
     * @return void
     */
    public function register()
    {
        $this->manager->registerGlobalEvent('initialized', function ($event, $payload = null) {

            // Cross Compatible Event handling with 5.3
            // TODO : find a replacement event handler
            if (is_null($payload)) {
                $mapper = $event;
            } else {
                $mapper = $payload[0]->mapper;
            }

            $entityMap = $mapper->getEntityMap();

            if ($entityMap->usesTimestamps()) {
                $mapper->registerEvent('creating', function ($event) use ($entityMap) {
                    $entity = $event->entity;
                    $wrappedEntity = $this->getMappable($entity);

                    $createdAtField = $entityMap->getCreatedAtColumn();
                    $updatedAtField = $entityMap->getUpdatedAtColumn();

                    $time = new Carbon();

                    $wrappedEntity->setEntityAttribute($createdAtField, $time);
                    $wrappedEntity->setEntityAttribute($updatedAtField, $time);
                });

                $mapper->registerEvent('updating', function ($event) use ($entityMap) {
                    $entity = $event->entity;
                    $wrappedEntity = $this->getMappable($entity);

                    $updatedAtField = $entityMap->getUpdatedAtColumn();

                    $time = new Carbon();

                    $wrappedEntity->setEntityAttribute($updatedAtField, $time);
                });
            }
        });
    }

    /**
     * Return internally mappable if not mappable
     * 
     * @param  mixed $entity
     * @return InternallyMappable
     */
    protected function getMappable($entity) : InternallyMappable
    {
        if(! $entity instanceof InternallyMappable) {
            $factory = new Factory();
            $wrappedEntity = $factory->make($entity);
            return $wrappedEntity;
        }

        return $entity;
    }

    /**
     * Get custom events provided by the plugin.
     *
     * @return array
     */
    public function getCustomEvents()
    {
        return [];
    }
}
