<?php

namespace Nhrcc\CoreContributions\Traits;

trait GlobalTrait
{
    use CoreContributionsTrait;
    
    public function dd($var)
    {
        echo "<pre>";
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

    public function paginate_links($page, $total_pages, $current_url) {
        $output = '';

        if ($page > 1) {
            $output .= '<a href="' . esc_url(add_query_arg('paged', $page - 1, $current_url)) . '" class="px-3 py-1 text-blue-500 hover:bg-blue-100 rounded">Previous</a>';
        }

        if ($page > 3) {
            $output .= '<a href="' . esc_url(add_query_arg('paged', 1, $current_url)) . '" class="px-3 py-1 text-blue-500 hover:bg-blue-100 rounded">1</a>';
            if ($page > 4) {
                $output .= '<span class="px-3 py-1">...</span>';
            }
        }

        for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++) {
            if ($i == $page) {
                $output .= '<span class="px-3 py-1 bg-gray-300 rounded">' . $i . '</span>';
            } else {
                $output .= '<a href="' . esc_url(add_query_arg('paged', $i, $current_url)) . '" class="px-3 py-1 text-blue-500 hover:bg-blue-100 rounded">' . $i . '</a>';
            }
        }

        if ($page < $total_pages - 2) {
            if ($page < $total_pages - 3) {
                $output .= '<span class="px-3 py-1">...</span>';
            }
            $output .= '<a href="' . esc_url(add_query_arg('paged', $total_pages, $current_url)) . '" class="px-3 py-1 text-blue-500 hover:bg-blue-100 rounded">' . $total_pages . '</a>';
        }

        if ($page < $total_pages) {
            $output .= '<a href="' . esc_url(add_query_arg('paged', $page + 1, $current_url)) . '" class="px-3 py-1 text-blue-500 hover:bg-blue-100 rounded">Next</a>';
        }

        return $output;
    }
    
}
