<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Core Contributions Block view template
 *
 * @var string $username
 * @var string $preset
 * @var array  $core_contributions
 * @var int    $total_contribution_count
 * @var int    $page
 */

// Get the current page URL
global $wp;
                    
$current_url = is_admin() ? admin_url("tools.php?page={$this->page_slug}") : home_url(add_query_arg(array(), $wp->request));
$is_shortcode = ! is_admin() ? 1 : 0;

$is_block_editor = defined('REST_REQUEST') && REST_REQUEST && strpos(wp_get_referer(), 'post.php') !== false;

$preset = isset($preset) ? $preset : 'default';

// Check for custom styles to avoid overriding them with default preset classes
$has_custom_bg = !empty($attributes['backgroundColor']) || !empty($attributes['style']['color']['background']) || !empty($attributes['style']['color']['gradient']);
$has_custom_padding = !empty($attributes['style']['spacing']['padding']);
$has_custom_border = !empty($attributes['style']['border']);
$has_custom_text_color = !empty($attributes['textColor']) || !empty($attributes['style']['color']['text']);
$has_custom_link_color = !empty($attributes['linkColor']) || !empty($attributes['style']['color']['link']);
$has_custom_typography = !empty($attributes['fontSize']) || !empty($attributes['fontFamily']) || !empty($attributes['style']['typography']);

// Title specific styles
$title_color = ! empty( $attributes['titleColor'] ) ? sanitize_text_field($attributes['titleColor']) : '';
$title_bg_color = ! empty( $attributes['titleBackgroundColor'] ) ? sanitize_text_field($attributes['titleBackgroundColor']) : '';
$title_font_size = ! empty( $attributes['titleFontSize'] ) ? sanitize_text_field($attributes['titleFontSize']) : '';
$title_font_weight = ! empty( $attributes['titleFontWeight'] ) ? sanitize_text_field($attributes['titleFontWeight']) : '';

$accent_color = ! empty( $attributes['accentColor'] ) ? sanitize_text_field($attributes['accentColor']) : '';
$meta_color = ! empty( $attributes['metaColor'] ) ? sanitize_text_field($attributes['metaColor']) : '';
$pagination_color = ! empty( $attributes['paginationColor'] ) ? sanitize_text_field($attributes['paginationColor']) : '';

$has_custom_title_style = ! empty( $title_color ) || ! empty( $title_bg_color ) || ! empty( $title_font_size ) || ! empty( $title_font_weight );

$title_style = '';
if ( ! empty( $title_color ) ) $title_style .= "color: {$title_color};";
if ( ! empty( $title_bg_color ) ) $title_style .= "background-color: {$title_bg_color};" . (empty($title_bg_color) ? '' : ' padding: 0.5rem; border-radius: 0.25rem;');
if ( ! empty( $title_font_size ) ) $title_style .= "font-size: {$title_font_size};";
if ( ! empty( $title_font_weight ) ) $title_style .= "font-weight: {$title_font_weight};";

$accent_style = ! empty( $accent_color ) ? "color: {$accent_color};" : '';
$accent_bg_style = ! empty( $accent_color ) ? "background-color: {$accent_color};" : '';
$meta_style = ! empty( $meta_color ) ? "color: {$meta_color};" : '';
$pagination_style = ! empty( $pagination_color ) ? "color: {$pagination_color};" : '';

$presets = [
    'default' => [
        'wrapper' => 'nhrcc-preset-default rounded ' . ($has_custom_bg ? '' : 'bg-gray-50 ') . ($has_custom_padding ? '' : 'p-4 ') . 'max-w-4xl mx-auto',
        'header' => 'flex items-center gap-2 mb-4',
        'title' => ($has_custom_text_color || $has_custom_title_style ? '' : 'text-gray-700 ') . ($has_custom_typography || $has_custom_title_style ? '' : 'text-lg font-medium'),
        'list' => 'grid gap-2',
        'item' => ($has_custom_typography ? '' : 'text-sm ') . 'bg-white rounded p-2 shadow-sm flex items-center gap-2',
        'link' => ($has_custom_link_color ? '' : 'text-gray-600 ') . 'no-underline hover:text-gray-900',
        'pagination_wrap' => 'pagination flex flex-wrap justify-center items-center space-x-2 mt-4',
        'editor_pagination_wrap' => 'bg-gray-100 border border-gray-500 text-gray-600 px-4 py-2 rounded text-base text-center',
    ],
    'minimal' => [
        'wrapper' => 'nhrcc-preset-minimal rounded ' . ($has_custom_padding ? '' : 'p-4 ') . 'max-w-4xl mx-auto',
        'header' => 'flex items-center gap-2 mb-4',
        'title' => ($has_custom_text_color || $has_custom_title_style ? '' : 'text-gray-700 ') . ($has_custom_typography || $has_custom_title_style ? '' : 'text-lg font-medium'),
        'list' => 'space-y-2',
        'item' => ($has_custom_typography ? '' : 'text-sm ') . 'p-2 flex items-center gap-2',
        'link' => ($has_custom_link_color ? '' : 'text-gray-600 ') . 'no-underline hover:text-gray-900',
        'pagination_wrap' => 'px-4 py-2 pagination flex flex-wrap space-x-2 mt-4',
        'editor_pagination_wrap' => 'border border-gray-500 text-gray-600 px-4 py-2 rounded text-base',
    ],
    'modern' => [
        'wrapper' => 'nhrcc-preset-modern rounded-2xl ' . ($has_custom_bg ? '' : 'bg-[#F9FAFB] ') . ($has_custom_padding ? '' : 'p-8 ') . ($has_custom_border ? '' : 'border border-gray-200/60 ') . 'max-w-6xl mx-auto shadow-[0_1px_3px_rgba(0,0,0,0.05)]',
        'header' => 'flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-6 border-b border-gray-200',
        'title' => ($has_custom_text_color || $has_custom_title_style ? '' : 'text-gray-900 ') . ($has_custom_typography || $has_custom_title_style ? '' : 'text-2xl font-bold tracking-tight'),
        'list' => 'grid grid-cols-1 lg:grid-cols-2 gap-4 p-0',
        'item' => 'group relative bg-white rounded-xl p-5 border border-gray-200/80 transition-all duration-300 hover:border-blue-500/50 hover:shadow-[0_10px_25px_-5px_rgba(0,0,0,0.05)] flex items-start gap-4 ' . ($has_custom_typography ? '' : 'text-sm'),
        'link' => ($has_custom_link_color ? '' : 'text-gray-900 ') . 'no-underline ' . ($has_custom_typography ? '' : 'text-[15px] ') . 'font-semibold leading-snug group-hover:text-blue-600 block mb-2',
        'pagination_wrap' => 'pagination flex flex-wrap justify-center items-center gap-2 mt-10',
        'editor_pagination_wrap' => 'bg-gray-100 border border-gray-200 text-gray-500 px-6 py-3 rounded-xl text-sm text-center font-medium italic',
    ],
];

$styles = $presets[$preset] ?? $presets['default'];
?>

<div <?php echo get_block_wrapper_attributes(['class' => $styles['wrapper']]); ?>>
    <div class="<?php echo esc_attr($styles['header']); ?>">
        <h2 class="<?php echo esc_attr($styles['title']); ?>" style="<?php echo esc_attr($title_style); ?>">
            <?php printf(__('Core Contributions (<code>%s</code>): %d', 'nhrrob-core-contributions'), 
                esc_attr($username), 
                intval($total_contribution_count)
            ); ?>
        </h2>
    </div>

    <?php if (!empty($core_contributions) && $total_contribution_count > 0) : ?>
        <ul class="<?php echo esc_attr($styles['list']); ?>">
            <?php foreach ($core_contributions as $contribution) : ?>
                <li class="<?php echo esc_attr($styles['item']); ?>">
                    <?php if ($preset === 'modern') : ?>
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center <?php echo ! empty( $accent_color ) ? '' : 'bg-blue-50'; ?>" 
                             style="<?php echo esc_attr($accent_bg_style); ?><?php echo ! empty( $accent_color ) ? ' opacity: 0.15;' : ''; ?>">
                        </div>
                        <div class="absolute left-5 top-5 flex-shrink-0 w-10 h-10 flex items-center justify-center">
                            <svg class="w-6 h-6 <?php echo ! empty( $accent_color ) ? '' : 'text-blue-600'; ?>" style="<?php echo esc_attr($accent_style); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </div>
                        <div class="flex-grow min-w-0">
                            <a href="<?php echo esc_url($contribution['link']); ?>"
                               target="_blank"
                               class="<?php echo esc_attr($styles['link']); ?>">
                                <?php echo esc_html($contribution['description']); ?>
                            </a>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs font-medium <?php echo ! empty( $meta_color ) ? '' : 'text-gray-500'; ?>" style="<?php echo esc_attr($meta_style); ?>">
                                <span class="flex items-center gap-1">
                                    <span class="<?php echo ! empty( $meta_color ) ? '' : 'text-gray-400'; ?>" style="<?php echo esc_attr($meta_style); ?>">Changeset:</span>
                                    <span class="px-1.5 py-0.5 rounded <?php echo ! empty( $accent_color ) ? '' : 'bg-gray-100 text-gray-700'; ?>" style="<?php echo esc_attr($accent_bg_style); ?><?php echo ! empty( $accent_color ) ? ' color: white;' : ''; ?>"><?php echo esc_html($contribution['changeset']); ?></span>
                                </span>
                                <?php if (!empty($contribution['ticket'])) : ?>
                                    <span class="flex items-center gap-1">
                                        <span class="<?php echo ! empty( $meta_color ) ? '' : 'text-gray-400'; ?>" style="<?php echo esc_attr($meta_style); ?>">Ticket:</span>
                                        <span class="<?php echo ! empty( $accent_color ) ? '' : 'text-blue-600'; ?>" style="<?php echo esc_attr($accent_style); ?>">#<?php echo esc_html($contribution['ticket']); ?></span>
                                    </span>
                                <?php endif; ?>
                                <span class="ml-auto font-normal <?php echo ! empty( $meta_color ) ? '' : 'text-gray-400'; ?>" style="<?php echo esc_attr($meta_style); ?>">Core</span>
                            </div>
                        </div>
                    <?php else : ?>
                        <?php if ( ! empty( $accent_color ) ) : ?>
                            <span class="w-2 h-2 rounded-full flex-shrink-0" style="<?php echo esc_attr($accent_bg_style); ?>"></span>
                        <?php endif; ?>
                        <a href="<?php echo esc_url($contribution['link']); ?>"
                           target="_blank"
                           class="<?php echo esc_attr($styles['link']); ?>">
                            <?php echo esc_html($contribution['description']); ?>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php
        $contributions_per_page = 10;
        $total_pages = ceil($total_contribution_count / $contributions_per_page);
        ?>

        <?php if ($total_pages > 1 && !$is_block_editor) : ?>
            <div class="<?php echo esc_attr($styles['pagination_wrap']); ?>" style="<?php echo esc_attr($pagination_style); ?>">
                <?php
                $output = $this->paginate_links(
                    intval($page),
                    intval($total_pages),
                    esc_url($current_url),
                    esc_html(sanitize_text_field($username)),
                    intval($is_shortcode)
                );
                // Simple regex to inject custom pagination color if set
                if ( ! empty( $pagination_color ) ) {
                    $output = str_replace('<a ', '<a style="' . esc_attr($pagination_style) . '" ', $output);
                    $output = str_replace('<span ', '<span style="' . esc_attr($pagination_style) . '" ', $output);
                }
                echo wp_kses($output, $this->allowed_html());
                ?>
            </div>
        <?php endif; ?>
        
        <?php if ($total_pages > 1 && $is_block_editor) : ?>
            <div class="<?php echo esc_attr($styles['editor_pagination_wrap']); ?>">
                <span>Pagination is hidden in the editor!</span>
            </div>
        <?php endif; ?>
    <?php else : ?>
        <p class="text-red-500"><?php esc_html_e('No contributions found for this user.', 'nhrrob-core-contributions'); ?></p>
    <?php endif; ?>
</div>