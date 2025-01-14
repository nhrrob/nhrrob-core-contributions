<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly 

// Get the current page URL
global $wp;
                    
$current_url = is_admin() ? admin_url("tools.php?page={$this->page_slug}") : home_url(add_query_arg(array(), $wp->request));
$is_shortcode = ! is_admin() ? 1 : 0;
$hide_on_shortcode = $is_shortcode ? 'hidden' : '';

$is_block_editor = defined('REST_REQUEST') && REST_REQUEST && strpos(wp_get_referer(), 'post.php') !== false;


$preset = isset($preset) ? $preset : 'default';
$presets = [
    'default' => [
        'wrapper' => 'nhrcc-preset-default bg-white shadow-lg rounded-lg p-6 max-w-4xl mx-auto',
        'header' => 'flex items-center gap-3 mb-6 border-b pb-4',
        'title' => 'text-2xl font-semibold text-gray-800',
        'list' => 'space-y-3',
        'item' => 'flex items-start gap-2 text-gray-700 hover:bg-gray-50 p-2 rounded transition-colors',
        'link' => 'text-blue-600 hover:text-blue-800 hover:underline'
    ],
    'modern' => [
        'wrapper' => 'nhrcc-preset-modern bg-gradient-to-br from-blue-50 to-indigo-50 shadow-xl rounded-xl p-8 max-w-4xl mx-auto',
        'header' => 'flex items-center gap-3 mb-8',
        'title' => 'text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600',
        'list' => 'space-y-4',
        'item' => 'bg-white shadow-sm rounded-lg p-4 hover:shadow-md transition-shadow',
        'link' => 'text-indigo-600 hover:text-indigo-800'
    ],
    'minimal' => [
        'wrapper' => 'nhrcc-preset-minimal max-w-4xl mx-auto p-6',
        'header' => 'flex items-center gap-2 mb-6',
        'title' => 'text-xl font-medium text-gray-800',
        'list' => 'space-y-2',
        'item' => 'border-l-2 border-gray-200 pl-4 py-2 hover:border-gray-400 transition-colors',
        'link' => 'text-gray-700 hover:text-gray-900'
    ],
    'compact' => [
        'wrapper' => 'nhrcc-preset-compact bg-gray-50 rounded p-4 max-w-4xl mx-auto',
        'header' => 'flex items-center gap-2 mb-4',
        'title' => 'text-lg font-medium text-gray-700',
        'list' => 'grid gap-2',
        'item' => 'text-sm bg-white rounded p-2 shadow-sm',
        'link' => 'text-gray-600 hover:text-gray-900'
    ]
];

$styles = $presets[$preset] ?? $presets['default'];
?>

<div class="wrap p-6 max-w-4xl mx-auto">
    <div class="bg-white shadow rounded-lg p-6 mb-6 <?php echo esc_attr( $hide_on_shortcode ); ?>">
        <h2 class="text-2xl font-semibold text-gray-800">
            <?php echo esc_html__('WordPress Core Contributions', 'nhrrob-core-contributions'); ?>
        </h2>

        <form method="post" action="<?php echo esc_url( $current_url ); ?>" class="space-y-4 mt-4 <?php echo esc_attr( $hide_on_shortcode ); ?>">
            <div>
                <?php wp_nonce_field('nhrcc_form_action', 'nhrcc_form_nonce'); ?>
                <input type="text" id="nhrcc_username" name="nhrcc_username" value="<?php echo esc_attr($username); ?>" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
        </form>
    </div>
</div>

<div class="<?php echo esc_attr($styles['wrapper']); ?>">
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
                       class="<?php echo esc_attr($styles['link']); ?>">
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
            <div class="pagination flex flex-wrap items-center space-x-4 mt-6">
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
            <p class="text-gray-500 mt-4">Pagination is available on frontend only!</p>
        <?php endif; ?>
    <?php else : ?>
        <p class="text-red-500"><?php esc_html_e('No contributions found for this user.', 'nhrrob-core-contributions'); ?></p>
    <?php endif; ?>
</div>