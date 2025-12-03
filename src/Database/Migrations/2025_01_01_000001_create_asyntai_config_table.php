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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asyntai_config', function (Blueprint $table) {
            $table->id();
            $table->string('config_key', 255)->unique();
            $table->text('config_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asyntai_config');
    }
};
