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
     * Add content to section with formatting support
     */
    private function addContentToSection($section, string $content): void
    {
        $lines = explode("\n", $content);
        $i = 0;
        $totalLines = count($lines);

        while ($i < $totalLines) {
            $line = trim($lines[$i]);

            if (empty($line)) {
                $section->addTextBreak();
                $i++;
                continue;
            }

            // Check for table (line starts with |)
            if (preg_match('/^\|/', $line)) {
                $tableLines = [];
                // Collect all consecutive table lines
                while ($i < $totalLines && preg_match('/^\|/', trim($lines[$i]))) {
                    $tableLines[] = trim($lines[$i]);
                    $i++;
                }
                $this->addTable($section, $tableLines);
                continue;
            }

            // Headers
            if (preg_match('/^####\s+(.+)$/', $line, $matches)) {
                $section->addTitle($matches[1], 4);
            } elseif (preg_match('/^###\s+(.+)$/', $line, $matches)) {
                $section->addTitle($matches[1], 3);
            } elseif (preg_match('/^##\s+(.+)$/', $line, $matches)) {
                $section->addTitle($matches[1], 2);
            } elseif (preg_match('/^#\s+(.+)$/', $line, $matches)) {
                $section->addTitle($matches[1], 1);
            }
            // Bullet lists (- or *)
            elseif (preg_match('/^[\-\*]\s+(.+)$/', $line, $matches)) {
                $textRun = $section->addListItemRun(0, null, 'bullet');
                $this->addFormattedText($textRun, $matches[1]);
            }
            // Numbered lists
            elseif (preg_match('/^\d+\.\s+(.+)$/', $line, $matches)) {
                $textRun = $section->addListItemRun(0, null, 'decimal');
                $this->addFormattedText($textRun, $matches[1]);
            }
            // Regular text with inline formatting
            else {
                $textRun = $section->addTextRun();
                $this->addFormattedText($textRun, $line);
            }

            $i++;
        }
    }

    /**
     * Add a table to the section
     */
    private function addTable($section, array $tableLines): void
    {
        if (empty($tableLines)) {
            return;
        }

        // Parse table rows
        $rows = [];
        $isHeaderRow = true;
        $skipNextRow = false;

        foreach ($tableLines as $line) {
            // Skip separator line (|---|---|)
            if (preg_match('/^\|[\s\-:|]+\|$/', $line)) {
                $skipNextRow = false;
                continue;
            }

            // Parse cells
            $cells = array_map('trim', explode('|', trim($line, '|')));

            if (!empty($cells)) {
                $rows[] = [
                    'cells' => $cells,
                    'isHeader' => $isHeaderRow
                ];
                $isHeaderRow = false;
            }
        }

        if (empty($rows)) {
            return;
        }

        // Determine column count
        $colCount = max(array_map(function($row) {
            return count($row['cells']);
        }, $rows));

        // Create table
        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 80
        ];

        $table = $section->addTable($tableStyle);

        // Add rows
        foreach ($rows as $rowData) {
            $table->addRow();

            $cells = $rowData['cells'];
            $isHeader = $rowData['isHeader'];

            // Pad cells if needed
            while (count($cells) < $colCount) {
                $cells[] = '';
            }

            foreach ($cells as $cellText) {
                $cellStyle = ['valign' => 'center'];

                if ($isHeader) {
                    $cellStyle['bgColor'] = 'E7E6E6';
                }

                $cell = $table->addCell(2000, $cellStyle);

                $textRun = $cell->addTextRun();

                if ($isHeader) {
                    $textRun->addText($cellText, ['bold' => true]);
                } else {
                    $this->addFormattedText($textRun, $cellText);
                }
            }
        }

        $section->addTextBreak();
    }

    /**
     * Add text with inline formatting (bold, italic, underline, strikethrough)
     */
    private function addFormattedText($textRun, string $text): void
    {
        // Pattern to match all formatting:
        // ***text*** = bold + italic
        // **text** = bold
        // __text__ = underline
        // *text* or _text_ = italic
        // ~~text~~ = strikethrough

        $patterns = [
            // Bold + Italic (must come before bold and italic)
            '/\*\*\*([^*]+)\*\*\*/' => ['bold' => true, 'italic' => true],
            // Bold
            '/\*\*([^*]+)\*\*/' => ['bold' => true],
            // Underline
            '/__([^_]+)__/' => ['underline' => 'single'],
            // Italic (asterisk)
            '/(?<!\*)\*([^*]+)\*(?!\*)/' => ['italic' => true],
            // Italic (underscore)
            '/(?<!_)_([^_]+)_(?!_)/' => ['italic' => true],
            // Strikethrough
            '/~~([^~]+)~~/' => ['strikethrough' => true],
        ];

        // Find all formatting tokens
        $tokens = [];
        foreach ($patterns as $pattern => $style) {
            if (preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $i => $match) {
                    $tokens[] = [
                        'start' => $match[1],
                        'length' => strlen($match[0]),
                        'text' => $matches[1][$i][0],
                        'style' => $style,
                        'pattern' => $pattern
                    ];
                }
            }
        }

        // Sort tokens by position
        usort($tokens, function($a, $b) {
            return $a['start'] - $b['start'];
        });

        // Remove overlapping tokens (keep the first one found)
        $filteredTokens = [];
        $lastEnd = -1;
        foreach ($tokens as $token) {
            if ($token['start'] >= $lastEnd) {
                $filteredTokens[] = $token;
                $lastEnd = $token['start'] + $token['length'];
            }
        }

        // If no formatting found, just add plain text
        if (empty($filteredTokens)) {
            $textRun->addText($text);
            return;
        }

        // Build text with formatting
        $lastPos = 0;
        foreach ($filteredTokens as $token) {
            // Add plain text before this token
            if ($token['start'] > $lastPos) {
                $plainText = substr($text, $lastPos, $token['start'] - $lastPos);
                $textRun->addText($plainText);
            }

            // Add formatted text
            $textRun->addText($token['text'], $token['style']);

            $lastPos = $token['start'] + $token['length'];
        }

        // Add any remaining plain text
        if ($lastPos < strlen($text)) {
            $textRun->addText(substr($text, $lastPos));
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
