<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Select2 extends Component
{
    public function __construct(
        public string $name,
        public string $id = '',
        public string $placeholder = 'Chọn...',
        public bool $multiple = false,
        public bool $showSelectAll = false,
        public bool $required = false,
        public bool $disabled = false,
        public ?string $label = null,
        public string $labelClass = '',
        public array $options = [],
        public mixed $selected = null,
        public bool $userTemplate = false,
    ) {
        $this->id = $id ?: $name;
        $this->selected = is_array($selected) ? $selected : (old($name) ?? ($selected ? [$selected] : []));
    }

    public function render(): View
    {
        return view('components.select2');
    }
}
