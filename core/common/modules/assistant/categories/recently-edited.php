<?php

namespace Elementor\Core\Common\Modules\Assistant\Categories;

use Elementor\Core\Base\Document;
use Elementor\Core\Common\Modules\Assistant\Base_Category;
use Elementor\Plugin;
use Elementor\TemplateLibrary\Source_Local;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Recently_Edited extends Base_Category {

	public function get_title() {
		return __( 'Recently Edited', 'elementor' );
	}

	public function is_remote() {
		return true;
	}

	public function get_category_items( array $options = [] ) {
		$post_types = get_post_types( [
			'exclude_from_search' => false,
		] );

		$post_types[] = Source_Local::CPT;

		$template_types = Source_Local::get_template_types();

		unset( $template_types['widget'] );

		$recently_edited_query_args = [
			'post_type' => $post_types,
			'post_status' => [ 'publish', 'draft' ],
			'posts_per_page' => '5',
			'meta_query' => [
				[
					'key' => '_elementor_edit_mode',
					'value' => 'builder',
				],
				[
					'relation' => 'or',
					[
						'key' => Document::TYPE_META_KEY,
						'compare' => 'NOT EXISTS',
					],
					[
						'key' => Document::TYPE_META_KEY,
						'value' => $template_types,
					],
				],
			],
			'orderby' => 'modified',
			's' => $options['filter'],
		];

		$recently_edited_query = new \WP_Query( $recently_edited_query_args );

		$posts = [];

		/** @var \WP_Post $post */
		foreach ( $recently_edited_query->posts as $post ) {
			$document = Plugin::$instance->documents->get( $post->ID );

			if ( ! $document ) {
				continue;
			}

			$is_template = Source_Local::CPT === $post->post_type;

			$description = $document->get_title();

			$icon = 'document-file';

			if ( $is_template ) {
				$description = __( 'Template', 'elementor' ) . ' / ' . $description;

				$icon = 'post-title';
			}

			$posts[] = [
				'icon' => $icon,
				'title' => $post->post_title,
				'description' => $description,
				'link' => $document->get_edit_url(),
			];
		}

		return $posts;
	}
}
