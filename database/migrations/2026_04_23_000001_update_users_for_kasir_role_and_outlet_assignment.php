<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->rebuildSqliteUsersTable(includeOutlet: true, strictLegacyRoles: false);

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'outlet_id')) {
                $table->foreignId('outlet_id')
                    ->nullable()
                    ->after('role')
                    ->constrained('outlets')
                    ->nullOnDelete();
            }
        });

        $this->convertRoleColumnToString($driver);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('role', 'kasir')
            ->update(['role' => 'pelanggan']);

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->rebuildSqliteUsersTable(includeOutlet: false, strictLegacyRoles: true);

            return;
        }

        if (Schema::hasColumn('users', 'outlet_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('outlet_id');
            });
        }

        $this->restoreLegacyRoleColumn($driver);
    }

    private function convertRoleColumnToString(string $driver): void
    {
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY role VARCHAR(50) NOT NULL DEFAULT 'pelanggan'");

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(50)");
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'pelanggan'");
        }
    }

    private function restoreLegacyRoleColumn(string $driver): void
    {
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'pelanggan') NOT NULL DEFAULT 'pelanggan'");

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'pelanggan'))");
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'pelanggan'");
        }
    }

    private function rebuildSqliteUsersTable(bool $includeOutlet, bool $strictLegacyRoles): void
    {
        $tempTable = 'users_backup_for_kasir_role';

        DB::statement('PRAGMA foreign_keys = OFF');

        try {
            Schema::dropIfExists($tempTable);
            DB::statement("CREATE TABLE {$tempTable} AS SELECT * FROM users");
            Schema::drop('users');

            Schema::create('users', function (Blueprint $table) use ($includeOutlet, $strictLegacyRoles) {
                $table->id();
                $table->string('name');
                $table->string('username')->unique();
                $table->string('email')->nullable()->unique();
                $table->string('password');

                if ($strictLegacyRoles) {
                    $table->enum('role', ['admin', 'pelanggan'])->default('pelanggan');
                } else {
                    $table->string('role', 50)->default('pelanggan');
                }

                if ($includeOutlet) {
                    $table->foreignId('outlet_id')->nullable()->constrained('outlets')->nullOnDelete();
                }

                $table->text('alamat')->nullable();
                $table->string('no_hp', 20)->nullable();
                $table->rememberToken();
                $table->timestamps();
            });

            $roleExpression = $strictLegacyRoles
                ? "CASE WHEN role = 'admin' THEN 'admin' ELSE 'pelanggan' END"
                : "CASE WHEN role = 'admin' THEN 'admin' WHEN role = 'kasir' THEN 'kasir' ELSE 'pelanggan' END";

            $outletSelect = $includeOutlet && Schema::hasColumn($tempTable, 'outlet_id')
                ? 'outlet_id'
                : 'NULL AS outlet_id';

            $outletColumnList = $includeOutlet
                ? ', outlet_id'
                : '';

            $outletValueList = $includeOutlet
                ? ", {$outletSelect}"
                : '';

            DB::statement("
                INSERT INTO users (
                    id, name, username, email, password, role{$outletColumnList}, alamat, no_hp, remember_token, created_at, updated_at
                )
                SELECT
                    id, name, username, email, password, {$roleExpression}{$outletValueList}, alamat, no_hp, remember_token, created_at, updated_at
                FROM {$tempTable}
            ");

            Schema::drop($tempTable);
        } finally {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }
};
