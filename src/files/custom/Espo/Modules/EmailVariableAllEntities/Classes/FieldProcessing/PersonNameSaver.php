<?php

namespace Espo\Modules\EmailVariableAllEntities\Classes\FieldProcessing;

use Espo\ORM\Entity;
use Espo\Core\FieldProcessing\Saver;
use Espo\Core\FieldProcessing\Saver\Params;

/**
 * Enables personName field processing for all entities
 */
class PersonNameSaver implements Saver
{
    public function process(Entity $entity, Params $params): void
    {
        // Handle personName fields for all entities
        $fieldDefs = $entity->getAttributeList();

        foreach ($fieldDefs as $field) {
            $fieldType = $entity->getAttributeType($field);

            if ($fieldType === 'personName') {
                // Process personName field
                $this->processPersonNameField($entity, $field);
            }
        }
    }

    private function processPersonNameField(Entity $entity, string $field): void
    {
        // Handle the personName field processing
        // This ensures proper storage and retrieval of person name data
        $salutationName = $field . 'Salutation';
        $firstName = $field . 'First';
        $lastName = $field . 'Last';

        // Build the full name if components are set
        if ($entity->has($firstName) || $entity->has($lastName)) {
            $parts = [];

            if ($entity->get($salutationName)) {
                $parts[] = $entity->get($salutationName);
            }

            if ($entity->get($firstName)) {
                $parts[] = $entity->get($firstName);
            }

            if ($entity->get($lastName)) {
                $parts[] = $entity->get($lastName);
            }

            $entity->set($field, implode(' ', $parts));
        }
    }
}
