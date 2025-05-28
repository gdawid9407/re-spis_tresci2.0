<?php
/**
 * Parser nagłówków H1–H4 dla wtyczki „Universal Table of Contents” (UniTOC).
 *
 * Zadanie plan-krok 4 — pełna implementacja punktów 4.1-4.11.
 *
 * Główne zadania klasy:
 *  • przechwytywać treść pojedynczego wpisu (filtr `the_content`);
 *  • wyszukiwać nagłówki <h1>–<h4>, nadawać im unikalny id;
 *  • budować strukturalną tablicę nagłówków, możliwą do wykorzystania
 *    przez komponent bloku/shortcode TOC;
 *  • zagwarantować brak kolizji slugów oraz odporność na puste
 *    nagłówki i błędny HTML.
 *
 * Wymagania niefunkcjonalne:
 *  • brak zależności poza ext-dom i funkcjami WordPress Core;
 *  • maksymalna kompatybilność PHP 8.2 (strict_types);
 *  • PSR-12, PSR-19 (phpdoc);
 *
 * @package   unitoc
 * @author    Rebrandy
 * @copyright 2025 Rebrandy
 * @license   GPL-2.0-or-later
 */

declare(strict_types=1);

// ZMIANA: Zmieniono przestrzeń nazw z ReSpis\Core na Unitoc\Core, zgodnie z planem i specyfikacją.
namespace Unitoc\Core;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Klasa analizująca kod HTML wpisu i budująca wewnętrzną listę nagłówków.
 *
 * @internal
 */
final class Parser
{
    /**
     * Zmapowane nagłówki w kolejności występowania.
     *
     * Każdy element:
     *  • level — poziom nagłówka (1–4),
     *  • id    — wygenerowany slug pełniący funkcję zakotwiczenia,
     *  • text  — oczyszczony tekst nagłówka.
     *
     * @var array<int, array{level:int, id:string, text:string}>
     */
    private static array $headings = [];

    /**
     * Podłącza Parser do cyklu wyświetlania treści.
     *
     * @return void
     */
    public static function init(): void
    {
        add_filter('the_content', [self::class, 'filterContent'], 8);
    }

    /**
     * Przetwarza treść wpisu w celu oznaczenia nagłówków.
     *
     * Uwarunkowania:
     *  • działa wyłącznie na pojedynczym wpisie (`is_singular`)
     *  • tylko w głównym zapytaniu pętli (`is_main_query`)
     *  • tylko we właściwej pętli (`in_the_loop`)
     *
     * @param  string $content surowa treść zwracana przez WordPress
     * @return string zmodyfikowana treść
     */
    public static function filterContent(string $content): string
    {
        if (!is_singular() || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        // ZMIANA: Wywołanie clearHeadings() wewnątrz filterContent()
        // Zapewnia, że każda nowa analiza treści zaczyna z czystą listą nagłówków.
        self::clearHeadings();

        libxml_use_internal_errors(true);

        $doc = new DOMDocument();
        // Zabezpieczenie przed brakującymi tagami HTML w treści
        $doc->loadHTML(
            mb_convert_encoding('<div>' . $content . '</div>', 'HTML-ENTITIES', 'UTF-8')
        );

        $xpath = new DOMXPath($doc);
        $nodes = $xpath->query('//h1|//h2|//h3|//h4');

        if ($nodes === false) {
            libxml_clear_errors();
            return $content;
        }

        $slugs = [];

        /** @var DOMElement $node */
        foreach ($nodes as $node) {
            $text = trim($node->textContent);

            if ($text === '') {
                continue; // pomijamy puste nagłówki
            }

            // Generuj unikalny slug na podstawie tekstu.
            $slugBase = sanitize_title_with_dashes($text);
            $slug = $slugBase;
            $i = 1;

            while (in_array($slug, $slugs, true)) {
                $slug = $slugBase . '-' . $i++;
            }

            $slugs[] = $slug;

            // Dodaj id do drzewa DOM.
            $node->setAttribute('id', $slug);

            // Zapisz nagłówek w strukturze wewnętrznej.
            self::$headings[] = [
                'level' => (int) substr($node->nodeName, 1),
                'id'    => $slug,
                'text'  => $text,
            ];
        }

        // Usuń pomocniczy <div> otaczający treść.
        $body = $doc->getElementsByTagName('body')->item(0);
        $innerHTML = '';

        if ($body instanceof DOMElement && $body->firstChild !== null) {
            // Przechodzimy przez dzieci pierwszego elementu body (czyli naszego <div>)
            foreach ($body->firstChild->childNodes as $child) {
                // saveHTML() zwraca fragment HTML danego węzła
                $innerHTML .= $doc->saveHTML($child);
            }
        }

        libxml_clear_errors();

        // Jeśli $innerHTML jest pusty (np. treść była pusta lub błędna), zwróć oryginalną treść
        return $innerHTML ?: $content;
    }

    /**
     * Zwraca aktualnie zmapowaną listę nagłówków.
     *
     * @return array<int, array{level:int, id:string, text:string}>
     */
    public static function getHeadings(): array
    {
        return self::$headings;
    }

    /**
     * Czyści wewnętrzną listę nagłówków.
     *
     * Ta metoda jest użyteczna głównie w testach jednostkowych,
     * aby zapewnić czysty stan przed każdym testem.
     * W normalnym przepływie produkcyjnym, `filterContent` sam czyści
     * listę nagłówków na początku swojego działania.
     *
     * @internal
     * @return void
     */
    public static function clearHeadings(): void
    {
        self::$headings = [];
    }
}

// Automatyczna rejestracja na hooku WordPress.
// Ta linia powinna być poza klasą, aby Parser::init() zostało wywołane przy ładowaniu wtyczki.
Parser::init();