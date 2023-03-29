<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentCustomerVerificationDocImages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_customer_verification_doc_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acvd_id')->constrained('agent_customer_verification_docs');
            $table->foreignId('doc_type_id')->constrained('user_verification_doc_types');
            $table->string('image_path');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agent_customer_verification_doc_images');
    }
}
