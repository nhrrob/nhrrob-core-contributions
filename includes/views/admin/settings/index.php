<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>

<div class="wrap p-6 max-w-4xl mx-auto">
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">
            <?php echo esc_html__('Core Contributions', 'nhrrob-core-contributions'); ?>
        </h2>

        <form method="post" action="" class="space-y-4">
            <div>
                <input type="text" id="nhrcc_username" name="nhrcc_username" value="<?php echo esc_attr($username); ?>" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
        </form>
    </div>

    <?php if ($username) : ?>
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <?php if ($total_contribution_count > 0) : ?>
                <p class="mb-4 text-gray-700">
                    <?php printf(esc_html__('Total Contributions: %d', 'nhrrob-core-contributions'), intval( $total_contribution_count ) ); ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($core_contributions)) : ?>
                <ul class="nhrcc-contributions-list list-disc pl-5 mb-6 space-y-2 text-gray-700">
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

                    // Get the current page URL
                    global $wp;
                    
                    $current_url = is_admin() ? admin_url("admin.php?page={$this->page_slug}") : home_url(add_query_arg(array(), $wp->request));
                    $is_shortcode = ! is_admin() ? 1 : 0;
                    // Call the paginate_links function with the username
                    echo $this->paginate_links( intval( $page ), intval( $total_pages ), esc_url( $current_url ), sanitize_text_field( $username ), intval( $is_shortcode ));
                    ?>
                </div>

            <?php else : ?>
                <p class="text-red-500"><?php esc_html_e('No contributions found for this user.', 'nhrrob-core-contributions'); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
