<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
  public function up(): void {
    Schema::create('skills', function (Blueprint $t) {
      $t->id();
      $t->string('name', 120);
      $t->unsignedTinyInteger('level');
      $t->unsignedSmallInteger('years_experience');
      $t->json('tags')->nullable();
      $t->unsignedTinyInteger('status_id')->default(1);
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('skills'); }
};
