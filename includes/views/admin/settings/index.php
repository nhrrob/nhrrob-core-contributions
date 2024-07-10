<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>

<div class="wrap p-6 max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-4"><?php esc_html_e('Core Contributions', 'nhrrob-core-contributions'); ?></h1>

    <form method="post" action="" class="mb-6">
        <label for="nhrcc_username" class="block text-lg font-medium text-gray-700 mb-2">
            <?php esc_html_e('Enter WordPress.org Username:', 'nhrrob-core-contributions'); ?>
        </label>
        <input type="text" id="nhrcc_username" name="nhrcc_username" value="<?php echo esc_attr($username); ?>" 
               class="w-full p-2 border border-gray-300 rounded-md mb-4" required>
        <button type="submit" 
                class="px-4 py-2 bg-blue-500 text-white font-semibold rounded-md hover:bg-blue-600">
            <?php esc_html_e('Get Contributions', 'nhrrob-core-contributions'); ?>
        </button>
    </form>

    <?php if ($username) : ?>
        <h2 class="text-xl font-semibold mb-4">
            <?php printf(esc_html__('Contributions for %s', 'nhrrob-core-contributions'), esc_html($username)); ?>
        </h2>

        <?php if ($total_contribution_count > 0) : ?>
            <p class="mb-4">
                <?php printf(esc_html__('Total Contributions: %d', 'nhrrob-core-contributions'), $total_contribution_count); ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($core_contributions)) : ?>
            <ul class="nhrcc-contributions-list list-disc pl-5 mb-6">
                <?php foreach ($core_contributions as $contribution) : ?>
                    <li class="mb-2">
                        <a href="<?php echo esc_url($contribution['link']); ?>" target="_blank" class="text-blue-500 hover:underline">
                            <?php echo esc_html($contribution['description']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php // Pagination links ?>
            <div class="pagination flex flex-wrap items-center space-x-4">
                <?php
                // Get the current page URL
                $current_url = admin_url("admin.php?page={$this->page_slug}");

                // Function to generate pagination links
                echo $this->paginate_links($page, $total_pages, $current_url);
                ?>
            </div>

        <?php else : ?>
            <p class="text-red-500"><?php esc_html_e('No contributions found for this user.', 'nhrrob-core-contributions'); ?></p>
        <?php endif; ?>

    <?php endif; ?>
</div>
