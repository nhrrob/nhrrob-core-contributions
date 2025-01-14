<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>

<?php 
// Get the current page URL
global $wp;
                    
$current_url = is_admin() ? admin_url("tools.php?page={$this->page_slug}") : home_url(add_query_arg(array(), $wp->request));
$is_shortcode = ! is_admin() ? 1 : 0;
$hide_on_shortcode = $is_shortcode ? 'hidden' : '';

$is_block_editor = defined('REST_REQUEST') && REST_REQUEST && strpos(wp_get_referer(), 'post.php') !== false;

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

    <?php if ($username) : ?>
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <?php if ($total_contribution_count > 0) : ?>
                <p class="mb-4 text-gray-700">
            		<?php 
                    /* translators: %d: Total contributions count */
                    // $display_name = $this->get_wporg_display_name($username);
                    printf(__('Core Contributions (<code>%s</code>): %d', 'nhrrob-core-contributions'), esc_attr( $username ), intval( $total_contribution_count ) ); 
                    ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($core_contributions) && $total_contribution_count > 0) : ?>
                <ul class="nhrcc-contributions-list list-disc pl-5 space-y-2 text-gray-700">
                    <?php foreach ($core_contributions as $contribution) : ?>
                        <li>
                            <a href="<?php echo esc_url($contribution['link']); ?>" target="_blank" class="text-blue-500 hover:underline">
                                <?php echo esc_html($contribution['description']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="pagination flex flex-wrap items-center space-x-4">
                    <?php
                    $contributions_per_page = 10; // Define the number of contributions per page
                    $total_pages = ceil($total_contribution_count / $contributions_per_page);

                    // Call the paginate_links function with the username
                    if ( $total_pages > 1 && !$is_block_editor ) {
                        $output = $this->paginate_links( intval( $page ), intval( $total_pages ), esc_url( $current_url ), esc_html( sanitize_text_field( $username ) ), intval( $is_shortcode ));
                        echo wp_kses( $output, $this->allowed_html() );
                    }
                    ?>
                </div>

            <?php else : ?>
                <p class="text-red-500"><?php esc_html_e('No contributions found for this user.', 'nhrrob-core-contributions'); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
