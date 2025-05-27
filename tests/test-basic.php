<?php
class Basic_Tests extends WP_UnitTestCase {
  public function test_parser_exists() {
    $this->assertTrue( class_exists( 'ReSpis\\Core\\Parser' ) );
  }
}
