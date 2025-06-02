<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Core Contributions Block view template
 *
 * @var string $username
 * @var string $preset
 * @var array  $core_contributions
 * @var int    $total_contribution_count
 * @var int    $page
 * @var string $backgroundColor
 * @var string $textColor
 * @var string $linkColor
 * @var string $borderColor
 * @var int    $borderRadius
 * @var array  $padding
 * @var array  $margin
 * @var string $fontSize
 * @var string $fontWeight
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
];

$styles = $presets[$preset] ?? $presets['default'];

// Generate custom styles
$custom_styles = [];
if (!empty($backgroundColor)) {
    $custom_styles[] = "background-color: {$backgroundColor}";
}
if (!empty($textColor)) {
    $custom_styles[] = "color: {$textColor}";
}
if (!empty($borderColor)) {
    $custom_styles[] = "border-color: {$borderColor}";
}
if (!empty($borderRadius)) {
    $custom_styles[] = "border-radius: {$borderRadius}px";
}
if (!empty($fontSize)) {
    $custom_styles[] = "font-size: {$fontSize}";
}
if (!empty($fontWeight)) {
    $custom_styles[] = "font-weight: {$fontWeight}";
}

// Handle padding
if (!empty($padding) && is_array($padding)) {
    $padding_values = [];
    $padding_values[] = isset($padding['top']) ? $padding['top'] : '0';
    $padding_values[] = isset($padding['right']) ? $padding['right'] : '0';
    $padding_values[] = isset($padding['bottom']) ? $padding['bottom'] : '0';
    $padding_values[] = isset($padding['left']) ? $padding['left'] : '0';
    $custom_styles[] = "padding: " . implode(' ', $padding_values);
}

// Handle margin
if (!empty($margin) && is_array($margin)) {
    $margin_values = [];
    $margin_values[] = isset($margin['top']) ? $margin['top'] : '0';
    $margin_values[] = isset($margin['right']) ? $margin['right'] : '0';
    $margin_values[] = isset($margin['bottom']) ? $margin['bottom'] : '0';
    $margin_values[] = isset($margin['left']) ? $margin['left'] : '0';
    $custom_styles[] = "margin: " . implode(' ', $margin_values);
}

$style_attribute = !empty($custom_styles) ? 'style="' . esc_attr(implode('; ', $custom_styles)) . '"' : '';
?>

<div class="<?php echo esc_attr($styles['wrapper']); ?>" <?php echo $style_attribute; ?>>
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
                    <a href="<?php echo esc_url($contribution['link']); ?>"
                       target="_blank"
                       class="<?php echo esc_attr($styles['link']); ?>"
                       <?php echo !empty($linkColor) ? 'style="color: ' . esc_attr($linkColor) . '"' : ''; ?>>
                        <?php echo esc_html($contribution['description']); ?>
                    </a>
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