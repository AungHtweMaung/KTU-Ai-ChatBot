<?php

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
        Schema::table('faqs', function (Blueprint $table) {
            // Comma-separated alternative terms / synonyms (any language) so
            // the chatbot matches an FAQ however the user phrases the question
            // — e.g. "hostel, dormitory, အဆောင်, ကျောင်းဆောင်, အိပ်ဆောင်".
            $table->text('keywords')->nullable()->after('answer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropColumn('keywords');
        });
    }
};
