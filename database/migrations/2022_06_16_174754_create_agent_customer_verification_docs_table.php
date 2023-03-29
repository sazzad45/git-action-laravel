<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentCustomerVerificationDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_customer_verification_docs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submitted_by_id')->constrained('users');
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('kyc_id')->constrained('user_verification_docs');
            $table->foreignId('doc_type_id')->constrained('user_verification_doc_types');
            $table->string('doc_number');
            $table->foreignId('country_id')->constrained('countries');
            $table->foreignId('state_id')->constrained('states');
            $table->foreignId('city_id')->constrained('cities');
            $table->string('full_name', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->tinyInteger('gender')->default(0)->comment('0 = not known, 1 = male, 2 = female, 9 = other');
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('monthly_income', 16, 2, true);
            $table->tinyInteger('status')->default(0)->comment('0 = pending, 1 = verified, 9 = declined');
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
        Schema::dropIfExists('agent_customer_verification_docs');
    }
}
