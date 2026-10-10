<?php
/**
 * Block registration and rendering.
 *
 * @package Nhrcc\CoreContributions
 */

namespace Nhrcc\CoreContributions;

use Nhrcc\CoreContributions\Sources\Commits;
use Nhrcc\CoreContributions\Sources\Directory;
use Nhrcc\CoreContributions\Sources\Profile;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blocks Class
 *
 * Every block is dynamic: the editor previews it through core's block renderer
 * and the front end gets plain HTML plus one stylesheet, with no script.
 */
class Blocks {

	/**
	 * Block directory => render method. The first name predates 2.0 and must not change.
	 */
	const BLOCKS = [
		'core-contributions-block' => 'render_list',
		'contributor-card'         => 'render_card',
		'contribution-stats'       => 'render_stats',
		'contribution-activity'    => 'render_activity',
		'release-badges'           => 'render_releases',
		'team-badges'              => 'render_badges',
		'directory'                => 'render_directory',
	];

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'register' ] );
	}

	/**
	 * Register the shared assets and every block.
	 *
	 * @return void
	 */
	public function register() {
		$build = NHRCC_PATH . '/assets/blocks/build';
		$asset = file_exists( $build . '/index.asset.php' ) ? require $build . '/index.asset.php' : [
			'dependencies' => [],
			'version'      => NHRCC_VERSION,
		];

		wp_register_script( 'nhrcc-blocks-editor', NHRCC_URL . '/assets/blocks/build/index.js', $asset['dependencies'], $asset['version'], true );
		wp_set_script_translations( 'nhrcc-blocks-editor', 'nhrrob-core-contributions' );
		wp_register_style( 'nhrcc-blocks', NHRCC_URL . '/assets/blocks/build/style-index.css', [], $asset['version'] );
		wp_style_add_data( 'nhrcc-blocks', 'rtl', 'replace' );

		foreach ( self::BLOCKS as $dir => $method ) {
			register_block_type( $build . '/' . $dir, [ 'render_callback' => [ $this, $method ] ] );
		}
	}

	/**
	 * The contributions list (core or Gutenberg).
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render_list( $attributes ) {
		$username = $this->username( $attributes );
		if ( '' === $username ) {
			return $this->notice( __( 'Set a WordPress.org username in the block settings or under Tools → Core Contributions.', 'nhrrob-core-contributions' ) );
		}

		$settings = Settings::get();
		$source   = isset( $attributes['source'] ) && 'gutenberg' === $attributes['source'] ? 'gutenberg' : 'core';
		$param    = 'nhrcc_' . $source . '_page';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a public page number.
		$page = isset( $_GET[ $param ] ) ? max( 1, absint( wp_unslash( $_GET[ $param ] ) ) ) : 1;
		// GitHub rejects a page beyond its last one, so never ask for it.
		$page = min( $page, Commits::pages( Commits::MAX_RESULTS, $settings['postsPerPage'] ) );
		$data = Commits::page( $source, $username, $page, $settings['postsPerPage'], $settings['cacheDuration'] );

		// A page number past this username's last page shows the first page.
		if ( ! is_wp_error( $data ) && $page > 1 && ! $data['items'] ) {
			$page = 1;
			$data = Commits::page( $source, $username, $page, $settings['postsPerPage'], $settings['cacheDuration'] );
		}

		if ( is_wp_error( $data ) ) {
			return $this->notice( $data->get_error_message() );
		}

		$html = '';

		if ( ! isset( $attributes['showHeader'] ) || $attributes['showHeader'] ) {
			$count = 'gutenberg' === $source
				/* translators: %s: number of contributions. */
				? _n( '%s Gutenberg contribution', '%s Gutenberg contributions', $data['total'], 'nhrrob-core-contributions' )
				/* translators: %s: number of contributions. */
				: _n( '%s core contribution', '%s core contributions', $data['total'], 'nhrrob-core-contributions' );
			$html .= $this->header( $username, sprintf( $count, number_format_i18n( $data['total'] ) ) );
		}

		if ( ! $data['items'] ) {
			$html .= '<p class="nhrcc-empty">' . esc_html__( 'No contributions found for this username yet.', 'nhrrob-core-contributions' ) . '</p>';
		} else {
			$html .= '<ol class="nhrcc-items">';
			foreach ( $data['items'] as $item ) {
				$meta = [];
				if ( $item['ticket'] ) {
					$meta[] = '#' . $item['ticket'];
				}
				if ( $item['changeset'] ) {
					$meta[] = '[' . $item['changeset'] . ']';
				}
				if ( '' !== $item['date'] && ( ! isset( $attributes['showDate'] ) || $attributes['showDate'] ) ) {
					$meta[] = wp_date( get_option( 'date_format' ), strtotime( $item['date'] ) );
				}

				$html .= '<li class="nhrcc-item">';
				$html .= '' !== $item['component'] ? '<span class="nhrcc-chip">' . esc_html( $item['component'] ) . '</span>' : '';
				$html .= '<a class="nhrcc-item__title" href="' . esc_url( $item['link'] ) . '">' . esc_html( $item['description'] ) . '</a>';
				$html .= $meta ? '<span class="nhrcc-item__meta">' . esc_html( implode( ' · ', $meta ) ) . '</span>' : '';
				$html .= '</li>';
			}
			$html .= '</ol>';
		}

		$pages = Commits::pages( $data['total'], $settings['postsPerPage'] );
		if ( $pages > 1 && ( ! isset( $attributes['showPagination'] ) || $attributes['showPagination'] ) ) {
			$links = paginate_links(
				[
					'base'         => add_query_arg( $param, '%#%' ),
					'format'       => '',
					'current'      => min( $page, $pages ),
					'total'        => $pages,
					'mid_size'     => 1,
					'add_fragment' => '#nhrcc-' . $source,
				]
			);
			$html .= '<nav class="nhrcc-pagination" aria-label="' . esc_attr__( 'Contributions pagination', 'nhrrob-core-contributions' ) . '">' . wp_kses_post( (string) $links ) . '</nav>';

			if ( $data['total'] > Commits::MAX_RESULTS ) {
				/* translators: 1: number of contributions shown, 2: total number of contributions. */
				$html .= '<p class="nhrcc-note">' . esc_html( sprintf( __( 'Showing the latest %1$s of %2$s contributions.', 'nhrrob-core-contributions' ), number_format_i18n( Commits::MAX_RESULTS ), number_format_i18n( $data['total'] ) ) ) . '</p>';
			}
		}

		// Blocks saved before 2.0 chose their look with a "preset" attribute.
		$class = 'nhrcc-list';
		$named = isset( $attributes['className'] ) && false !== strpos( $attributes['className'], 'is-style-' );
		if ( ! $named && isset( $attributes['preset'] ) && in_array( $attributes['preset'], [ 'minimal', 'timeline' ], true ) ) {
			$class .= ' is-style-' . $attributes['preset'];
		}

		return $this->wrap( $attributes, $class, $html, 'nhrcc-' . $source );
	}

	/**
	 * The contributor card: who, headline numbers, first and latest contribution, top components.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render_card( $attributes ) {
		$username = $this->username( $attributes );
		if ( '' === $username ) {
			return $this->notice( __( 'Set a WordPress.org username in the block settings or under Tools → Core Contributions.', 'nhrrob-core-contributions' ) );
		}

		$summary = Sync::summary( $username );
		$profile = Profile::get( $username );
		$since   = ! is_wp_error( $profile ) && '' !== $profile['since']
			/* translators: %s: date the WordPress.org account was created. */
			? sprintf( __( 'WordPress.org member since %s', 'nhrrob-core-contributions' ), $profile['since'] )
			: '';
		$html = $this->header( $username, $since ) . $this->tiles( $username, $summary );

		if ( ! empty( $summary['first'] ) && ! empty( $summary['latest'] ) ) {
			$html .= '<p class="nhrcc-note">' . esc_html(
				sprintf(
					/* translators: 1: date of the first contribution, 2: date of the latest contribution. */
					__( 'First contribution %1$s · Latest %2$s', 'nhrrob-core-contributions' ),
					wp_date( 'F Y', strtotime( $summary['first'] ) ),
					wp_date( 'F Y', strtotime( $summary['latest'] ) )
				)
			) . '</p>';
		}

		if ( ! empty( $summary['components'] ) ) {
			$html .= '<ul class="nhrcc-chips" aria-label="' . esc_attr__( 'Top components', 'nhrrob-core-contributions' ) . '">';
			foreach ( array_slice( $summary['components'], 0, 5, true ) as $component => $count ) {
				$html .= '<li class="nhrcc-chip">' . esc_html( $component ) . ' <span class="nhrcc-chip__count">' . esc_html( number_format_i18n( $count ) ) . '</span></li>';
			}
			$html .= '</ul>';
		}

		return $this->wrap( $attributes, 'nhrcc-card', $html );
	}

	/**
	 * The headline numbers on their own.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render_stats( $attributes ) {
		$username = $this->username( $attributes );
		if ( '' === $username ) {
			return $this->notice( __( 'Set a WordPress.org username in the block settings or under Tools → Core Contributions.', 'nhrrob-core-contributions' ) );
		}

		$tiles = $this->tiles( $username, Sync::summary( $username ) );

		return '' === $tiles ? $this->notice( __( 'Contribution numbers are unavailable right now.', 'nhrrob-core-contributions' ) ) : $this->wrap( $attributes, 'nhrcc-stats', $tiles );
	}

	/**
	 * Core contributions per year as a bar chart.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render_activity( $attributes ) {
		$username = $this->username( $attributes );
		if ( '' === $username ) {
			return $this->notice( __( 'Set a WordPress.org username in the block settings or under Tools → Core Contributions.', 'nhrrob-core-contributions' ) );
		}

		$summary = Sync::summary( $username );
		if ( empty( $summary['years'] ) ) {
			return $this->pending( $summary, __( 'No core contributions to chart for this username yet.', 'nhrrob-core-contributions' ) );
		}

		$years = $summary['years'];
		$peak  = max( $years );
		$last  = (int) gmdate( 'Y' );
		$html  = '<ol class="nhrcc-chart">';

		for ( $year = (int) min( array_keys( $years ) ); $year <= $last; $year++ ) {
			$count = isset( $years[ $year ] ) ? (int) $years[ $year ] : 0;
			$html .= '<li class="nhrcc-chart__col">';
			$html .= '<span class="nhrcc-chart__value">' . esc_html( number_format_i18n( $count ) ) . '</span>';
			$html .= '<span class="nhrcc-chart__bar" style="--nhrcc-share:' . esc_attr( round( $count / $peak, 4 ) ) . '"></span>';
			$html .= '<span class="nhrcc-chart__label">' . esc_html( (string) $year ) . '</span>';
			$html .= '</li>';
		}

		$html .= '</ol>';
		/* translators: %s: WordPress.org username. */
		$label = sprintf( __( 'Core contributions per year by %s', 'nhrrob-core-contributions' ), $username );

		return $this->wrap( $attributes, 'nhrcc-activity', '<p class="nhrcc-title">' . esc_html( $label ) . '</p>' . $html );
	}

	/**
	 * WordPress releases the contributor is credited in.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render_releases( $attributes ) {
		$username = $this->username( $attributes );
		if ( '' === $username ) {
			return $this->notice( __( 'Set a WordPress.org username in the block settings or under Tools → Core Contributions.', 'nhrrob-core-contributions' ) );
		}

		$summary = Sync::summary( $username );
		if ( empty( $summary['releases'] ) ) {
			return $this->pending( $summary, __( 'This username is not in the credits of a WordPress release yet.', 'nhrrob-core-contributions' ) );
		}

		$releases = array_reverse( $summary['releases'] );
		/* translators: %s: number of WordPress releases. */
		$label = sprintf( _n( 'Credited in %s WordPress release', 'Credited in %s WordPress releases', count( $releases ), 'nhrrob-core-contributions' ), number_format_i18n( count( $releases ) ) );
		$html  = '<p class="nhrcc-title">' . esc_html( $label ) . '</p><ul class="nhrcc-chips">';

		foreach ( $releases as $version ) {
			$url   = 'https://wordpress.org/documentation/wordpress-version/version-' . str_replace( '.', '-', $version ) . '/';
			$html .= '<li class="nhrcc-chip nhrcc-chip--release"><a href="' . esc_url( $url ) . '">' . esc_html( 'WordPress ' . $version ) . '</a></li>';
		}

		return $this->wrap( $attributes, 'nhrcc-releases', $html . '</ul>' );
	}

	/**
	 * The contributor's WordPress.org profile badges (teams, WordCamps, translation and more).
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render_badges( $attributes ) {
		$username = $this->username( $attributes );
		if ( '' === $username ) {
			return $this->notice( __( 'Set a WordPress.org username in the block settings or under Tools → Core Contributions.', 'nhrrob-core-contributions' ) );
		}

		$profile = Profile::get( $username );
		if ( is_wp_error( $profile ) || ! $profile['badges'] ) {
			return $this->notice( __( 'No WordPress.org profile badges could be read for this username.', 'nhrrob-core-contributions' ) );
		}

		$html = '<p class="nhrcc-title">' . esc_html__( 'WordPress.org badges', 'nhrrob-core-contributions' ) . '</p><ul class="nhrcc-chips">';
		foreach ( $profile['badges'] as $badge ) {
			$html .= '<li class="nhrcc-chip nhrcc-chip--badge">' . esc_html( $badge['label'] );
			$html .= '' !== $badge['since'] ? ' <span class="nhrcc-chip__count">' . esc_html( $badge['since'] ) . '</span>' : '';
			$html .= '</li>';
		}

		return $this->wrap( $attributes, 'nhrcc-badges', $html . '</ul>' );
	}

	/**
	 * Plugins or themes the contributor has published on WordPress.org.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render_directory( $attributes ) {
		$username = $this->username( $attributes );
		if ( '' === $username ) {
			return $this->notice( __( 'Set a WordPress.org username in the block settings or under Tools → Core Contributions.', 'nhrrob-core-contributions' ) );
		}

		$settings = Settings::get();
		$type     = isset( $attributes['type'] ) && 'themes' === $attributes['type'] ? 'themes' : 'plugins';
		$data     = Directory::items( $type, $username, $settings['cacheDuration'] );

		if ( is_wp_error( $data ) ) {
			return $this->notice( $data->get_error_message() );
		}
		if ( ! $data['items'] ) {
			return $this->notice( 'themes' === $type ? __( 'No themes found for this username on WordPress.org.', 'nhrrob-core-contributions' ) : __( 'No plugins found for this username on WordPress.org.', 'nhrrob-core-contributions' ) );
		}

		$html = '<ul class="nhrcc-grid">';
		foreach ( $data['items'] as $item ) {
			$meta = [];
			if ( $item['installs'] ) {
				/* translators: %s: number of active installations. */
				$meta[] = sprintf( __( '%s+ active installs', 'nhrrob-core-contributions' ), number_format_i18n( $item['installs'] ) );
			} elseif ( $item['downloads'] ) {
				/* translators: %s: number of downloads. */
				$meta[] = sprintf( __( '%s downloads', 'nhrrob-core-contributions' ), number_format_i18n( $item['downloads'] ) );
			}
			if ( $item['rating'] ) {
				/* translators: %s: average rating out of 5. */
				$meta[] = sprintf( __( '%s out of 5 stars', 'nhrrob-core-contributions' ), number_format_i18n( $item['rating'], 1 ) );
			}

			$html .= '<li class="nhrcc-entry nhrcc-entry--' . esc_attr( $type ) . '">';
			$html .= '' !== $item['image'] ? '<img class="nhrcc-entry__image" src="' . esc_url( $item['image'] ) . '" alt="" loading="lazy" decoding="async" />' : '';
			$html .= '<div class="nhrcc-entry__body"><a class="nhrcc-entry__name" href="' . esc_url( $item['link'] ) . '">' . esc_html( $item['name'] ) . '</a>';
			$html .= '' !== $item['description'] ? '<p class="nhrcc-entry__text">' . esc_html( wp_trim_words( $item['description'], 22 ) ) . '</p>' : '';
			$html .= $meta ? '<p class="nhrcc-item__meta">' . esc_html( implode( ' · ', $meta ) ) . '</p>' : '';
			$html .= '</div></li>';
		}

		return $this->wrap( $attributes, 'nhrcc-directory', $html . '</ul>' );
	}

	/**
	 * The username a block is about: its own, or the default from the settings.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	private function username( $attributes ) {
		$username = Settings::username( isset( $attributes['username'] ) ? $attributes['username'] : '' );

		if ( '' === $username ) {
			$settings = Settings::get();
			$username = $settings['username'];
		}

		return $username;
	}

	/**
	 * Avatar, name and one line of text. Falls back to the username when the profile cannot be read.
	 *
	 * @param string $username WordPress.org username.
	 * @param string $text     Line under the name.
	 * @return string
	 */
	private function header( $username, $text ) {
		$profile = Profile::get( $username );
		$name    = ! is_wp_error( $profile ) && '' !== $profile['name'] ? $profile['name'] : $username;
		$html    = '<div class="nhrcc-head">';

		if ( ! is_wp_error( $profile ) && '' !== $profile['avatar'] ) {
			$html .= '<img class="nhrcc-head__avatar" src="' . esc_url( $profile['avatar'] . '?s=128&d=mm' ) . '" alt="" width="56" height="56" loading="lazy" decoding="async" />';
		}

		$html .= '<div><p class="nhrcc-head__name"><a href="' . esc_url( Profile::url( $username ) ) . '">' . esc_html( $name ) . '</a> <span class="nhrcc-head__user">@' . esc_html( $username ) . '</span></p>';
		$html .= '' !== $text ? '<p class="nhrcc-head__text">' . esc_html( $text ) . '</p>' : '';

		return $html . '</div></div>';
	}

	/**
	 * The headline numbers. Before the first sync finishes only the core total is known.
	 *
	 * @param string $username WordPress.org username.
	 * @param array  $summary  Stored summary, possibly empty.
	 * @return string
	 */
	private function tiles( $username, array $summary ) {
		$tiles = [];

		if ( $summary ) {
			$tiles[] = [ $summary['total'], __( 'Core contributions', 'nhrrob-core-contributions' ) ];
			$tiles[] = [ $summary['gutenberg'], __( 'Gutenberg contributions', 'nhrrob-core-contributions' ) ];
			$tiles[] = [ count( $summary['releases'] ), __( 'WordPress releases', 'nhrrob-core-contributions' ) ];
			$tiles[] = [ count( $summary['years'] ), __( 'Years contributing', 'nhrrob-core-contributions' ) ];
		} else {
			$settings = Settings::get();
			$data     = Commits::page( 'core', $username, 1, $settings['postsPerPage'], $settings['cacheDuration'] );
			if ( ! is_wp_error( $data ) ) {
				$tiles[] = [ $data['total'], __( 'Core contributions', 'nhrrob-core-contributions' ) ];
			}
		}

		$html = '';
		foreach ( $tiles as $tile ) {
			if ( $tile[0] ) {
				$html .= '<div class="nhrcc-tile"><dt class="nhrcc-tile__label">' . esc_html( $tile[1] ) . '</dt><dd class="nhrcc-tile__value">' . esc_html( number_format_i18n( $tile[0] ) ) . '</dd></div>';
			}
		}

		return '' === $html ? '' : '<dl class="nhrcc-tiles">' . $html . '</dl>';
	}

	/**
	 * What a block that needs the summary shows when it has nothing to show.
	 *
	 * @param array  $summary Stored summary, possibly empty.
	 * @param string $none    Message when the summary is in but holds nothing for this block.
	 * @return string
	 */
	private function pending( array $summary, $none ) {
		return $this->notice( $summary ? $none : __( 'Contribution history is being gathered in the background. This block fills in within a few minutes.', 'nhrrob-core-contributions' ) );
	}

	/**
	 * A message for people who can edit; visitors get nothing.
	 *
	 * @param string $message Message.
	 * @return string
	 */
	private function notice( $message ) {
		return current_user_can( 'edit_posts' ) ? '<p class="nhrcc-notice">' . esc_html( $message ) . '</p>' : '';
	}

	/**
	 * Wrap block content with the block's wrapper attributes.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $class_name Block-specific class.
	 * @param string $html       Inner HTML, already escaped.
	 * @param string $id         Optional element id.
	 * @return string
	 */
	private function wrap( $attributes, $class_name, $html, $id = '' ) {
		$extra = [ 'class' => 'nhrcc ' . $class_name ];

		// Only a colour value can reach the style attribute.
		if ( ! empty( $attributes['accentColor'] ) && preg_match( '/^(#[0-9a-f]{3,8}|var\(--[\w-]+\)|rgba?\([\d\s.,%\/]+\))$/i', (string) $attributes['accentColor'] ) ) {
			$extra['style'] = '--nhrcc-accent:' . $attributes['accentColor'] . ';';
		}
		if ( '' !== $id ) {
			$extra['id'] = $id;
		}

		return '<div ' . get_block_wrapper_attributes( $extra ) . '>' . $html . '</div>';
	}
}
