<?php

namespace Nhrcc\CoreContributions\Traits;

trait GlobalTrait
{
    use CoreContributionsTrait;
    
    public function dd($var)
    {
        echo "<pre>";

        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
        print_r($var);
        
        wp_die('ok');
    }

    public function allowed_html(){
        $allowed_tags = wp_kses_allowed_html('post');

        $allowed_tags_extra = array(
            // 'li'   => array( 'class' => 1 ),
            // 'div'  => array( 'class' => 1 ),
            // 'span' => array( 'class' => 1 ),
            'a' => array(
                'href'            => 1,
                'class'           => 1,
                'id'              => 1,
                'target'          => 1,
            ),
            // 'img'  => array(
            //     'src'     => 1,
            //     'class'   => 1,
            //     'loading' => 1,
            // ),
            'svg' => array(
                'class' => 1,
                'xmlns' => 1,
                'aria-hidden' => 1,
                'aria-labelledby' => 1,
                'fill' => 1,
                'role' => 1,
                'width' => 1,
                'height' => 1,
                'viewbox' => 1,
                'stroke-width' => 1,
                'stroke' => 1,
            ),
            'g' => array(
                'fill' => 1,
            ),
            'title' => array(
                'title' => 1,
            ),
            'path' => array(
                'stroke-linecap' => 1,
                'stroke-linejoin' => 1,
                'd' => 1,
                'fill' => 1,
            ),
            'input' => array(
                'class' => 1,
                'type' => 1,
                'name' => 1,
                'placeholder' => 1,
                'value' => 1,
                'id' => 1,
                'required' => 1,
            ),
            'select' => array(
                'class' => 1,
                'name' => 1,
                'id' => 1,
                'required' => 1,
            ),
            'option' => array(
                'value' => 1,
                'selected' => 1,
            ),
            'form' => array(
                'action' => 1,
                'method' => 1,
                'id' => 1,
                'class' => 1,
            ),
        );

        $allowed_tags = array_merge( $allowed_tags, $allowed_tags_extra );

        return $allowed_tags;
    }

    public function paginate_links($current_page, $total_pages, $current_url, $username, $shortcode=0) {
        $output = '';
        $paged = $shortcode ? esc_html( 'front_paged' ) : esc_html( 'paged' );
        $link_classes = 'px-3 py-1 border border-gray-300 rounded-md text-gray-700 text-base bg-white hover:bg-gray-100 hover:border-gray-400 transition !no-underline';
        
        if ($current_page > 1) {
            $output .= '<a href="' . esc_url(add_query_arg(array( $paged => intval( $current_page ) - 1, 'nhrcc_username' => sanitize_text_field( $username ), 'nhrcc_form_nonce' => wp_create_nonce('nhrcc_form_action') ), $current_url)) . '" class="' . esc_attr( $link_classes ) . '">' . esc_html__('Previous', 'nhrrob-core-contributions') . '</a>';
        }
    
        if ($current_page > 3) {
            $output .= '<a href="' . esc_url(add_query_arg(array( $paged => 1, 'nhrcc_username' => sanitize_text_field( $username ), 'nhrcc_form_nonce' => wp_create_nonce('nhrcc_form_action') ), $current_url)) . '" class="' . esc_attr( $link_classes ) . '">1</a>';
            if ($current_page > 4) {
                $output .= '<span class="px-3 py-1 text-base">...</span>';
            }
        }
    
        for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++) {
            if ($i == $current_page) {
                $output .= '<span class="px-3 py-1 bg-gray-200 rounded text-base">' . intval( $i ) . '</span>';
            } else {
                $output .= '<a href="' . esc_url(add_query_arg(array($paged => intval( $i ), 'nhrcc_username' => sanitize_text_field( $username ), 'nhrcc_form_nonce' => wp_create_nonce('nhrcc_form_action') ), $current_url)) . '" class="' . esc_attr( $link_classes ) . '">' . intval( $i ) . '</a>';
            }
        }
    
        if ($current_page < $total_pages - 2) {
            if ($current_page < $total_pages - 3) {
                $output .= '<span class="px-3 py-1 text-base">...</span>';
            }
            $output .= '<a href="' . esc_url(add_query_arg(array( $paged => intval( $total_pages ), 'nhrcc_username' => sanitize_text_field( $username ), 'nhrcc_form_nonce' => wp_create_nonce('nhrcc_form_action') ), $current_url)) . '" class="' . esc_attr( $link_classes ) . '">' . intval( $total_pages ) . '</a>';
        }
    
        if ($current_page < $total_pages) {
            $output .= '<a href="' . esc_url(add_query_arg(array( $paged => intval( $current_page ) + 1, 'nhrcc_username' => sanitize_text_field( $username ), 'nhrcc_form_nonce' => wp_create_nonce('nhrcc_form_action') ), $current_url)) . '" class="' . esc_attr( $link_classes ) . '">' . esc_html__('Next', 'nhrrob-core-contributions') . '</a>';
        }
    
        return $output;
    }    
    
}
