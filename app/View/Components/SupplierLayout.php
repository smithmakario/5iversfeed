<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class SupplierLayout extends Component
{
    public function render(): View
    {
        return view('layouts.supplier');
    }
}
