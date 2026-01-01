<?php

namespace Espo\Modules\EmailVariableAllEntities\Hooks\Common;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\ORM\Entity;

/**
 * Hook to enable email variable support for all entities
 */
class EnableEmailVariables implements AfterSave
{
    public function afterSave(Entity $entity, array $options): void
    {
        // This hook ensures that all entities support email variables
        // The actual functionality is handled through metadata definitions
    }
}
