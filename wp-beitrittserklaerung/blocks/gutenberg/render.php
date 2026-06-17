<?php
/**
 * Gutenberg-Block Server-Render.
 *
 * @var array<string, mixed> $attributes Block-Attribute.
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo BSE_Form_Renderer::render( $attributes ?? array() );
