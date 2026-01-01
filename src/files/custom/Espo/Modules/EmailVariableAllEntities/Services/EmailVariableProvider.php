<?php

namespace Espo\Modules\EmailVariableAllEntities\Services;

use Espo\Core\Injectable;

/**
 * Service to provide email variable support for all entities
 */
class EmailVariableProvider extends Injectable
{
    /**
     * Check if entity supports email variables
     *
     * @param string $entityType
     * @return bool
     */
    public function supportsEmailVariables(string $entityType): bool
    {
        // Allow all entity types to support email variables
        return true;
    }

    /**
     * Get available fields for email variables
     *
     * @param string $entityType
     * @return array
     */
    public function getAvailableFields(string $entityType): array
    {
        $metadata = $this->getInjection('metadata');
        $fieldDefs = $metadata->get(['entityDefs', $entityType, 'fields'], []);

        $availableFields = [];

        foreach ($fieldDefs as $field => $defs) {
            $type = $defs['type'] ?? null;

            // Include personName fields and other relevant field types
            if (in_array($type, ['personName', 'varchar', 'text', 'email', 'phone', 'url'])) {
                $availableFields[] = $field;
            }
        }

        return $availableFields;
    }
}
