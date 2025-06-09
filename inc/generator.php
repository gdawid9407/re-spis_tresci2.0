<?php
namespace Unitoc\Core;

use Unitoc\Core\Cache;

final class Generator
{
    /**
     * Generuje hierarchiczny spis treści.
     *
     * @param array  $headings
     * @param int    $depth       Maksymalna głębokość.
     * @param string $list_class  Dodatkowa klasa UL (jeśli tryb default).
     *
     * @return string
     */
    public static function generate(array $headings, int $depth = PHP_INT_MAX, string $list_class = ''): string
    {
        if (!$headings) {
            return '';
        }

        $use_theme = get_option('unitoc_style_source', 'default') === 'theme';

        $firstLevel   = (int) $headings[0]['level'];
        $currentLevel = $firstLevel;

        // filtruj wg głębokości
        $headings = array_filter(
            $headings,
            static fn(array $h): bool => ((int) $h['level'] - $firstLevel + 1) <= $depth
        );
        if (!$headings) {
            return '';
        }

        /* ----- atrybuty ----- */
        $ul_class_base = $use_theme ? '' : 'toc-list';
        if (!$use_theme && $list_class) {
            $ul_class_base .= ' ' . $list_class;
        }

        // otwarcie <nav>
        $html = $use_theme
            ? '<nav role="navigation" aria-label="' . esc_attr__('Spis treści', 're-spis-tresci') . '">'
            : '<nav class="toc-nav" role="navigation" aria-label="' . esc_attr__('Spis treści', 're-spis-tresci') . '">';

        // otwarcie głównego UL
        $html .= $use_theme
            ? '<ul role="list">'
            : '<ul class="' . esc_attr(trim($ul_class_base) . ' toc-level-' . $firstLevel) . '" role="list">';

        /* ----- cache ----- */
        $post_id = get_the_ID();
        $hash    = md5(serialize($headings) . $depth . $list_class . (int)$use_theme);
        if ($post_id && ($cached = Cache::get($post_id, $hash))) {
            return $cached;
        }

        $is_first_li = true;

        foreach ($headings as $heading) {
            $level = (int) $heading['level'];
            $id    = htmlspecialchars($heading['id'], ENT_QUOTES, 'UTF-8');
            $text  = htmlspecialchars($heading['text'], ENT_QUOTES, 'UTF-8');

            // w górę / w dół w hierarchii
            if ($level > $currentLevel) {
                for ($i = $currentLevel; $i < $level; ++$i) {
                    $html .= $use_theme
                        ? '<ul role="list">'
                        : '<ul class="' . esc_attr(trim($ul_class_base) . ' toc-level-' . ($i + 1)) . '" role="list">';
                }
            } elseif ($level < $currentLevel) {
                for ($i = $currentLevel; $i > $level; --$i) {
                    $html .= '</li></ul>';
                }
                $html .= '</li>';
            } elseif (!$is_first_li) {
                $html .= '</li>';
            }

            // LI + link
            if ($use_theme) {
                $html .= '<li role="listitem"><a href="#' . $id . '">' . $text . '</a>';
            } else {
                $html .= '<li class="toc-item toc-level-' . $level . '" role="listitem"><a href="#' . $id . '" class="toc-link">' . $text . '</a>';
            }

            $currentLevel = $level;
            $is_first_li  = false;
        }

        // domknij tagi
        for ($i = $currentLevel; $i >= $firstLevel; --$i) {
            $html .= '</li>';
            if ($i > $firstLevel) {
                $html .= '</ul>';
            }
        }
        $html .= '</ul></nav>';

        if ($post_id) {
            Cache::set($post_id, $hash, $html);
        }

        return $html;
    }
}
