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

namespace Asyntai\Chatbot\Http\Controllers\Admin;

use Asyntai\Chatbot\Services\ConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SettingsController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct(
        protected ConfigService $configService
    ) {
    }

    /**
     * Display the settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('asyntai-chatbot::admin.settings.index', [
            'siteId' => $this->configService->getSiteId(),
            'accountEmail' => $this->configService->getAccountEmail(),
            'scriptUrl' => $this->configService->getScriptUrl(),
            'isConnected' => $this->configService->isConnected(),
        ]);
    }

    /**
     * Save the connection settings.
     */
    public function save(Request $request): JsonResponse
    {
        try {
            $data = $request->json()->all();

            if (!is_array($data)) {
                $data = $request->all();
            }

            $siteId = $data['site_id'] ?? null;

            if (empty($siteId)) {
                return response()->json([
                    'success' => false,
                    'error' => 'site_id is required',
                ], 400);
            }

            $scriptUrl = $data['script_url'] ?? null;
            $accountEmail = $data['account_email'] ?? null;

            $this->configService->saveSettings($siteId, $scriptUrl, $accountEmail);

            return response()->json([
                'success' => true,
                'site_id' => $siteId,
                'script_url' => $this->configService->getScriptUrl(),
                'account_email' => $accountEmail,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset the connection.
     */
    public function reset(): JsonResponse
    {
        try {
            $this->configService->resetSettings();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
