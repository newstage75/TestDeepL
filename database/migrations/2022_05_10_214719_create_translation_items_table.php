<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('translation_items', function (Blueprint $table) {
              $table->id();
              $table->unsignedBigInteger('translation_id')->comment('翻訳ID');
              $table->string('locale')->comment('言語'); // 2文字のコード
              $table->text('text')->nullable()->comment('テキスト');
              $table->timestamps();

              $table->unique(['translation_id', 'locale']);
              $table->foreign('translation_id')->references('id')->on('translations')->onDelete('cascade');
          });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('translation_items');
    }
};
