<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>

<div class="wrap">
    <h1><?php esc_html_e('Core Contributions', 'nhrrob-core-contributions'); ?></h1>

    <form method="post" action="">
        <label for="nhrcc_username"><?php esc_html_e('Enter WordPress.org Username:', 'nhrrob-core-contributions'); ?></label>
        <input type="text" id="nhrcc_username" name="nhrcc_username" value="<?php echo esc_attr($username); ?>" required>
        <button type="submit"><?php esc_html_e('Get Contributions', 'nhrrob-core-contributions'); ?></button>
    </form>

    <?php if ($username) : ?>
        <h2><?php printf(esc_html__('Contributions for %s', 'nhrrob-core-contributions'), esc_html($username)); ?></h2>

        <?php if ($total_contribution_count > 0) : ?>
            <p><?php printf(esc_html__('Total Contributions: %d', 'nhrrob-core-contributions'), $total_contribution_count); ?></p>
        <?php endif; ?>

        <?php if (!empty($core_contributions)) : ?>
            <ul class="nhrcc-contributions-list">
                <?php foreach ($core_contributions as $contribution) : ?>
                    <li>
                        <a href="<?php echo esc_url($contribution['link']); ?>" target="_blank">
                            <?php echo esc_html($contribution['description']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php // Pagination links ?>
            <div class="pagination">
                <?php
                // Get the current page URL
                $current_url = admin_url("admin.php?page={$this->page_slug}");
                
                for ($i = 1; $i <= $total_pages; $i++) {
                    if ($i == $page) {
                        echo '<span>' . $i . '</span>';
                    } else {
                        echo '<a href="' . esc_url(add_query_arg('paged', $i, $current_url)) . '">' . $i . '</a>';
                    }
                    
                    if ($i < $total_pages) {
                        echo ' | ';
                    }
                }
                ?>
            </div>

        <?php else : ?>
            <p><?php esc_html_e('No contributions found for this user.', 'nhrrob-core-contributions'); ?></p>
        <?php endif; ?>

    <?php endif; ?>
</div>
