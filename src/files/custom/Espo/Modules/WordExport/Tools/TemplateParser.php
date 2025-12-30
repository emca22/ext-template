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

    public function parse(string $template): array
    {
        // Extract sections first
        $sections = $this->extractSections($template);

        // Process main content
        $content = $sections['main'];
        $content = $this->processConditionals($content);
        $content = $this->processLoops($content);
        $content = $this->replaceVariables($content);

        // Process header if exists
        $header = '';
        if (!empty($sections['header'])) {
            $header = $this->processConditionals($sections['header']);
            $header = $this->processLoops($sections['header']);
            $header = $this->replaceVariables($header);
        }

        // Process footer if exists
        $footer = '';
        if (!empty($sections['footer'])) {
            $footer = $this->processConditionals($sections['footer']);
            $footer = $this->processLoops($sections['footer']);
            $footer = $this->replaceVariables($footer);
        }

        return [
            'content' => $content,
            'header' => $header,
            'footer' => $footer
        ];
    }

    private function extractSections(string $template): array
    {
        $sections = [
            'main' => $template,
            'header' => '',
            'footer' => ''
        ];

        // Extract header
        if (preg_match('/\{\{#header\}\}(.*?)\{\{\/header\}\}/s', $template, $matches)) {
            $sections['header'] = trim($matches[1]);
            $sections['main'] = preg_replace('/\{\{#header\}\}.*?\{\{\/header\}\}/s', '', $sections['main']);
        }

        // Extract footer
        if (preg_match('/\{\{#footer\}\}(.*?)\{\{\/footer\}\}/s', $template, $matches)) {
            $sections['footer'] = trim($matches[1]);
            $sections['main'] = preg_replace('/\{\{#footer\}\}.*?\{\{\/footer\}\}/s', '', $sections['main']);
        }

        $sections['main'] = trim($sections['main']);

        return $sections;
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
