
<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

/**
 * Alert Component
 *
 * Displays alert messages with different types and optional dismiss functionality
 */
class Alert extends Component
{
    /**
     * The type of alert (success/error/warning/info)
     *
     * @var string
     */
    public string $type;

    /**
     * The alert message
     *
     * @var string
     */
    public string $message;

    /**
     * Whether the alert can be dismissed
     *
     * @var bool
     */
    public bool $dismissible;

    /**
     * Create a new component instance.
     *
     * @param string $type
     * @param string $message
     * @param bool $dismissible
     * @return void
     */
    public function __construct(
        string $message,
        string $type = 'info',
        bool $dismissible = true
    ) {
        $this->type = $type;
        $this->message = $message;
        $this->dismissible = $dismissible;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.alert');
    }
}
