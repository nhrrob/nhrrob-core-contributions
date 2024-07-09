<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly 
?>

<div class="wrap">
    <h1><?php esc_html_e('Core Contributions', 'nhrrob-core-contributions'); ?></h1>

    <ul class="nhrcc-contributions-list">
        <?php foreach ($core_contributions as $contribution) : ?>
            <li>
                <a href="<?php echo esc_attr( $contribution['link'] ); ?>" target="_blank">
                    <?php echo esc_html($contribution['description']); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>