<?php

namespace Espo\Modules\EmailVariableAllEntities\Tools\FieldManager;

use Espo\Core\Utils\Metadata;
use Espo\ORM\EntityManager;

/**
 * Hook to check if entity supports email variables before allowing personName fields
 */
class Hook
{
    private Metadata $metadata;
    private EntityManager $entityManager;

    public function __construct(
        Metadata $metadata,
        EntityManager $entityManager
    ) {
        $this->metadata = $metadata;
        $this->entityManager = $entityManager;
    }

    /**
     * Check if a field type is allowed for an entity
     *
     * @param string $entityType
     * @param string $fieldType
     * @return bool
     */
    public function isFieldTypeAllowed(string $entityType, string $fieldType): bool
    {
        if ($fieldType !== 'personName') {
            return true;
        }

        // Check if entity has email variable support enabled
        $enabledEntities = $this->metadata->get(['app', 'emailVariableEntities', 'emailVariableEnabled'], []);

        return $enabledEntities[$entityType] ?? false;
    }

    /**
     * Get available field types for an entity
     *
     * @param string $entityType
     * @param array $fieldTypes
     * @return array
     */
    public function filterFieldTypes(string $entityType, array $fieldTypes): array
    {
        $enabledEntities = $this->metadata->get(['app', 'emailVariableEntities', 'emailVariableEnabled'], []);
        $isEnabled = $enabledEntities[$entityType] ?? false;

        // If personName is not enabled for this entity, remove it from available types
        if (!$isEnabled && isset($fieldTypes['personName'])) {
            unset($fieldTypes['personName']);
        }

        return $fieldTypes;
    }
}
