<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteContent extends Model
{
    use HasFactory;

    protected $table = 'website_contents';

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Get a website content value by key, with a fallback.
     */
    public static function getValue(string $key, $default = null)
    {
        $content = self::where('key', $key)->first();
        if (!$content) {
            return $default;
        }

        if ($content->type === 'json') {
            return json_decode($content->value, true) ?: $default;
        }

        return $content->value;
    }

    /**
     * Set a website content value by key.
     */
    public static function setValue(string $key, $value, string $type = 'text', string $description = null)
    {
        $val = is_array($value) ? json_encode($value) : $value;
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $val,
                'type' => $type,
                'description' => $description,
            ]
        );
    }
}
