
<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

/**
 * Stat Component
 *
 * Displays a statistic card with title, value, optional icon and trend
 */
class Stat extends Component
{
    /**
     * The statistic title
     *
     * @var string
     */
    public string $title;

    /**
     * The statistic value
     *
     * @var string|int
     */
    public string|int $value;

    /**
     * The optional icon class
     *
     * @var string|null
     */
    public ?string $icon;

    /**
     * The optional color
     *
     * @var string
     */
    public string $color;

    /**
     * The optional change percentage or value
     *
     * @var string|int|null
     */
    public string|int|null $change;

    /**
     * Create a new component instance.
     *
     * @param string $title
     * @param string|int $value
     * @param string|null $icon
     * @param string $color
     * @param string|int|null $change
     * @return void
     */
    public function __construct(
        string $title,
        string|int $value,
        ?string $icon = null,
        string $color = 'primary',
        string|int|null $change = null
    ) {
        $this->title = $title;
        $this->value = $value;
        $this->icon = $icon;
        $this->color = $color;
        $this->change = $change;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.stat');
    }
}
