<?php

// Menghubungkan komponen x-guest-layout dengan kerangka halaman autentikasi.

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    public function render(): View
    {
        return view('layouts.guest');
    }
}
