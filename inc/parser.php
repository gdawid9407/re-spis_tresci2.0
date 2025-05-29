<?php
declare(strict_types=1);

namespace Unitoc\Core;

use DOMDocument;
use DOMElement;
use DOMXPath;

final class Parser
{
    /** @var array<int, array{level:int,id:string,text:string}> */
    private static array $headings = [];

    public static function init(): void
    {
        add_filter('the_content', [self::class, 'filterContent'], 8);
        add_action('wp_enqueue_scripts', [self::class, 'enqueueDynamicScript']);
    }
    
    public static function enqueueDynamicScript(): void
    {
    wp_enqueue_script(
        'unitoc-dynamic-headings',
        plugin_dir_url(__FILE__) . 'assets/js/dynamic-headings.js',
        ['jquery'],
        '1.0',
        true
    );
    }

    public static function filterContent(string $content): string
    {
        if (!is_singular() || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        self::clearHeadings();
        libxml_use_internal_errors(true);

        $doc = new DOMDocument();
        $wrapper = '<?xml encoding="UTF-8"><div>' . $content . '</div>';
        if ($doc->loadHTML($wrapper) === false) {
        libxml_clear_errors();
        return $content;
    }

    $xpath = new DOMXPath($doc);

        $xpath = new DOMXPath($doc);
        $nodes = $xpath->query('//h1|//h2|//h3|//h4');
        if ($nodes === false) {
            libxml_clear_errors();
            return $content;
        }

        $usedSlugs = [];
        /** @var DOMElement $node */
        foreach ($nodes as $node) {
            $text = trim($node->textContent);
            if ($text === '') {
                continue; // skip empty headings
            }

            $base = $node->hasAttribute('id')
                ? $node->getAttribute('id')
                : sanitize_title_with_dashes($text);
            $slug = self::getUniqueSlug($base, $usedSlugs);
            $slug = apply_filters('unitoc_heading_id', $slug, $text);

            $node->setAttribute('id', $slug);
            self::$headings[] = [
                'level' => (int) substr($node->nodeName, 1),
                'id'    => $slug,
                'text'  => $text,
            ];
        }

        $body = $doc->getElementsByTagName('body')->item(0);
        $inner = '';
        if ($body instanceof DOMElement && $body->firstChild !== null) {
            $wrapper = $body->firstChild;
            foreach ($wrapper->childNodes as $child) {
                $inner .= $doc->saveHTML($child);
            }
        }

        libxml_clear_errors();
        return $inner ?: $content;
    }

    private static function getUniqueSlug(string $base, array &$usedSlugs): string
    {
        $slug = $base;
        $i = 1;
        while (isset($usedSlugs[$slug])) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        $usedSlugs[$slug] = true;
        return $slug;
    }

    public static function getHeadings(): array
    {
        return self::$headings;
    }

    public static function clearHeadings(): void
    {
        self::$headings = [];
    }
}

Parser::init();
