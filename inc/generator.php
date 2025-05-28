<?php
namespace Unitoc\Core;

final class Generator
{
    /**
     * Generuje hierarchiczny spis treści z obsługą klas CSS i atrybutów ARIA.
     *
     * @param array[] $headings [
     *     ['level' => int, 'id' => string, 'text' => string],
     *     …
     * ]
     * @return string
     */
    public static function generate(array $headings): string
    {
        if (empty($headings)) {
            return '';
        }

        $firstLevel   = (int) $headings[0]['level'];
        $currentLevel = $firstLevel;

        $html  = '<nav class="toc-nav" role="navigation" aria-label="Spis treści">';
        $html .= '<ul class="toc-list toc-level-' . $firstLevel . '" role="list">';

        foreach ($headings as $heading) {
            $level = (int) $heading['level'];
            $id    = htmlspecialchars($heading['id'],   ENT_QUOTES, 'UTF-8');
            $text  = htmlspecialchars($heading['text'], ENT_QUOTES, 'UTF-8');

            if ($level > $currentLevel) {
                for ($i = $currentLevel; $i < $level; $i++) {
                    $html .= '<ul class="toc-list toc-level-' . ($i + 1) . '" role="list">';
                }
            } elseif ($level < $currentLevel) {
                for ($i = $currentLevel; $i > $level; $i--) {
                    $html .= '</li></ul>';
                }
                $html .= '</li>';
            } else {
                $html .= '</li>';
            }

            $html .= '<li class="toc-item toc-level-' . $level . '" role="listitem">';
            $html .= '<a href="#' . $id . '" class="toc-link">' . $text . '</a>';

            $currentLevel = $level;
        }

        for ($i = $currentLevel; $i > $firstLevel; $i--) {
            $html .= '</li></ul>';
        }

        $html .= '</li></ul>';
        $html .= '</nav>';

        return $html;
    }
}

