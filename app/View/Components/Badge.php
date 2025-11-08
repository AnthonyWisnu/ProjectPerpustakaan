
<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

/**
 * Badge Component
 *
 * Displays a badge with different types and colors
 */
class Badge extends Component
{
    /**
     * The type of badge (primary/secondary/success/danger/warning/info)
     *
     * @var string
     */
    public string $type;

    /**
     * The badge text
     *
     * @var string
     */
    public string $text;

    /**
     * Create a new component instance.
     *
     * @param string $type
     * @param string $text
     * @return void
     */
    public function __construct(
        string $text,
        string $type = 'primary'
    ) {
        $this->type = $type;
        $this->text = $text;
    }

    /**
     * Get CSS classes based on badge type
     *
     * @return string
     */
    public function getCssClasses(): string
    {
        $baseClasses = 'badge';

        $typeClasses = [
            'primary' => 'badge-primary',
            'secondary' => 'badge-secondary',
            'success' => 'badge-success',
            'danger' => 'badge-danger',
            'warning' => 'badge-warning',
            'info' => 'badge-info',
        ];

        return $baseClasses . ' ' . ($typeClasses[$this->type] ?? $typeClasses['primary']);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.badge');
    }
}
