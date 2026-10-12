<?php

// Menghubungkan komponen x-app-layout dengan kerangka halaman pengguna yang login.

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public function render(): View
    {
        return view('layouts.app');
    }
}
