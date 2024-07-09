<?php

namespace Nhrcc\CoreContributions;

use Nhrcc\CoreContributions\Traits\GlobalTrait;

/**
 * Controller Class
 */
class App {
    
    use GlobalTrait;
    
    protected $page_slug;
    
    public function __construct()
    {
        $this->page_slug = 'nhrcc-core-contributions';
    }
}
