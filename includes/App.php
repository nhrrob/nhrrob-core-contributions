<?php
namespace Nhrcc\CoreContributions;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

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
