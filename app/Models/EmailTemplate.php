<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'name', 'description', 'subject', 'body', 'button_text'])]
class EmailTemplate extends Model
{
    public function getRouteKeyName(): string
    {
        return 'key';
    }

    /**
     * Built-in templates: the code sends these by key, so admins edit their
     * wording but can't add or delete them. {{placeholders}} are listed per key.
     */
    public const DEFAULTS = [
        'welcome' => [
            'name' => 'Welcome',
            'description' => 'Sent right after someone signs up.',
            'placeholders' => ['name', 'store_name', 'shop_url'],
            'subject' => 'Welcome to {{store_name}}, {{name}}!',
            'body' => "Hi {{name}},\n\nThanks for creating an account with {{store_name}}. You can now track your orders and check out faster.\n\nHappy shopping!",
            'button_text' => 'Start shopping',
        ],
        'order_confirmation' => [
            'name' => 'Order confirmation',
            'description' => 'Sent when an order is paid.',
            'placeholders' => ['name', 'store_name', 'order_number', 'order_total', 'order_items', 'order_url'],
            'subject' => 'Order {{order_number}} confirmed',
            'body' => "Hi {{name}},\n\nThanks for your order! We've received your payment of {{order_total}} and are getting it ready.\n\n{{order_items}}\n\nWe'll email you again when it ships.",
            'button_text' => 'View your order',
        ],
        'order_status' => [
            'name' => 'Order status update',
            'description' => 'Sent when staff mark an order shipped, delivered, cancelled or refunded.',
            'placeholders' => ['name', 'store_name', 'order_number', 'order_status', 'order_url'],
            'subject' => 'Order {{order_number}}: {{order_status}}',
            'body' => "Hi {{name}},\n\nYour order {{order_number}} is now: {{order_status}}.\n\nIf you have any questions, just reply to this email.",
            'button_text' => 'View your order',
        ],
        'password_reset' => [
            'name' => 'Password reset',
            'description' => 'Sent from "Forgot password?".',
            'placeholders' => ['name', 'store_name', 'reset_url', 'expires_minutes'],
            'subject' => 'Reset your {{store_name}} password',
            'body' => "Hi {{name}},\n\nWe received a request to reset your password. The link below works for {{expires_minutes}} minutes.\n\nIf you didn't ask for this, you can ignore this email.",
            'button_text' => 'Reset password',
        ],
    ];

    public function placeholders(): array
    {
        return self::DEFAULTS[$this->key]['placeholders'] ?? [];
    }
}
