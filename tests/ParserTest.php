<?php
declare(strict_types=1);

namespace Unitoc\Core {
    function is_singular(): bool { return \Tests\ParserTestHelper::$singular; }  // poprawiony FQN
    function in_the_loop(): bool { return \Tests\ParserTestHelper::$inLoop; }
    function is_main_query(): bool { return \Tests\ParserTestHelper::$mainQuery; }
}

namespace Tests {
    use PHPUnit\Framework\TestCase;
    use Unitoc\Core\Parser;

    // helper do symulacji warunków WordPress
    final class ParserTestHelper {
        public static bool $singular = true;
        public static bool $inLoop = true;
        public static bool $mainQuery = true;
    }

    // testy Parser::filterContent()
    final class ParserTest extends TestCase
    {
        protected function setUp(): void
        {
            ParserTestHelper::$singular = true;
            ParserTestHelper::$inLoop = true;
            ParserTestHelper::$mainQuery = true;
            Parser::clearHeadings();  // resetuje listę nagłówków
        }

        public function testNoHeadingsLeavesContentUnchanged(): void
        {
            $this->assertSame(
                '<p>Brak nagłówków tutaj.</p>',
                Parser::filterContent('<p>Brak nagłówków tutaj.</p>')
            );
        }

        public function testSlugCollisionsProduceUniqueIds(): void
        {
            $html = '<h2>Test</h2><h2>Test</h2><h2>Test</h2>';
            $output = Parser::filterContent($html);
            $ids = array_column(Parser::getHeadings(), 'id');
            $this->assertEquals(['test', 'test-1', 'test-2'], $ids);
        }

        public function testAddsIdAttributeToHeading(): void
        {
            $html = '<h2>Nagłówek</h2>';
            $output = Parser::filterContent($html);
            $this->assertStringContainsString('id="naglowek"', $output);
            $this->assertSame('naglowek', Parser::getHeadings()[0]['id']);
        }

        public function testFilterContentSkipsOnNonSingular(): void
        {
            ParserTestHelper::$singular = false;
            $this->assertSame('<h1>Nagłówek</h1>', Parser::filterContent('<h1>Nagłówek</h1>'));
        }

        public function testFilterContentSkipsWhenNotInTheLoop(): void
        {
            ParserTestHelper::$inLoop = false;
            $this->assertSame('<h1>Nagłówek</h1>', Parser::filterContent('<h1>Nagłówek</h1>'));
        }

        public function testFilterContentSkipsWhenNotMainQuery(): void
        {
            ParserTestHelper::$mainQuery = false;
            $this->assertSame('<h1>Nagłówek</h1>', Parser::filterContent('<h1>Nagłówek</h1>'));
        }
    }
}
