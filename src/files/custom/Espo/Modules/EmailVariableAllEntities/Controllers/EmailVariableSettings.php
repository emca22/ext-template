<?php

namespace Espo\Modules\EmailVariableAllEntities\Controllers;

use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Controllers\Base;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Metadata;

class EmailVariableSettings extends Base
{
    private Metadata $metadata;
    private Config $config;
    private FileManager $fileManager;

    public function __construct(
        Metadata $metadata,
        Config $config,
        FileManager $fileManager
    ) {
        $this->metadata = $metadata;
        $this->config = $config;
        $this->fileManager = $fileManager;
    }

    /**
     * Get list of entities and their email variable status
     */
    public function getActionList(Request $request): array
    {
        if (!$this->user->isAdmin()) {
            throw new Forbidden();
        }

        $entityList = [];
        $scopes = $this->metadata->get(['scopes']);
        $enabledEntities = $this->metadata->get(['app', 'emailVariableEntities', 'emailVariableEnabled'], []);

        foreach ($scopes as $entityType => $scopeDefs) {
            // Skip system entities and those that shouldn't have custom fields
            if (
                !empty($scopeDefs['entity']) &&
                empty($scopeDefs['disabled']) &&
                !in_array($entityType, ['Attachment', 'Import', 'LayoutRecord', 'LayoutSet'])
            ) {
                $entityList[] = [
                    'name' => $entityType,
                    'label' => $this->metadata->get(['entityDefs', $entityType, 'labels', 'scopeName']) ?? $entityType,
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
        if (!$this->user->isAdmin()) {
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
        $metadata = [
            'emailVariableEnabled' => $enabledEntities,
        ];

        $this->fileManager->putContentsJson($metadataPath, $metadata);

        return true;
    }
}
