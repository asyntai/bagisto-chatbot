<?php

declare(strict_types=1);

/**
 * Asyntai - AI Chatbot for Bagisto
 *
 * @category  Asyntai
 * @package   Asyntai\Chatbot
 * @author    Asyntai <hello@asyntai.com>
 * @copyright Copyright (c) Asyntai
 * @license   MIT License
 */

namespace Asyntai\Chatbot\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'asyntai_config';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'config_key',
        'config_value',
    ];

    /**
     * Get a configuration value by key.
     */
    public static function getValue(string $key): ?string
    {
        $config = static::where('config_key', $key)->first();
        return $config?->config_value;
    }

    /**
     * Set a configuration value.
     */
    public static function setValue(string $key, ?string $value): void
    {
        static::updateOrCreate(
            ['config_key' => $key],
            ['config_value' => $value]
        );
    }

    /**
     * Delete a configuration by key.
     */
    public static function deleteByKey(string $key): void
    {
        static::where('config_key', $key)->delete();
    }
}
