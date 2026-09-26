<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

/** Creates any missing built-in templates; never overwrites edited wording. */
class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach (EmailTemplate::DEFAULTS as $key => $template) {
            EmailTemplate::firstOrCreate(['key' => $key], [
                'name' => $template['name'],
                'description' => $template['description'],
                'subject' => $template['subject'],
                'body' => $template['body'],
                'button_text' => $template['button_text'],
            ]);
        }
    }
}
