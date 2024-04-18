<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwillDataImporterTables extends Migration
{
    public function up(): void
    {
        Schema::create('twill_data_importer', function (Blueprint $table) {
            createDefaultTableFields($table);

            $table->string('title')->nullable();

            $table->string('base_name')->nullable();

            $table->string('status')->default('missing-files');

            $table->string('mime_type')->nullable();

            $table->boolean('success')->nullable();

            $table->text('error_message')->nullable();

            $table->integer('imported_records')->nullable();

            $table->timestamp('imported_at')->nullable();
        });

        Schema::create('twill_data_importer_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'twill_data_importer', 'twill_data_importer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('twill_data_importer_revisions');
        Schema::dropIfExists('twill_data_importer');
    }
}
