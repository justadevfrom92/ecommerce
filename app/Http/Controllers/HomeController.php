<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\HomeSlider;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** Keyword => Bootstrap Icons name, for category tiles (department pages). */
    private const ICONS = [
        'electronic' => 'cpu', 'phone' => 'phone', 'laptop' => 'laptop', 'audio' => 'headphones', 'camera' => 'camera',
        'cloth' => 'bag', 'fashion' => 'bag', 'men' => 'person', 'women' => 'person-heart', 'kid' => 'balloon', 'shoe' => 'tags',
        'home' => 'house', 'kitchen' => 'cup-hot', 'furniture' => 'lamp', 'cookware' => 'fire', 'bedding' => 'moon-stars', 'decor' => 'flower1',
        'sport' => 'trophy', 'fitness' => 'heart-pulse', 'camping' => 'tree', 'cycling' => 'bicycle', 'team' => 'dribbble',
        'grocery' => 'basket', 'toy' => 'controller', 'book' => 'book', 'beauty' => 'stars', 'garden' => 'flower2', 'tool' => 'tools',
    ];

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

    public static function iconFor(string $name): string
    {
        $name = strtolower($name);
        foreach (self::ICONS as $needle => $icon) {
            if (str_contains($name, $needle)) {
                return $icon;
            }
        }

        return 'tag';
    }
}
