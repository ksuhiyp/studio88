<?php
namespace UiCore;

use Elementor\Control_Select2;
defined('ABSPATH') || exit();

class Query extends Control_Select2
{
    const CONTROL_ID = 'query';

    public function get_type()
    {
        return self::CONTROL_ID;
    }
}

\Elementor\Plugin::$instance->controls_manager->register_control('query', new Query());
