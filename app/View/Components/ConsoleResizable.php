<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ConsoleResizable extends Component
{
    /**
     * The console width (default: 25% of available space, max 50%)
     */
    public $width;

    /**
     * Create a new component instance.
     */
    public function __construct($width = '25%')
    {
        $this->width = $width;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.console-resizable', [
            'width' => $this->width
        ]);
    }
}
