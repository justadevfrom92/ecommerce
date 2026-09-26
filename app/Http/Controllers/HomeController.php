<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\HomeSlider;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $sliders = HomeSlider::active()->get()
            ->map(fn (HomeSlider $slider) => [
                'title' => $slider->title,
                'products' => $slider->products(),
                'viewAll' => $slider->viewAllUrl(),
            ]);

        $departments = Department::active()->orderBy('sort_order')->orderBy('name')->withCount('products')->get();

        return view('store.home', compact('sliders', 'departments'));
    }
}
