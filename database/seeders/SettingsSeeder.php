<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\HomeSlider;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Setting::DEFAULTS as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
        Setting::putMany([]); // clear cache

        if (HomeSlider::exists()) {
            return;
        }

        $sliders = [
            ['title' => 'Featured products', 'source' => 'featured'],
            ['title' => 'New arrivals', 'source' => 'new'],
            ['title' => 'On sale now', 'source' => 'sale'],
        ];

        foreach (Department::active()->orderBy('sort_order')->get() as $department) {
            $sliders[] = ['title' => $department->name, 'source' => 'department', 'source_id' => $department->id];
        }

        foreach ($sliders as $i => $slider) {
            HomeSlider::create([...$slider, 'sort_order' => $i]);
        }
    }
}
