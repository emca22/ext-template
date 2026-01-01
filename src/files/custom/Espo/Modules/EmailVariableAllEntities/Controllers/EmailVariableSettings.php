<?php

namespace Espo\Modules\EmailVariableAllEntities\Controllers;

use Espo\Core\Api\Request;
use Espo\Core\Controllers\Record;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;

class EmailVariableSettings extends Record
{
    /**
     * Get list of entities and their email variable status
     */
    public function getActionList(Request $request): array
    {
        if (!$this->getUser()->isAdmin()) {
            throw new Forbidden();
        }

        $metadata = $this->getMetadata();
        $entityList = [];
        $scopes = $metadata->get(['scopes']) ?? [];
        $enabledEntities = $metadata->get(['app', 'emailVariableEntities', 'emailVariableEnabled'], []);

        foreach ($scopes as $entityType => $scopeDefs) {
            // Skip system entities and those that shouldn't have custom fields
            if (
                !empty($scopeDefs['entity']) &&
                empty($scopeDefs['disabled']) &&
                !in_array($entityType, ['Attachment', 'Import', 'LayoutRecord', 'LayoutSet'])
            ) {
                $entityList[] = [
                    'name' => $entityType,
                    'label' => $metadata->get(['entityDefs', $entityType, 'labels', 'scopeName']) ?? $entityType,
                    'emailVariableEnabled' => $enabledEntities[$entityType] ?? false,
                ];
            }
        }

        // Sort by label
        usort($entityList, function ($a, $b) {
            return strcmp($a['label'], $b['label']);
        });

        return [
            'list' => $entityList,
        ];
    }

    /**
     * Update email variable settings for entities
     */
    public function putActionUpdate(Request $request): bool
    {
        if (!$this->getUser()->isAdmin()) {
            throw new Forbidden();
        }

        $data = $request->getParsedBody();

        if (!isset($data->entities) || !is_array($data->entities)) {
            throw new BadRequest('Invalid data provided');
        }

        $enabledEntities = [];
        foreach ($data->entities as $entityType => $enabled) {
            $enabledEntities[$entityType] = (bool) $enabled;
        }

        // Save to custom metadata
        $metadataPath = 'custom/Espo/Custom/Resources/metadata/app/emailVariableEntities.json';
        $metadataContent = [
            'emailVariableEnabled' => $enabledEntities,
        ];

        $fileManager = $this->getFileManager();
        $fileManager->putContentsJson($metadataPath, $metadataContent);

        return true;
    }
}
