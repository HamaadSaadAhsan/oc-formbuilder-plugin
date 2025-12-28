<?php

namespace SaadAhsan\CallbackForm\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Add fields_config column for jsonable repeater
 */
class FixFormFieldsNullable extends Migration
{
    public function up(): void
    {
        // Add fields_config column for jsonable storage
        Schema::table('saadahsan_callbackform_forms', function (Blueprint $table) {
            $table->text('fields_config')->nullable()->after('form_class');
        });

        // Drop the foreign key constraint on form_fields
        Schema::table('saadahsan_callbackform_form_fields', function (Blueprint $table) {
            $table->dropForeign(['form_id']);
        });

        // Make form_id nullable
        Schema::table('saadahsan_callbackform_form_fields', function (Blueprint $table) {
            $table->bigInteger('form_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('saadahsan_callbackform_forms', function (Blueprint $table) {
            $table->dropColumn('fields_config');
        });

        Schema::table('saadahsan_callbackform_form_fields', function (Blueprint $table) {
            $table->bigInteger('form_id')->nullable(false)->change();
            $table->foreign('form_id')->references('id')->on('saadahsan_callbackform_forms')->onDelete('cascade');
        });
    }
}
