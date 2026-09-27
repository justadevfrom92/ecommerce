<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\HomeSlider;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $sliders = HomeSlider::active()->get()
            ->map(fn (HomeSlider $slider) => [
                'title' => $slider->title,
                'icon' => $slider->source === 'sale' ? 'lightning-charge-fill' : null,
                'products' => $slider->products(),
                'viewAll' => $slider->viewAllUrl(),
            ]);

        $departments = Department::active()->orderBy('sort_order')->orderBy('name')->get();

        $maxDiscount = (int) round(Product::active()->whereNotNull('compare_at_price')->whereColumn('compare_at_price', '>', 'price')
            ->selectRaw('MAX(1 - price / compare_at_price) * 100 as pct')->value('pct') ?? 0);

        return view('store.home', [
            'sliders' => $sliders,
            'maxDiscount' => $maxDiscount,
            'spotlight' => $departments->first(),
        ]);
    }
}
