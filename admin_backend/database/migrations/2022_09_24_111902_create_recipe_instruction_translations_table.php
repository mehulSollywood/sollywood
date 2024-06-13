<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecipeInstructionTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipe_instruction_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instruction_id')->constrained('recipe_instructions')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('title');
            $table->string('locale')->index();
            $table->unique(['instruction_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recipe_instruction_translations');
    }
}
