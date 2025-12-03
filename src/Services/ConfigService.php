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

namespace Asyntai\Chatbot\Services;

use Asyntai\Chatbot\Models\Config;
use Illuminate\Support\Facades\Schema;

class ConfigService
{
    private const KEY_SITE_ID = 'site_id';
    private const KEY_SCRIPT_URL = 'script_url';
    private const KEY_ACCOUNT_EMAIL = 'account_email';
    private const DEFAULT_SCRIPT_URL = 'https://asyntai.com/static/js/chat-widget.js';

    /**
     * Get the site ID.
     */
    public function getSiteId(): ?string
    {
        return $this->getValue(self::KEY_SITE_ID);
    }

    /**
     * Get the script URL.
     */
    public function getScriptUrl(): string
    {
        return $this->getValue(self::KEY_SCRIPT_URL) ?? self::DEFAULT_SCRIPT_URL;
    }

    /**
     * Get the account email.
     */
    public function getAccountEmail(): ?string
    {
        return $this->getValue(self::KEY_ACCOUNT_EMAIL);
    }

    /**
     * Check if the chatbot is connected.
     */
    public function isConnected(): bool
    {
        $siteId = $this->getSiteId();
        return $siteId !== null && $siteId !== '';
    }

    /**
     * Save settings.
     */
    public function saveSettings(string $siteId, ?string $scriptUrl = null, ?string $accountEmail = null): void
    {
        $this->setValue(self::KEY_SITE_ID, $siteId);

        if ($scriptUrl !== null && $scriptUrl !== '') {
            $this->setValue(self::KEY_SCRIPT_URL, $scriptUrl);
        }

        if ($accountEmail !== null) {
            $this->setValue(self::KEY_ACCOUNT_EMAIL, $accountEmail);
        }
    }

    /**
     * Reset all settings.
     */
    public function resetSettings(): void
    {
        $this->deleteByKey(self::KEY_SITE_ID);
        $this->deleteByKey(self::KEY_SCRIPT_URL);
        $this->deleteByKey(self::KEY_ACCOUNT_EMAIL);
    }

    /**
     * Get a value from config.
     */
    protected function getValue(string $key): ?string
    {
        // Check if table exists (in case migrations haven't run yet)
        if (!Schema::hasTable('asyntai_config')) {
            return null;
        }

        return Config::getValue($key);
    }

    /**
     * Set a value in config.
     */
    protected function setValue(string $key, ?string $value): void
    {
        Config::setValue($key, $value);
    }

    /**
     * Delete a config by key.
     */
    protected function deleteByKey(string $key): void
    {
        Config::deleteByKey($key);
    }
}
