<?php
namespace Unitoc\Core;

use Unitoc\Core\Cache;

final class Generator
{
    /**
     * Generuje hierarchiczny spis treści z obsługą klas CSS i atrybutów ARIA.
     *
     * @param array $headings
     * @param int $depth maksymalna głębokość nagłówków
     * @param string $list_class Opcjonalna klasa CSS dla głównej listy ul.
     * @return string
     */
    public static function generate(array $headings, int $depth = PHP_INT_MAX, string $list_class = ''): string // Dodany parametr $list_class
    {
        if (empty($headings)) {
            return '';
        }

        $firstLevel   = (int) $headings[0]['level'];
        $currentLevel = $firstLevel;

        $headings = array_filter($headings, function(array $h) use ($firstLevel, $depth): bool {
            return ((int) $h['level'] - $firstLevel + 1) <= $depth;
        });
        if (empty($headings)) {
            return '';
        }

        // Dodajemy klasę z opcji do domyślnych klas listy
        $ul_classes = ['toc-list'];
        if (!empty($list_class)) {
            $ul_classes[] = $list_class; // Dodajemy klasę z opcji
        }
        $ul_classes_str = implode(' ', array_unique($ul_classes)); // Unikamy duplikatów

        $html  = '<nav class="toc-nav" role="navigation" aria-label="' . esc_attr__('Spis treści', 're-spis-tresci') . '">';
        // Używamy $ul_classes_str zamiast statycznej klasy
        $html .= '<ul class="' . esc_attr($ul_classes_str . ' toc-level-' . $firstLevel) . '" role="list">';


        $post_id = get_the_ID(); // Pobierz ID posta wewnątrz funkcji, jeśli to możliwe
        $hash   = md5( serialize( $headings ) . $depth . $list_class ); // Dodaj $depth i $list_class do hasha

        // Sprawdź, czy $post_id jest prawidłowe przed użyciem
        if ($post_id) {
            $cached = Cache::get( $post_id, $hash );
            if ( $cached ) {
                return $cached;
            }
        }

        $is_first_li = true; // Flaga do obsługi pierwszego <li>

        foreach ($headings as $heading) {
            $level = (int) $heading['level'];
            $id    = htmlspecialchars($heading['id'],   ENT_QUOTES, 'UTF-8');
            $text  = htmlspecialchars($heading['text'], ENT_QUOTES, 'UTF-8');

            if ($level > $currentLevel) {
                for ($i = $currentLevel; $i < $level; $i++) {
                    // Używamy $ul_classes_str
                    $html .= '<ul class="' . esc_attr($ul_classes_str . ' toc-level-' . ($i + 1)) . '" role="list">';
                }
            } elseif ($level < $currentLevel) {
                for ($i = $currentLevel; $i > $level; $i--) {
                    $html .= '</li></ul>';
                }
                $html .= '</li>'; // Zamknij poprzedni element li
            } elseif (!$is_first_li) { // Jeśli nie jest to pierwszy element na tym samym poziomie
                $html .= '</li>'; // Zamknij poprzedni element li
            }

            $html .= '<li class="toc-item toc-level-' . $level . '" role="listitem">';
            $html .= '<a href="#' . $id . '" class="toc-link">' . $text . '</a>';

            $currentLevel = $level;
            $is_first_li = false; // Już nie jest to pierwszy LI
        }

        // Zamknij pozostałe otwarte tagi
        for ($i = $currentLevel; $i >= $firstLevel; $i--) { // >= aby zamknąć ostatni LI i UL
            $html .= '</li>';
            if ($i > $firstLevel) { // Nie zamykaj głównego UL jeszcze raz, jeśli jest tylko jeden poziom
                $html .= '</ul>';
            }
        }
        if ($firstLevel == $currentLevel && count($headings) == 1) { // Jeśli tylko jeden element i jeden poziom
            // $html .= '</li>'; // Już zamknięte
        } else if ($firstLevel != $currentLevel) {
            // $html .= '</ul>'; // Już zamknięte w pętli
        }


        $html .= '</ul>'; // Zamknięcie głównego ul
        $html .= '</nav>';

        if ($post_id) { // Cache tylko jeśli mamy ID posta
            Cache::set( $post_id, $hash, $html );
        }

        return $html;
    }
}