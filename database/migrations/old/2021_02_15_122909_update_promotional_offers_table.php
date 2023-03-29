<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePromotionalOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('promotional_offers', function (Blueprint $table) {
            $table->boolean('is_bundle_offer')->default(0);
            $table->string('link')->nullable();
            $table->string('btn_text')->nullable();
            $table->string('btn_color')->nullable();
            $table->integer('operator_id')->nullable();
            $table->integer('bundle_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
//alter table `promotional_offers`
// add `is_bundle_offer` tinyint(1) not null default '0',
// add `link` varchar(255) null, add `btn_text` varchar(255) null,
// add `btn_color` varchar(255) null,
// add `operator_id` int null,
// add `bundle_id` int null
