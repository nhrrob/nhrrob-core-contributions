<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>

<div class="wrap">
    <h1><?php esc_html_e('Core Contributions', 'nhrrob-core-contributions'); ?></h1>

    <?php if (isset($total_contribution_count)) : ?>
        <p><?php printf(esc_html__('Total Contributions: %d', 'nhrrob-core-contributions'), $total_contribution_count); ?></p>
    <?php endif; ?>

    <?php if (!empty($core_contributions) && is_array($core_contributions)) : ?>
        <ul class="nhrcc-contributions-list">
            <?php foreach ($core_contributions as $contribution) : 
                // echo "<pre>";
                // print_r($core_contributions);
                // wp_die('ok');
                ?>
                <li>
                    <a href="<?php echo esc_url($contribution['link']); ?>" target="_blank">
                        <?php echo esc_html($contribution['description']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p><?php esc_html_e('No contributions found for this user.', 'nhrrob-core-contributions'); ?></p>
    <?php endif; ?>
</div>