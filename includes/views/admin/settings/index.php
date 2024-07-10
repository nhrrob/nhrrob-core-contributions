<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>

<div class="wrap">
    <h1><?php esc_html_e('Core Contributions', 'nhrrob-core-contributions'); ?></h1>

    <form method="post" action="">
        <label for="nhrcc_username"><?php esc_html_e('Enter WordPress.org Username:', 'nhrrob-core-contributions'); ?></label>
        <input type="text" id="nhrcc_username" name="nhrcc_username" value="<?php echo isset($username) ? esc_attr($username) : ''; ?>" required>
        <button type="submit"><?php esc_html_e('Get Contributions', 'nhrrob-core-contributions'); ?></button>
    </form>

    <?php if ($username) : ?>
        <h2><?php printf(esc_html__('Contributions for %s', 'nhrrob-core-contributions'), esc_html($username)); ?></h2>
        
        <?php if (isset($total_contribution_count)) : ?>
            <p><?php printf(esc_html__('Total Contributions: %d', 'nhrrob-core-contributions'), $total_contribution_count); ?></p>
        <?php endif; ?>

        <?php if (!empty($core_contributions) && is_array($core_contributions)) : ?>
            <ul class="nhrcc-contributions-list">
                <?php foreach ($core_contributions as $contribution) : ?>
                    <li>
                        <a href="<?php echo esc_url($contribution['link']); ?>" target="_blank">
                            <?php echo esc_html($contribution['description']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p>
                <a href="<?php echo esc_url("https://core.trac.wordpress.org/search?q=props+$username&noquickjump=1&changeset=on"); ?>" target="_blank">
                    <?php esc_html_e('See the full list of contributions', 'nhrrob-core-contributions'); ?>
                </a>
            </p>
        <?php else : ?>
            <p><?php esc_html_e('No contributions found for this user.', 'nhrrob-core-contributions'); ?></p>
        <?php endif; ?>
    <?php endif; ?>
</div>
