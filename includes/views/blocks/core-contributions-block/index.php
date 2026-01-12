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

$presets = [
    'default' => [
        'wrapper' => 'nhrcc-preset-default bg-gray-50 rounded p-4 max-w-4xl mx-auto',
        'header' => 'flex items-center gap-2 mb-4',
        'title' => 'text-lg font-medium text-gray-700',
        'list' => 'grid gap-2',
        'item' => 'text-sm bg-white rounded p-2 shadow-sm',
        'link' => '!text-gray-600 !no-underline hover:text-gray-900',
        'pagination_wrap' => 'pagination flex flex-wrap justify-center items-center space-x-2 mt-4',
        'editor_pagination_wrap' => 'bg-gray-100 border border-gray-500 text-gray-600 px-4 py-2 rounded text-base text-center',
    ],
    'minimal' => [
        'wrapper' => 'nhrcc-preset-minimal rounded p-4 max-w-4xl mx-auto',
        'header' => 'flex items-center gap-2 mb-4',
        'title' => 'text-lg font-medium text-gray-700',
        'list' => 'space-y-2',
        'item' => 'text-sm p-2',
        'link' => '!text-gray-600 !no-underline hover:text-gray-900',
        'pagination_wrap' => 'px-4 py-2 pagination flex flex-wrap space-x-2 mt-4',
        'editor_pagination_wrap' => 'border border-gray-500 text-gray-600 px-4 py-2 rounded text-base',
    ],
    'modern' => [
        'wrapper' => 'nhrcc-preset-modern bg-[#F9FAFB] rounded-2xl p-8 max-w-6xl mx-auto border border-gray-200/60 shadow-[0_1px_3px_rgba(0,0,0,0.05)]',
        'header' => 'flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-6 border-b border-gray-200',
        'title' => 'text-2xl font-bold tracking-tight text-gray-900',
        'list' => 'grid grid-cols-1 lg:grid-cols-2 gap-4 p-0',
        'item' => 'group relative bg-white rounded-xl p-5 border border-gray-200/80 transition-all duration-300 hover:border-blue-500/50 hover:shadow-[0_10px_25px_-5px_rgba(0,0,0,0.05)] flex items-start gap-4',
        'link' => '!text-gray-900 !no-underline text-[15px] font-semibold leading-snug group-hover:text-blue-600 block mb-2',
        'pagination_wrap' => 'pagination flex flex-wrap justify-center items-center gap-2 mt-10',
        'editor_pagination_wrap' => 'bg-gray-100 border border-gray-200 text-gray-500 px-6 py-3 rounded-xl text-sm text-center font-medium italic',
    ],
];

$styles = $presets[$preset] ?? $presets['default'];
?>

<div <?php echo get_block_wrapper_attributes(['class' => $styles['wrapper']]); ?>>
    <div class="<?php echo esc_attr($styles['header']); ?>">
        <h2 class="<?php echo esc_attr($styles['title']); ?>">
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
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </div>
                        <div class="flex-grow min-w-0">
                            <a href="<?php echo esc_url($contribution['link']); ?>"
                               target="_blank"
                               class="<?php echo esc_attr($styles['link']); ?>">
                                <?php echo esc_html($contribution['description']); ?>
                            </a>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500 font-medium">
                                <span class="flex items-center gap-1">
                                    <span class="text-gray-400">Changeset:</span>
                                    <span class="bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded"><?php echo esc_html($contribution['changeset']); ?></span>
                                </span>
                                <?php if (!empty($contribution['ticket'])) : ?>
                                    <span class="flex items-center gap-1">
                                        <span class="text-gray-400">Ticket:</span>
                                        <span class="text-blue-600">#<?php echo esc_html($contribution['ticket']); ?></span>
                                    </span>
                                <?php endif; ?>
                                <span class="ml-auto text-gray-400 font-normal">Core</span>
                            </div>
                        </div>
                    <?php else : ?>
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
            <div class="<?php echo esc_attr($styles['pagination_wrap']); ?>">
                <?php
                $output = $this->paginate_links(
                    intval($page),
                    intval($total_pages),
                    esc_url($current_url),
                    esc_html(sanitize_text_field($username)),
                    intval($is_shortcode)
                );
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