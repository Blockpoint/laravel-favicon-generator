<?php

namespace Blockpoint\LaravelFaviconGenerator\View\Components;

use Illuminate\View\Component;

class FaviconMeta extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('favicon-generator::components.favicon-meta');
    }
}
