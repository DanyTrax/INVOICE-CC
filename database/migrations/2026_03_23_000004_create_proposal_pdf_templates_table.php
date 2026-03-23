<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_pdf_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128);
            $table->string('logo_path', 255)->nullable();
            $table->string('header_company_name', 255)->nullable();
            $table->string('header_nit', 64)->nullable();
            $table->string('header_subtitle', 500)->nullable();
            $table->text('body_html')->nullable();
            $table->string('footer_text', 500)->nullable();
            $table->string('signature_name', 128)->nullable();
            $table->string('signature_position', 128)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_pdf_templates');
    }
};
