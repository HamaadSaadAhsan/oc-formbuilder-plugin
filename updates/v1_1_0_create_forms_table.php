<?php

namespace SaadAhsan\FormBuilder\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateFormsTable Migration
 */
class CreateFormsTable extends Migration
{
    public function up(): void
    {
        Schema::create('saadahsan_callbackform_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('success_message')->default('Thank you! Your form has been submitted.');
            $table->string('error_message')->default('There was an error. Please try again.');
            $table->string('submit_button_text')->default('Submit');
            $table->string('notify_email')->nullable();
            $table->text('custom_css')->nullable();
            $table->text('custom_js')->nullable();
            $table->text('wrapper_class')->nullable();
            $table->text('form_class')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('saadahsan_callbackform_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('saadahsan_callbackform_forms')->onDelete('cascade');
            $table->string('field_type'); // text, email, phone, textarea, select, checkbox, radio, date, file, hidden, number, url, html
            $table->string('name'); // field name attribute
            $table->string('label')->nullable();
            $table->string('placeholder')->nullable();
            $table->text('default_value')->nullable();
            $table->text('options')->nullable(); // JSON for select/radio/checkbox options
            $table->text('validation_rules')->nullable(); // e.g., required|email|max:255
            $table->string('error_message')->nullable(); // Custom validation error message
            $table->string('field_class')->nullable(); // CSS classes for the input
            $table->string('field_style')->nullable(); // Inline styles for the input
            $table->string('wrapper_class')->nullable(); // CSS classes for wrapper div
            $table->string('wrapper_style')->nullable(); // Inline styles for wrapper
            $table->text('custom_attributes')->nullable(); // JSON for data-*, aria-*, etc.
            $table->text('html_content')->nullable(); // For custom HTML field type
            $table->integer('sort_order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add form_id to submissions table
        Schema::table('saadahsan_callbackform_submissions', function (Blueprint $table) {
            $table->foreignId('form_id')->nullable()->after('id')->constrained('saadahsan_callbackform_forms')->onDelete('cascade');
            $table->text('form_data')->nullable()->after('message'); // JSON storage for all dynamic fields
        });
    }

    public function down(): void
    {
        Schema::table('saadahsan_callbackform_submissions', function (Blueprint $table) {
            $table->dropForeign(['form_id']);
            $table->dropColumn(['form_id', 'form_data']);
        });

        Schema::dropIfExists('saadahsan_callbackform_form_fields');
        Schema::dropIfExists('saadahsan_callbackform_forms');
    }
}
