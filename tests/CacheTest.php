<?php
namespace Unitoc\Core;

use Unitoc\Core\Generator;
use WP_UnitTestCase;

class CacheTest extends WP_UnitTestCase {

	public function test_cache_hit() {
		$headings = [
			['level' => 2, 'id' => 'a', 'text' => 'A'],
		];

		$html1 = Generator::generate( $headings ); // pierwsze wywołanie – generuje HTML
		$html2 = Generator::generate( $headings ); // drugie wywołanie – powinno zwrócić z cache

		$this->assertSame( $html1, $html2 );
	}
}
