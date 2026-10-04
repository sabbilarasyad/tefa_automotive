<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        // Placeholder gallery images - in production, this would come from a gallery model
        $images = [
            ['url' => 'https://images.unsplash.com/photo-1583121274602-3e2820c69888?w=800', 'alt' => 'Bengkel TEFA Automotive'],
            ['url' => 'https://images.unsplash.com/photo-1623992949408-6e9d1f9a2e71?w=800', 'alt' => 'Area servis'],
            ['url' => 'https://images.unsplash.com/photo-1617814076367-b759c7d7e81b?w=800', 'alt' => 'Sparepart'],
            ['url' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=800', 'alt' => 'Tim mekanik'],
        ];

        return view('public.gallery', compact('images'));
    }
}