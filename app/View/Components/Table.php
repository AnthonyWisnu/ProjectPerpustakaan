
<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

/**
 * Table Component
 *
 * Displays a data table with headers, rows and optional action column
 */
class Table extends Component
{
    /**
     * The table headers
     *
     * @var array
     */
    public array $headers;

    /**
     * The table rows
     *
     * @var Collection|array
     */
    public Collection|array $rows;

    /**
     * Whether to show actions column
     *
     * @var bool
     */
    public bool $actions;

    /**
     * Create a new component instance.
     *
     * @param array $headers
     * @param Collection|array $rows
     * @param bool $actions
     * @return void
     */
    public function __construct(
        array $headers,
        Collection|array $rows,
        bool $actions = false
    ) {
        $this->headers = $headers;
        $this->rows = $rows;
        $this->actions = $actions;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.table');
    }
}
