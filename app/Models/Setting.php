<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description'
    ];

    // Method: Get setting by key
    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }
        
        // Cast value sesuai type
        return self::castValue($setting->value, $setting->type);
    }

    // Method: Set setting value
    public static function setValue($key, $value, $type = 'string')
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type
            ]
        );
    }

    // Method: Cast value sesuai type
    private static function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'number':
                return is_numeric($value) ? (float) $value : 0;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    // Method: Get settings by group
    public static function getByGroup($group)
    {
        return self::where('group', $group)->get()->mapWithKeys(function ($item) {
            return [$item->key => self::castValue($item->value, $item->type)];
        });
    }
}