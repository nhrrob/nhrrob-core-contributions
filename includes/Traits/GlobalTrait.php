<?php

namespace Nhrcc\CoreContributions\Traits;

trait GlobalTrait
{
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
            ),
        );

        $allowed_tags = array_merge( $allowed_tags, $allowed_tags_extra );

        return $allowed_tags;
    }
}
