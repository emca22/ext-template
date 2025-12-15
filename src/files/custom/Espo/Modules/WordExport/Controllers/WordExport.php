<?php

namespace Espo\Modules\WordExport\Controllers;

use Espo\Core\Controllers\Base;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Modules\WordExport\Services\WordExportService;

class WordExport extends Base
{
    public function postActionExport(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $entityType = $request->getRouteParam('entityType');
        $id = $request->getRouteParam('id');

        $templateId = $data->templateId ?? null;
        $relatedEntities = $data->relatedEntities ?? [];

        if (!$entityType || !$id) {
            $response->setStatus(400);
            return $response->writeBody(json_encode([
                'error' => 'Missing entityType or id'
            ]));
        }

        /** @var WordExportService $service */
        $service = $this->injectableFactory->create(WordExportService::class);

        try {
            $result = $service->export($entityType, $id, $templateId, $relatedEntities);

            $response->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            $response->setHeader('Content-Disposition', 'attachment; filename="' . $result['filename'] . '"');

            return $response->writeBody($result['content']);
        } catch (\Exception $e) {
            $response->setStatus(500);
            return $response->writeBody(json_encode([
                'error' => $e->getMessage()
            ]));
        }
    }

    public function getActionGetTemplates(Request $request, Response $response): Response
    {
        $entityType = $request->getRouteParam('entityType');

        if (!$entityType) {
            $response->setStatus(400);
            return $response->writeBody(json_encode([
                'error' => 'Missing entityType'
            ]));
        }

        /** @var WordExportService $service */
        $service = $this->injectableFactory->create(WordExportService::class);

        try {
            $templates = $service->getTemplatesForEntity($entityType);

            return $response->writeBody(json_encode([
                'templates' => $templates
            ]));
        } catch (\Exception $e) {
            $response->setStatus(500);
            return $response->writeBody(json_encode([
                'error' => $e->getMessage()
            ]));
        }
    }
}
