<?php

declare(strict_types=1);

/**
 * Asyntai - AI Chatbot for Bagisto
 *
 * Middleware to inject the chat widget into shop pages
 *
 * @category  Asyntai
 * @package   Asyntai\Chatbot
 * @author    Asyntai <hello@asyntai.com>
 * @copyright Copyright (c) Asyntai
 * @license   MIT License
 */

namespace Asyntai\Chatbot\Http\Middleware;

use Asyntai\Chatbot\Services\ConfigService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectChatWidget
{
    /**
     * Constructor.
     */
    public function __construct(
        protected ConfigService $configService
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Skip admin routes - only inject on shop/frontend pages
        if ($this->isAdminRoute($request)) {
            return $response;
        }

        // Skip API routes
        if ($this->isApiRoute($request)) {
            return $response;
        }

        // Only inject into HTML responses
        if (!$this->shouldInject($response)) {
            return $response;
        }

        // Check if connected
        if (!$this->configService->isConnected()) {
            return $response;
        }

        $content = $response->getContent();

        // Check if script is already injected (to avoid duplicates)
        if (str_contains($content, 'data-asyntai-id')) {
            return $response;
        }

        // Inject the widget script before closing </body> tag
        $siteId = $this->configService->getSiteId();
        $scriptUrl = $this->configService->getScriptUrl();

        $script = '<script src="' . e($scriptUrl) . '" async defer data-asyntai-id="' . e($siteId) . '"></script>';

        // Insert before </body>
        $content = str_ireplace('</body>', $script . '</body>', $content);

        $response->setContent($content);

        return $response;
    }

    /**
     * Check if we should inject the widget.
     */
    protected function shouldInject(Response $response): bool
    {
        // Only inject into HTML responses
        $contentType = $response->headers->get('Content-Type', '');

        if (!str_contains($contentType, 'text/html') && !empty($contentType)) {
            return false;
        }

        // Check if response has content
        $content = $response->getContent();
        if (empty($content)) {
            return false;
        }

        // Check if it's an HTML page with a body tag
        if (!str_contains(strtolower($content), '</body>')) {
            return false;
        }

        return true;
    }

    /**
     * Check if the current request is for an admin route.
     */
    protected function isAdminRoute(Request $request): bool
    {
        $adminUrl = config('app.admin_url', 'admin');
        $path = $request->path();

        return str_starts_with($path, $adminUrl) || str_starts_with($path, '/' . $adminUrl);
    }

    /**
     * Check if the current request is for an API route.
     */
    protected function isApiRoute(Request $request): bool
    {
        $path = $request->path();

        return str_starts_with($path, 'api') || str_starts_with($path, '/api');
    }
}
