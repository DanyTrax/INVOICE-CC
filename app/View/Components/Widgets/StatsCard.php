<?php

namespace App\View\Components\Widgets;

use Illuminate\View\Component;

class StatsCard extends Component
{
    public function __construct(
        public string $title,
        public string|int $value,
        public string $icon = 'chart-bar',
        public string $color = 'blue',
        public ?string $link = null,
        public ?string $subtitle = null,
    ) {}

    public function render()
    {
        return view('components.widgets.stats-card');
    }
}
