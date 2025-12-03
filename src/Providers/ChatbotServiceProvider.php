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

namespace Asyntai\Chatbot\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class ChatbotServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin-routes.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'asyntai-chatbot');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'asyntai-chatbot');

        // Publish assets
        $this->publishes([
            __DIR__ . '/../Resources/assets' => public_path('vendor/asyntai/chatbot'),
        ], 'asyntai-chatbot-assets');

        // Register middleware for shop routes to inject the chatbot widget
        $this->registerMiddleware();
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge admin menu config
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/admin-menu.php',
            'menu.admin'
        );

        // Register the config service as singleton
        $this->app->singleton(\Asyntai\Chatbot\Services\ConfigService::class, function ($app) {
            return new \Asyntai\Chatbot\Services\ConfigService();
        });
    }

    /**
     * Register the middleware for shop routes.
     */
    protected function registerMiddleware(): void
    {
        /** @var Router $router */
        $router = $this->app['router'];

        // Add middleware to web group for shop pages
        // The middleware will automatically skip admin routes
        $router->pushMiddlewareToGroup('web', \Asyntai\Chatbot\Http\Middleware\InjectChatWidget::class);
    }
}
