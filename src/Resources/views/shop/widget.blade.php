{{--
    Asyntai - AI Chatbot for Bagisto

    Shop widget template (injected into storefront footer)

    @category  Asyntai
    @package   Asyntai\Chatbot
    @author    Asyntai <hello@asyntai.com>
    @copyright Copyright (c) Asyntai
    @license   MIT License
--}}

@if(isset($asyntaiChatbot) && $asyntaiChatbot['isConnected'] && $asyntaiChatbot['siteId'])
<script src="{{ $asyntaiChatbot['scriptUrl'] }}" async defer data-asyntai-id="{{ $asyntaiChatbot['siteId'] }}"></script>
@endif
