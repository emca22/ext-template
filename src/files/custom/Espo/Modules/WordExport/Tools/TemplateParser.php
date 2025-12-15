<?php

namespace Espo\Modules\WordExport\Tools;

/**
 * Template parser with conditional logic support
 *
 * Syntax:
 * - Variables: {{fieldName}}
 * - Related entities: {{relatedEntity.fieldName}}
 * - Conditionals: {{#if fieldName}}content{{/if}}
 * - Negation: {{#unless fieldName}}content{{/unless}}
 * - Equality: {{#ifEqual fieldName value}}content{{/ifEqual}}
 * - Loops: {{#each relatedList}}{{name}}{{/each}}
 */
class TemplateParser
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function parse(string $template): string
    {
        // Process conditionals first
        $template = $this->processConditionals($template);

        // Process loops
        $template = $this->processLoops($template);

        // Replace variables
        $template = $this->replaceVariables($template);

        return $template;
    }

    private function processConditionals(string $template): string
    {
        // Process {{#if field}}...{{/if}}
        $template = preg_replace_callback(
            '/\{\{#if\s+([a-zA-Z0-9_.]+)\}\}(.*?)\{\{\/if\}\}/s',
            function ($matches) {
                $field = $matches[1];
                $content = $matches[2];
                $value = $this->getValue($field);
                return $value ? $content : '';
            },
            $template
        );

        // Process {{#unless field}}...{{/unless}}
        $template = preg_replace_callback(
            '/\{\{#unless\s+([a-zA-Z0-9_.]+)\}\}(.*?)\{\{\/unless\}\}/s',
            function ($matches) {
                $field = $matches[1];
                $content = $matches[2];
                $value = $this->getValue($field);
                return !$value ? $content : '';
            },
            $template
        );

        // Process {{#ifEqual field value}}...{{/ifEqual}}
        $template = preg_replace_callback(
            '/\{\{#ifEqual\s+([a-zA-Z0-9_.]+)\s+"([^"]+)"\}\}(.*?)\{\{\/ifEqual\}\}/s',
            function ($matches) {
                $field = $matches[1];
                $compareValue = $matches[2];
                $content = $matches[3];
                $value = $this->getValue($field);
                return $value == $compareValue ? $content : '';
            },
            $template
        );

        return $template;
    }

    private function processLoops(string $template): string
    {
        return preg_replace_callback(
            '/\{\{#each\s+([a-zA-Z0-9_.]+)\}\}(.*?)\{\{\/each\}\}/s',
            function ($matches) {
                $field = $matches[1];
                $itemTemplate = $matches[2];
                $items = $this->getValue($field);

                if (!is_array($items)) {
                    return '';
                }

                $result = '';
                foreach ($items as $item) {
                    $parser = new self(is_array($item) ? $item : ['item' => $item]);
                    $result .= $parser->parse($itemTemplate);
                }

                return $result;
            },
            $template
        );
    }

    private function replaceVariables(string $template): string
    {
        return preg_replace_callback(
            '/\{\{([a-zA-Z0-9_.]+)\}\}/',
            function ($matches) {
                $field = $matches[1];
                $value = $this->getValue($field);
                return $value !== null ? (string)$value : '';
            },
            $template
        );
    }

    private function getValue(string $path)
    {
        $parts = explode('.', $path);
        $value = $this->data;

        foreach ($parts as $part) {
            if (is_array($value) && isset($value[$part])) {
                $value = $value[$part];
            } elseif (is_object($value) && isset($value->$part)) {
                $value = $value->$part;
            } else {
                return null;
            }
        }

        return $value;
    }
}
