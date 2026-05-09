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
                    
$nhrcc_current_url = is_admin() ? admin_url("tools.php?page={$this->page_slug}") : home_url(add_query_arg(array(), $wp->request));
$nhrcc_is_shortcode = ! is_admin() ? 1 : 0;

$nhrcc_is_block_editor = defined('REST_REQUEST') && REST_REQUEST && strpos(wp_get_referer(), 'post.php') !== false;

$nhrcc_preset = isset($preset) ? $preset : 'default';

// Check for custom styles to avoid overriding them with default preset classes
$nhrcc_has_custom_bg = !empty($attributes['backgroundColor']) || !empty($attributes['style']['color']['background']) || !empty($attributes['style']['color']['gradient']);
$nhrcc_has_custom_padding = !empty($attributes['style']['spacing']['padding']);
$nhrcc_has_custom_border = !empty($attributes['style']['border']);
$nhrcc_has_custom_text_color = !empty($attributes['textColor']) || !empty($attributes['style']['color']['text']);
$nhrcc_has_custom_link_color = !empty($attributes['linkColor']) || !empty($attributes['style']['color']['link']);
$nhrcc_has_custom_typography = !empty($attributes['fontSize']) || !empty($attributes['fontFamily']) || !empty($attributes['style']['typography']);

// Title specific styles
$nhrcc_title_color = ! empty( $attributes['titleColor'] ) ? sanitize_text_field($attributes['titleColor']) : '';
$nhrcc_title_bg_color = ! empty( $attributes['titleBackgroundColor'] ) ? sanitize_text_field($attributes['titleBackgroundColor']) : '';
$nhrcc_title_font_size = ! empty( $attributes['titleFontSize'] ) ? sanitize_text_field($attributes['titleFontSize']) : '';
$nhrcc_title_font_weight = ! empty( $attributes['titleFontWeight'] ) ? sanitize_text_field($attributes['titleFontWeight']) : '';

$nhrcc_accent_color = ! empty( $attributes['accentColor'] ) ? sanitize_text_field($attributes['accentColor']) : '';
$nhrcc_meta_color = ! empty( $attributes['metaColor'] ) ? sanitize_text_field($attributes['metaColor']) : '';
$nhrcc_pagination_color = ! empty( $attributes['paginationColor'] ) ? sanitize_text_field($attributes['paginationColor']) : '';
$nhrcc_show_time = ! empty( $attributes['showTime'] ) ? true : false;

$nhrcc_has_custom_title_style = ! empty( $nhrcc_title_color ) || ! empty( $nhrcc_title_bg_color ) || ! empty( $nhrcc_title_font_size ) || ! empty( $nhrcc_title_font_weight );

$nhrcc_title_style = '';
if ( ! empty( $nhrcc_title_color ) ) $nhrcc_title_style .= "color: {$nhrcc_title_color};";
if ( ! empty( $nhrcc_title_bg_color ) ) $nhrcc_title_style .= "background-color: {$nhrcc_title_bg_color};" . (empty($nhrcc_title_bg_color) ? '' : ' padding: 0.5rem; border-radius: 0.25rem;');
if ( ! empty( $nhrcc_title_font_size ) ) $nhrcc_title_style .= "font-size: {$nhrcc_title_font_size};";
if ( ! empty( $nhrcc_title_font_weight ) ) $nhrcc_title_style .= "font-weight: {$nhrcc_title_font_weight};";

$nhrcc_accent_style = ! empty( $nhrcc_accent_color ) ? "color: {$nhrcc_accent_color};" : '';
$nhrcc_accent_bg_style = ! empty( $nhrcc_accent_color ) ? "background-color: {$nhrcc_accent_color};" : '';
$nhrcc_meta_style = ! empty( $nhrcc_meta_color ) ? "color: {$nhrcc_meta_color};" : '';
$nhrcc_pagination_style = ! empty( $nhrcc_pagination_color ) ? "color: {$nhrcc_pagination_color};" : '';

// CSS Variables for dynamic styles
$nhrcc_vars = [];
if (!empty($nhrcc_title_color)) $nhrcc_vars[] = "--nhrcc-title-color: {$nhrcc_title_color}";
if (!empty($nhrcc_title_bg_color)) $nhrcc_vars[] = "--nhrcc-title-bg: {$nhrcc_title_bg_color}";
if (!empty($nhrcc_accent_color)) $nhrcc_vars[] = "--nhrcc-accent-color: {$nhrcc_accent_color}";
if (!empty($nhrcc_meta_color)) $nhrcc_vars[] = "--nhrcc-meta-color: {$nhrcc_meta_color}";
if (!empty($nhrcc_pagination_color)) $nhrcc_vars[] = "--nhrcc-pagination-color: {$nhrcc_pagination_color}";

$nhrcc_vars_style = !empty($nhrcc_vars) ? implode('; ', $nhrcc_vars) . ';' : '';

$nhrcc_presets = [
    'default' => [
        'wrapper' => 'nhrcc-preset-default rounded ' . ($nhrcc_has_custom_bg ? '' : 'bg-gray-50 ') . ($nhrcc_has_custom_padding ? '' : 'p-4 ') . 'max-w-4xl mx-auto',
        'header' => 'flex items-center gap-2 mb-4',
        'title' => ($nhrcc_has_custom_text_color || $nhrcc_has_custom_title_style ? '' : 'text-gray-700 ') . ($nhrcc_has_custom_typography || $nhrcc_has_custom_title_style ? '' : 'text-lg font-medium'),
        'list' => 'grid gap-2',
        'item' => ($nhrcc_has_custom_typography ? '' : 'text-sm ') . 'bg-white rounded p-2 shadow-sm flex items-center gap-2',
        'link' => ($nhrcc_has_custom_link_color ? '' : 'text-gray-600 ') . 'no-underline hover:text-gray-900',
        'pagination_wrap' => 'pagination flex flex-wrap justify-center items-center space-x-2 mt-4',
        'editor_pagination_wrap' => 'bg-gray-100 border border-gray-500 text-gray-600 px-4 py-2 rounded text-base text-center',
    ],
    'minimal' => [
        'wrapper' => 'nhrcc-preset-minimal rounded ' . ($nhrcc_has_custom_padding ? '' : 'p-4 ') . 'max-w-4xl mx-auto',
        'header' => 'flex items-center gap-2 mb-4',
        'title' => ($nhrcc_has_custom_text_color || $nhrcc_has_custom_title_style ? '' : 'text-gray-700 ') . ($nhrcc_has_custom_typography || $nhrcc_has_custom_title_style ? '' : 'text-lg font-medium'),
        'list' => 'space-y-2',
        'item' => ($nhrcc_has_custom_typography ? '' : 'text-sm ') . 'p-2 flex items-center gap-2',
        'link' => ($nhrcc_has_custom_link_color ? '' : 'text-gray-600 ') . 'no-underline hover:text-gray-900',
        'pagination_wrap' => 'px-4 py-2 pagination flex flex-wrap space-x-2 mt-4',
        'editor_pagination_wrap' => 'border border-gray-500 text-gray-600 px-4 py-2 rounded text-base',
    ],
    'modern' => [
        'wrapper' => 'nhrcc-preset-modern rounded-2xl ' . ($nhrcc_has_custom_bg ? '' : 'bg-[#F9FAFB] ') . ($nhrcc_has_custom_padding ? '' : 'p-8 ') . ($nhrcc_has_custom_border ? '' : 'border border-gray-200/60 ') . 'max-w-6xl mx-auto shadow-[0_1px_3px_rgba(0,0,0,0.05)]',
        'header' => 'flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-6 border-b border-gray-200',
        'title' => ($nhrcc_has_custom_text_color || $nhrcc_has_custom_title_style ? '' : 'text-gray-900 ') . ($nhrcc_has_custom_typography || $nhrcc_has_custom_title_style ? '' : 'text-2xl font-bold tracking-tight'),
        'list' => 'nhrcc-grid',
        'item' => 'group relative bg-white rounded-xl p-5 border border-gray-200/80 transition-all duration-300 hover:border-blue-500/50 hover:shadow-[0_10px_25px_-5px_rgba(0,0,0,0.05)] flex items-start gap-4 ' . ($nhrcc_has_custom_typography ? '' : 'text-sm'),
        'link' => ($nhrcc_has_custom_link_color ? '' : 'text-gray-900 ') . 'no-underline ' . ($nhrcc_has_custom_typography ? '' : 'text-[15px] ') . 'font-semibold leading-snug group-hover:text-blue-600 block mb-2',
        'pagination_wrap' => 'nhrcc-pagination',
        'editor_pagination_wrap' => 'bg-gray-100 border border-gray-200 text-gray-500 px-6 py-3 rounded-xl text-sm text-center font-medium italic',
    ],
    'card' => [
        'wrapper' => 'nhrcc-preset-card ' . ($nhrcc_has_custom_bg ? '' : '') . ($nhrcc_has_custom_padding ? '' : 'nhrcc-padding-default '),
        'header'  => 'nhrcc-header',
        'title'   => 'nhrcc-title ' . ($nhrcc_has_custom_text_color || $nhrcc_has_custom_title_style ? '' : 'nhrcc-text-default '),
        'list'    => 'nhrcc-card-grid',
        'item'    => 'nhrcc-card-item',
        'link'    => 'nhrcc-card-link ' . ($nhrcc_has_custom_link_color ? '' : 'nhrcc-link-default '),
        'pagination_wrap' => 'nhrcc-pagination',
        'editor_pagination_wrap' => 'nhrcc-editor-pagination',
    ],
    'timeline' => [
        'wrapper' => 'nhrcc-preset-timeline ' . ($nhrcc_has_custom_bg ? '' : '') . ($nhrcc_has_custom_padding ? '' : 'nhrcc-padding-default '),
        'header'  => 'nhrcc-header',
        'title'   => 'nhrcc-title ' . ($nhrcc_has_custom_text_color || $nhrcc_has_custom_title_style ? '' : 'nhrcc-text-default '),
        'list'    => 'nhrcc-timeline-list',
        'item'    => 'nhrcc-timeline-item',
        'link'    => 'nhrcc-timeline-link ' . ($nhrcc_has_custom_link_color ? '' : ''),
        'pagination_wrap' => 'nhrcc-pagination',
        'editor_pagination_wrap' => 'nhrcc-editor-pagination',
    ],
];

$nhrcc_styles = $nhrcc_presets[$nhrcc_preset] ?? $nhrcc_presets['default'];
?>

<div <?php echo get_block_wrapper_attributes(['class' => $nhrcc_styles['wrapper'], 'style' => $nhrcc_vars_style]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
    <div class="<?php echo esc_attr($nhrcc_styles['header']); ?>">
        <h2 class="<?php echo esc_attr($nhrcc_styles['title']); ?>" style="<?php echo esc_attr($nhrcc_title_style); ?>">
            <?php
            $nhrcc_title_text = sprintf(
                /* translators: 1: WordPress.org username, 2: total contribution count */
                __('Core Contributions (<code>%1$s</code>): %2$d', 'nhrrob-core-contributions'),
                esc_html($username),
                intval($total_contribution_count)
            );
            echo wp_kses($nhrcc_title_text, ['code' => []]);
            ?>
        </h2>
    </div>

    <?php if (!empty($core_contributions) && $total_contribution_count > 0) : ?>
        <ul class="<?php echo esc_attr($nhrcc_styles['list']); ?>">
            <?php foreach ($core_contributions as $nhrcc_index => $nhrcc_contribution) : ?>
                <li class="<?php echo esc_attr($nhrcc_styles['item']); ?>">
                    <?php if ($nhrcc_preset === 'modern' || $nhrcc_preset === 'card') : ?>
                        <div class="nhrcc-card-icon <?php echo ! empty( $nhrcc_accent_color ) ? 'nhrcc-card-icon-bg-custom' : ''; ?>">
                             <?php if ($nhrcc_preset === 'modern') : ?>
                                <svg class="w-6 h-6 <?php echo ! empty( $nhrcc_accent_color ) ? '' : 'text-blue-600'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                             <?php else : ?>
                                <svg class="nhrcc-card-icon-<?php echo esc_attr($nhrcc_index); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                             <?php endif; ?>
                        </div>
                        
                        <div class="flex-grow min-w-0 <?php echo $nhrcc_preset === 'card' ? 'flex flex-col h-full' : ''; ?>">
                            <a href="<?php echo esc_url($nhrcc_contribution['link']); ?>"
                               target="_blank"
                               class="<?php echo esc_attr($nhrcc_styles['link']); ?>">
                                <?php echo esc_html($nhrcc_contribution['description']); ?>
                            </a>
                            <div class="nhrcc-card-meta">
                                <span class="nhrcc-badge-wrap">
                                    <svg class="w-3.5 h-3.5 <?php echo ! empty( $nhrcc_meta_color ) ? '' : 'text-gray-400'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <span class="nhrcc-badge <?php echo ! empty( $nhrcc_accent_color ) ? '' : 'bg-gray-100 text-gray-600'; ?>"><?php echo esc_html($nhrcc_contribution['changeset']); ?></span>
                                </span>
                                <?php if (!empty($nhrcc_contribution['ticket'])) : ?>
                                    <span class="nhrcc-badge-wrap">
                                        <span class="<?php echo ! empty( $nhrcc_meta_color ) ? '' : 'text-gray-300'; ?>">|</span>
                                        <a href="https://core.trac.wordpress.org/ticket/<?php echo esc_html($nhrcc_contribution['ticket']); ?>" target="_blank" class="hover:underline <?php echo ! empty( $nhrcc_accent_color ) ? '' : 'text-blue-600'; ?>">#<?php echo esc_html($nhrcc_contribution['ticket']); ?></a>
                                    </span>
                                <?php endif; ?>
                                <?php if ($nhrcc_show_time && !empty($nhrcc_contribution['date'])) : ?>
                                    <span class="nhrcc-badge-wrap">
                                        <span class="<?php echo ! empty( $nhrcc_meta_color ) ? '' : 'text-gray-300'; ?>">|</span>
                                        <span class="nhrcc-time"><?php 
                                            $nhrcc_date_obj = DateTime::createFromFormat('m/d/Y h:i:s A', $nhrcc_contribution['date']);
                                            if ($nhrcc_date_obj) {
                                                echo esc_html(date_i18n(get_option('date_format'), $nhrcc_date_obj->getTimestamp()));
                                            } else {
                                                echo esc_html($nhrcc_contribution['date']);
                                            }
                                        ?></span>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php elseif ($nhrcc_preset === 'timeline') : ?>
                        <!-- Timeline Node -->
                        <div class="nhrcc-timeline-node <?php echo ! empty( $nhrcc_accent_color ) ? 'nhrcc-timeline-node-custom' : ''; ?>">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        
                        <!-- Timeline Content -->
                        <div class="nhrcc-timeline-content">
                            <a href="<?php echo esc_url($nhrcc_contribution['link']); ?>"
                               target="_blank"
                               class="<?php echo esc_attr($nhrcc_styles['link']); ?>">
                                <?php echo esc_html($nhrcc_contribution['description']); ?>
                            </a>
                            
                            <div class="nhrcc-timeline-meta <?php echo ! empty( $nhrcc_meta_color ) ? '' : 'text-gray-500'; ?>">
                                <span class="nhrcc-meta-item">
                                    <span class="nhrcc-dot <?php echo ! empty( $nhrcc_accent_color ) ? '' : 'bg-blue-500'; ?>"></span>
                                    <span>Changeset <?php echo esc_html($nhrcc_contribution['changeset']); ?></span>
                                </span>
                                <?php if (!empty($nhrcc_contribution['ticket'])) : ?>
                                    <span class="nhrcc-separator"></span>
                                    <span class="font-mono">Ticket #<?php echo esc_html($nhrcc_contribution['ticket']); ?></span>
                                <?php endif; ?>
                                <?php if ($nhrcc_show_time && !empty($nhrcc_contribution['date'])) : ?>
                                    <span class="nhrcc-separator"></span>
                                    <span class="nhrcc-time"><?php 
                                        $nhrcc_date_obj = DateTime::createFromFormat('m/d/Y h:i:s A', $nhrcc_contribution['date']);
                                        if ($nhrcc_date_obj) {
                                            echo esc_html(date_i18n(get_option('date_format'), $nhrcc_date_obj->getTimestamp()));
                                        } else {
                                            echo esc_html($nhrcc_contribution['date']);
                                        }
                                    ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else : ?>

                        <?php if ( ! empty( $nhrcc_accent_color ) ) : ?>
                            <span class="w-2 h-2 rounded-full flex-shrink-0 nhrcc-accent-dot"></span>
                        <?php endif; ?>
                        <a href="<?php echo esc_url($nhrcc_contribution['link']); ?>"
                           target="_blank"
                           class="<?php echo esc_attr($nhrcc_styles['link']); ?>">
                            <?php echo esc_html($nhrcc_contribution['description']); ?>
                        </a>
                        <?php if ($nhrcc_show_time && !empty($nhrcc_contribution['date'])) : ?>
                            <span class="text-xs text-gray-500 ml-auto nhrcc-time"><?php 
                                $nhrcc_date_obj = DateTime::createFromFormat('m/d/Y h:i:s A', $nhrcc_contribution['date']);
                                if ($nhrcc_date_obj) {
                                    echo esc_html(date_i18n(get_option('date_format'), $nhrcc_date_obj->getTimestamp()));
                                } else {
                                    echo esc_html($nhrcc_contribution['date']);
                                }
                            ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php
        $nhrcc_contributions_per_page = 10;
        $nhrcc_total_pages = ceil($total_contribution_count / $nhrcc_contributions_per_page);
        ?>

        <?php if ($nhrcc_total_pages > 1 && !$nhrcc_is_block_editor) : ?>
            <div class="<?php echo esc_attr($nhrcc_styles['pagination_wrap']); ?>">
                <?php
                $nhrcc_output = $this->paginate_links(
                    intval($page),
                    intval($nhrcc_total_pages),
                    esc_url($nhrcc_current_url),
                    esc_html(sanitize_text_field($username)),
                    intval($nhrcc_is_shortcode)
                );
                // Simple regex to inject custom pagination color if set
                if ( ! empty( $nhrcc_pagination_color ) ) {
                    $nhrcc_output = str_replace('<a ', '<a style="' . esc_attr($nhrcc_pagination_style) . '" ', $nhrcc_output);
                    $nhrcc_output = str_replace('<span ', '<span style="' . esc_attr($nhrcc_pagination_style) . '" ', $nhrcc_output);
                }
                echo wp_kses($nhrcc_output, $this->allowed_html());
                ?>
            </div>
        <?php endif; ?>
        
        <?php if ($nhrcc_total_pages > 1 && $nhrcc_is_block_editor) : ?>
            <div class="<?php echo esc_attr($nhrcc_styles['editor_pagination_wrap']); ?>">
                <span>Pagination is hidden in the editor!</span>
            </div>
        <?php endif; ?>
    <?php else : ?>
        <p class="text-red-500"><?php esc_html_e('No contributions found for this user.', 'nhrrob-core-contributions'); ?></p>
    <?php endif; ?>
</div>