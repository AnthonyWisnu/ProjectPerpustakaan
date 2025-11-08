
<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

/**
 * Modal Component
 *
 * Displays a modal dialog with customizable size
 */
class Modal extends Component
{
    /**
     * The modal ID
     *
     * @var string
     */
    public string $id;

    /**
     * The modal title
     *
     * @var string
     */
    public string $title;

    /**
     * The modal size (sm/md/lg/xl)
     *
     * @var string
     */
    public string $size;

    /**
     * Create a new component instance.
     *
     * @param string $id
     * @param string $title
     * @param string $size
     * @return void
     */
    public function __construct(
        string $id,
        string $title,
        string $size = 'md'
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->size = $size;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.modal');
    }
}
