<?php

namespace SaadAhsan\FormBuilder\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateSubmissionsTable Migration
 */
class CreateSubmissionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('saadahsan_callbackform_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('preferred_time')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('new'); // new, contacted, completed, cancelled
            $table->text('admin_notes')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saadahsan_callbackform_submissions');
    }
}
