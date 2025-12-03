<?php

declare(strict_types=1);

/**
 * Asyntai - AI Chatbot for Bagisto
 *
 * Admin routes
 *
 * @category  Asyntai
 * @package   Asyntai\Chatbot
 * @author    Asyntai <hello@asyntai.com>
 * @copyright Copyright (c) Asyntai
 * @license   MIT License
 */

use Illuminate\Support\Facades\Route;
use Asyntai\Chatbot\Http\Controllers\Admin\SettingsController;

Route::group([
    'prefix' => config('app.admin_url', 'admin') . '/asyntai/chatbot',
    'middleware' => ['web', 'admin'],
], function () {
    // Settings page
    Route::get('settings', [SettingsController::class, 'index'])
        ->name('admin.asyntai.chatbot.settings');

    // API endpoints for saving/resetting connection
    Route::post('api/save', [SettingsController::class, 'save'])
        ->name('admin.asyntai.chatbot.api.save');

    Route::post('api/reset', [SettingsController::class, 'reset'])
        ->name('admin.asyntai.chatbot.api.reset');
});
