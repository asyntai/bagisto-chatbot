{{--
    Asyntai - AI Chatbot for Bagisto

    @category  Asyntai
    @package   Asyntai\Chatbot
    @author    Asyntai <hello@asyntai.com>
    @copyright Copyright (c) Asyntai
    @license   MIT License
--}}

<x-admin::layouts>
    <x-slot:title>
        @lang('asyntai-chatbot::app.admin.settings.title')
    </x-slot:title>

    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
        <p class="text-xl text-gray-800 dark:text-white font-bold">
            @lang('asyntai-chatbot::app.admin.settings.title')
        </p>
    </div>

    <div class="flex gap-2.5 mt-3.5 max-xl:flex-wrap">
        <div class="flex flex-col gap-2 flex-1 max-xl:flex-auto">
            <div class="p-4 bg-white dark:bg-gray-900 rounded box-shadow" id="asyntai-settings-wrap">
                <p id="asyntai-status" class="mb-4 text-gray-600 dark:text-gray-300">
                    @lang('asyntai-chatbot::app.admin.settings.status'):
                    <span style="color:{{ $isConnected ? '#28a745' : '#dc3545' }};font-weight:600;">
                        {{ $isConnected ? __('asyntai-chatbot::app.admin.settings.connected') : __('asyntai-chatbot::app.admin.settings.not_connected') }}
                    </span>
                    @if ($isConnected && $accountEmail)
                        @lang('asyntai-chatbot::app.admin.settings.as') {{ $accountEmail }}
                    @endif
                    @if ($isConnected)
                        <button type="button" onclick="window.asyntaiReset()" class="ml-4 px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600 text-sm">
                            @lang('asyntai-chatbot::app.admin.settings.reset')
                        </button>
                    @endif
                </p>

                <div id="asyntai-alert" class="p-4 rounded mb-4" style="display:none;"></div>

                <div id="asyntai-connected-box" style="display:{{ $isConnected ? 'block' : 'none' }};">
                    <div class="p-8 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                        <h4 class="text-lg font-semibold mb-3 text-gray-800 dark:text-white">
                            @lang('asyntai-chatbot::app.admin.settings.enabled_title')
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">
                            @lang('asyntai-chatbot::app.admin.settings.enabled_description')
                        </p>
                        <a href="https://asyntai.com/dashboard" target="_blank" rel="noopener" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                            @lang('asyntai-chatbot::app.admin.settings.open_panel')
                        </a>
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                            <strong>@lang('asyntai-chatbot::app.admin.settings.tip'):</strong>
                            @lang('asyntai-chatbot::app.admin.settings.tip_text')
                            <a href="https://asyntai.com/dashboard#setup" target="_blank" rel="noopener" class="text-blue-600 hover:underline">
                                @lang('asyntai-chatbot::app.admin.settings.go_here')
                            </a>.
                        </p>
                    </div>
                </div>

                <div id="asyntai-popup-wrap" style="display:{{ $isConnected ? 'none' : 'block' }};">
                    <div class="p-8 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                        <p class="text-gray-600 dark:text-gray-300 mb-4">
                            @lang('asyntai-chatbot::app.admin.settings.get_started_text')
                        </p>
                        <button type="button" id="asyntai-connect-btn" onclick="window.asyntaiConnect()" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium cursor-pointer">
                            @lang('asyntai-chatbot::app.admin.settings.get_started')
                        </button>
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                            @lang('asyntai-chatbot::app.admin.settings.fallback_text')
                            <a href="https://asyntai.com/wp-auth?platform=bagisto" id="asyntai-fallback-link" target="_blank" rel="noopener" class="text-blue-600 hover:underline">
                                @lang('asyntai-chatbot::app.admin.settings.open_connect_window')
                            </a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin::layouts>

<script>
(function() {
    'use strict';

    var currentState = null;
    var saveUrl = '{{ route("admin.asyntai.chatbot.api.save") }}';
    var resetUrl = '{{ route("admin.asyntai.chatbot.api.reset") }}';
    var csrfToken = '{{ csrf_token() }}';

    function showAlert(msg, ok) {
        var el = document.getElementById('asyntai-alert');
        if (!el) return;
        el.style.display = 'block';
        el.className = 'p-4 rounded mb-4 ' + (ok ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200');
        el.textContent = msg;
    }

    function generateState() {
        return 'bagisto_' + Math.random().toString(36).substr(2, 9);
    }

    function updateFallbackLink() {
        var fallbackLink = document.getElementById('asyntai-fallback-link');
        if (fallbackLink && currentState) {
            fallbackLink.href = 'https://asyntai.com/wp-auth?platform=bagisto&state=' + encodeURIComponent(currentState);
        }
    }

    function showLoading(show) {
        var btn = document.getElementById('asyntai-connect-btn');
        if (btn) {
            if (show) {
                btn.disabled = true;
                btn.setAttribute('data-original-text', btn.textContent);
                btn.textContent = '{{ __("asyntai-chatbot::app.admin.settings.connecting") }}';
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                btn.disabled = false;
                var originalText = btn.getAttribute('data-original-text');
                if (originalText) btn.textContent = originalText;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
    }

    window.asyntaiConnect = function() {
        currentState = generateState();
        updateFallbackLink();
        showLoading(true);
        showAlert('{{ __("asyntai-chatbot::app.admin.settings.waiting_auth") }}', true);

        var url = 'https://asyntai.com/wp-auth?platform=bagisto&state=' + encodeURIComponent(currentState);
        var w = 800, h = 720;
        var y = window.top.outerHeight / 2 + window.top.screenY - (h / 2);
        var x = window.top.outerWidth / 2 + window.top.screenX - (w / 2);
        var pop = window.open(url, 'asyntai_connect', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=' + w + ',height=' + h + ',top=' + y + ',left=' + x);

        setTimeout(function() {
            if (!pop || pop.closed || typeof pop.closed === 'undefined') {
                showAlert('{{ __("asyntai-chatbot::app.admin.settings.popup_blocked") }}', false);
                showLoading(false);
                return;
            }
            pollForConnection(currentState);
        }, 100);
    };

    function pollForConnection(state) {
        var attempts = 0;
        var maxAttempts = 60;

        function check() {
            if (attempts++ > maxAttempts) {
                showAlert('{{ __("asyntai-chatbot::app.admin.settings.timeout") }}', false);
                showLoading(false);
                return;
            }

            var script = document.createElement('script');
            var cb = 'asyntai_cb_' + Date.now();

            window[cb] = function(data) {
                try { delete window[cb]; } catch (e) { window[cb] = undefined; }
                if (script.parentNode) script.parentNode.removeChild(script);

                if (data && data.site_id) {
                    saveConnection(data);
                    return;
                }
                setTimeout(check, 500);
            };

            script.src = 'https://asyntai.com/connect-status.js?state=' + encodeURIComponent(state) + '&cb=' + cb;
            script.onerror = function() {
                try { delete window[cb]; } catch (e) { window[cb] = undefined; }
                if (script.parentNode) script.parentNode.removeChild(script);
                setTimeout(check, 1000);
            };

            document.head.appendChild(script);
        }

        setTimeout(check, 800);
    }

    function saveConnection(data) {
        showLoading(false);
        showAlert('{{ __("asyntai-chatbot::app.admin.settings.connected_saving") }}', true);

        // Use FormData to send as form-urlencoded (better CSRF compatibility)
        var formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('site_id', data.site_id || '');
        if (data.script_url) formData.append('script_url', data.script_url);
        if (data.account_email) formData.append('account_email', data.account_email);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', saveUrl, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var json = JSON.parse(xhr.responseText);
                    if (json && json.success) {
                        showAlert('{{ __("asyntai-chatbot::app.admin.settings.connected_success") }}', true);
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showAlert('{{ __("asyntai-chatbot::app.admin.settings.save_error") }}: ' + (json.error || 'Unknown error'), false);
                    }
                } catch (e) {
                    showAlert('{{ __("asyntai-chatbot::app.admin.settings.parse_error") }}', false);
                }
            } else {
                showAlert('{{ __("asyntai-chatbot::app.admin.settings.save_error") }}: HTTP ' + xhr.status, false);
            }
        };

        xhr.onerror = function() {
            showAlert('{{ __("asyntai-chatbot::app.admin.settings.save_error") }}: Network error', false);
        };

        xhr.send(formData);
    }

    window.asyntaiReset = function() {
        if (!confirm('{{ __("asyntai-chatbot::app.admin.settings.reset_confirm") }}')) {
            return;
        }

        var formData = new FormData();
        formData.append('_token', csrfToken);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', resetUrl, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var json = JSON.parse(xhr.responseText);
                    if (json && json.success) {
                        window.location.reload();
                    } else {
                        showAlert('{{ __("asyntai-chatbot::app.admin.settings.reset_failed") }}: ' + (json.error || 'Unknown error'), false);
                    }
                } catch (e) {
                    showAlert('{{ __("asyntai-chatbot::app.admin.settings.parse_error") }}', false);
                }
            } else {
                showAlert('{{ __("asyntai-chatbot::app.admin.settings.reset_failed") }}: HTTP ' + xhr.status, false);
            }
        };

        xhr.onerror = function() {
            showAlert('{{ __("asyntai-chatbot::app.admin.settings.reset_failed") }}: Network error', false);
        };

        xhr.send(formData);
    };

    // Initialize fallback link
    currentState = generateState();
    updateFallbackLink();
})();
</script>
