<?php
/**
 * Caches generated TOC HTML in a WordPress transient.
 */
namespace Unitoc\Core;

defined( 'ABSPATH' ) || exit;

final class Cache {

	/** @var string Prefix for transient keys */
	private const PREFIX = 'unitoc_toc_';

	/** @var int Time-to-live for cached HTML */
	private const TTL = 12 * HOUR_IN_SECONDS; // adjust as needed

	/**
	 * Hook into save_post to invalidate cache.
	 */
	public static function init(): void {
		add_action( 'save_post', [ __CLASS__, 'clear_cache' ], 10, 1 );
	}

	/**
	 * Retrieve cached HTML if hash matches.
	 */
	public static function get( int $post_id, string $hash ): ?string {
		$data = get_transient( self::key( $post_id ) );

		if ( ! is_array( $data ) || $data['hash'] !== $hash ) {
			return null; // cache miss
		}
		return $data['html']; // cache hit
	}

	/**
	 * Store HTML and its hash in a transient.
	 */
	public static function set( int $post_id, string $hash, string $html ): void {
		set_transient(
			self::key( $post_id ),
			[
				'hash' => $hash,
				'html' => $html,
			],
			self::TTL
		);
	}

	/**
	 * Clear cached HTML when a post is updated.
	 */
	public static function clear_cache( int $post_id ): void {
		delete_transient( self::key( $post_id ) );
	}

	/**
	 * Build transient key.
	 */
	private static function key( int $post_id ): string {
		return self::PREFIX . $post_id;
	}
}

// Initialise.
Cache::init();
