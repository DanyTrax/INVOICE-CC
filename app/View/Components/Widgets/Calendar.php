<?php

namespace App\View\Components\Widgets;

use Illuminate\View\Component;

class Calendar extends Component
{
    public function __construct(
        public array $events = [],
        public bool $showNavigation = true,
    ) {}

    public function render()
    {
        return view('components.widgets.calendar');
    }
}
