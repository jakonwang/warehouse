<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Services\TranslationService;

class Lang extends Component
{
    public $key;
    public $params;
    public $fallback;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($key, $params = [], $fallback = null)
    {
        $this->key = $key;
        $this->params = is_array($params) ? $params : [];
        $this->fallback = $fallback;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Closure|string
     */
    public function render()
    {
        return function (array $data) {
            return TranslationService::get($this->key, $this->params, $this->fallback);
        };
    }
}
