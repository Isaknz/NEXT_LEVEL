<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','gerente','secretaria','cajero') DEFAULT 'secretaria' NOT NULL");
        } elseif ($driver === 'sqlite') {
            Schema::dropIfExists('sessions');
            Schema::dropIfExists('password_reset_tokens');
            Schema::dropIfExists('users');
            Schema::create('users', function ($table) {
                $table->id();
                $table->string('nombre');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('role')->default('secretaria');
                $table->enum('estado', ['activo', 'inactivo'])->default('activo');
                $table->timestamp('last_login')->nullable()->after('estado');
                $table->timestamp('password_changed_at')->nullable()->after('last_login');
                $table->rememberToken();
                $table->timestamps();
                $table->index(['role', 'estado'], 'idx_users_role_estado');
            });
            Schema::create('password_reset_tokens', function ($table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
            Schema::create('sessions', function ($table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','gerente','secretaria') DEFAULT 'secretaria' NOT NULL");
        } elseif ($driver === 'sqlite') {
            Schema::dropIfExists('sessions');
            Schema::dropIfExists('password_reset_tokens');
            Schema::dropIfExists('users');
            Schema::create('users', function ($table) {
                $table->id();
                $table->string('nombre');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('role')->default('secretaria');
                $table->enum('estado', ['activo', 'inactivo'])->default('activo');
                $table->timestamp('last_login')->nullable()->after('estado');
                $table->timestamp('password_changed_at')->nullable()->after('last_login');
                $table->rememberToken();
                $table->timestamps();
                $table->index(['role', 'estado'], 'idx_users_role_estado');
            });
            Schema::create('password_reset_tokens', function ($table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
            Schema::create('sessions', function ($table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }
};
