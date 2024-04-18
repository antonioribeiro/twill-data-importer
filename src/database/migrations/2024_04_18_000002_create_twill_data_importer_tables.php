<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwillDataImporterTables extends Migration
{
    public function up(): void
    {
        Schema::create('twill_sec_head', function (Blueprint $table) {
            createDefaultTableFields($table);

            $table->boolean('hsts_enabled')->default(true);
            $table->text('hsts')->nullable();

        });

        Schema::create('twill_sec_head_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'twill_sec_head', 'twill_sec_head');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('twill_sec_head_revisions');
        Schema::dropIfExists('twill_sec_head');
    }
}











