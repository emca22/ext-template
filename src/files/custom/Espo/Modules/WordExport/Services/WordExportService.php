<?php

namespace Espo\Modules\WordExport\Services;

use Espo\Core\Di;
use Espo\Modules\WordExport\Tools\TemplateParser;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class WordExportService implements
    Di\EntityManagerAware,
    Di\MetadataAware,
    Di\ConfigAware
{
    use Di\EntityManagerSetter;
    use Di\MetadataSetter;
    use Di\ConfigSetter;

    /**
     * Export entity to Word document
     */
    public function export(
        string $entityType,
        string $id,
        ?string $templateId,
        array $relatedEntities = []
    ): array {
        // Fetch the main entity
        $entity = $this->entityManager->getEntity($entityType, $id);
        if (!$entity) {
            throw new \Exception("Entity not found: {$entityType} {$id}");
        }

        // Get template
        $template = null;
        if ($templateId) {
            $template = $this->entityManager->getEntity('WordTemplate', $templateId);
            if (!$template) {
                throw new \Exception("Template not found: {$templateId}");
            }
        }

        // Build data array
        $data = $this->buildDataArray($entity, $relatedEntities);

        // Generate document
        $phpWord = new PhpWord();

        if ($template && $template->get('content')) {
            // Parse template with data
            $parser = new TemplateParser($data);
            $parsedContent = $parser->parse($template->get('content'));

            // Add parsed content to document
            $section = $phpWord->addSection();
            $this->addContentToSection($section, $parsedContent);
        } else {
            // Generate default document
            $section = $phpWord->addSection();
            $section->addTitle($entity->get('name') ?? $entityType, 1);

            // Add entity fields
            foreach ($data as $key => $value) {
                if (!is_array($value) && !is_object($value)) {
                    $section->addText(
                        $this->formatFieldName($key) . ': ' . ($value ?? 'N/A')
                    );
                }
            }

            // Add related entities
            foreach ($relatedEntities as $relationName) {
                if (isset($data[$relationName]) && is_array($data[$relationName])) {
                    $section->addTextBreak();
                    $section->addTitle($this->formatFieldName($relationName), 2);

                    foreach ($data[$relationName] as $relatedItem) {
                        if (is_array($relatedItem)) {
                            $section->addTextBreak();
                            foreach ($relatedItem as $k => $v) {
                                if (!is_array($v) && !is_object($v)) {
                                    $section->addText(
                                        '  ' . $this->formatFieldName($k) . ': ' . ($v ?? 'N/A')
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        // Generate filename
        $filename = $this->generateFilename($entity, $template);

        // Save to string
        $tempFile = tempnam(sys_get_temp_dir(), 'word_export_');
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        return [
            'filename' => $filename,
            'content' => $content
        ];
    }

    /**
     * Get templates for a specific entity type
     */
    public function getTemplatesForEntity(string $entityType): array
    {
        $templates = $this->entityManager
            ->getRDBRepository('WordTemplate')
            ->where([
                'entityType' => $entityType,
                'isActive' => true
            ])
            ->find();

        $result = [];
        foreach ($templates as $template) {
            $result[] = [
                'id' => $template->getId(),
                'name' => $template->get('name'),
                'description' => $template->get('description')
            ];
        }

        return $result;
    }

    /**
     * Build data array from entity and related entities
     */
    private function buildDataArray($entity, array $relatedEntities): array
    {
        $data = [];

        // Get all entity attributes
        $attributes = $entity->getAttributeList();
        foreach ($attributes as $attribute) {
            $value = $entity->get($attribute);

            // Format dates
            if ($value instanceof \DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }

            $data[$attribute] = $value;
        }

        // Add related entities
        foreach ($relatedEntities as $relationName) {
            $relatedCollection = $entity->get($relationName);

            if ($relatedCollection && method_exists($relatedCollection, 'count')) {
                $relatedData = [];
                foreach ($relatedCollection as $relatedEntity) {
                    $relatedItem = [];
                    $relatedAttributes = $relatedEntity->getAttributeList();

                    foreach ($relatedAttributes as $attr) {
                        $val = $relatedEntity->get($attr);
                        if ($val instanceof \DateTime) {
                            $val = $val->format('Y-m-d H:i:s');
                        }
                        $relatedItem[$attr] = $val;
                    }

                    $relatedData[] = $relatedItem;
                }

                $data[$relationName] = $relatedData;
            }
        }

        return $data;
    }

    /**
     * Add content to section with basic formatting
     */
    private function addContentToSection($section, string $content): void
    {
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                $section->addTextBreak();
                continue;
            }

            // Simple markdown-style headers
            if (preg_match('/^#\s+(.+)$/', $line, $matches)) {
                $section->addTitle($matches[1], 1);
            } elseif (preg_match('/^##\s+(.+)$/', $line, $matches)) {
                $section->addTitle($matches[1], 2);
            } elseif (preg_match('/^###\s+(.+)$/', $line, $matches)) {
                $section->addTitle($matches[1], 3);
            } else {
                // Check for bold text **text**
                if (preg_match_all('/\*\*([^*]+)\*\*/', $line, $matches)) {
                    $textRun = $section->addTextRun();
                    $lastPos = 0;
                    foreach ($matches[0] as $i => $fullMatch) {
                        $pos = strpos($line, $fullMatch, $lastPos);
                        if ($pos > $lastPos) {
                            $textRun->addText(substr($line, $lastPos, $pos - $lastPos));
                        }
                        $textRun->addText($matches[1][$i], ['bold' => true]);
                        $lastPos = $pos + strlen($fullMatch);
                    }
                    if ($lastPos < strlen($line)) {
                        $textRun->addText(substr($line, $lastPos));
                    }
                } else {
                    $section->addText($line);
                }
            }
        }
    }

    /**
     * Format field name for display
     */
    private function formatFieldName(string $fieldName): string
    {
        // Convert camelCase to Title Case
        $formatted = preg_replace('/([a-z])([A-Z])/', '$1 $2', $fieldName);
        return ucwords(str_replace('_', ' ', $formatted));
    }

    /**
     * Generate filename for the export
     */
    private function generateFilename($entity, $template = null): string
    {
        $name = $entity->get('name') ?? $entity->getEntityType();
        $templateName = $template ? $template->get('name') : 'Export';

        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
        $safeTemplateName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $templateName);

        return "{$safeName}_{$safeTemplateName}_" . date('Y-m-d') . ".docx";
    }
}
