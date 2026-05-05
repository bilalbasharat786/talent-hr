<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('role')->constrained('companies')->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
        });

        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }

            if (! Schema::hasColumn('companies', 'logo')) {
                $table->string('logo')->nullable();
            }

            if (! Schema::hasColumn('companies', 'cover_image')) {
                $table->string('cover_image')->nullable();
            }

            if (! Schema::hasColumn('companies', 'about')) {
                $table->longText('about')->nullable();
            }

            if (! Schema::hasColumn('companies', 'industry')) {
                $table->string('industry')->nullable();
            }

            if (! Schema::hasColumn('companies', 'company_size')) {
                $table->string('company_size')->nullable();
            }

            if (! Schema::hasColumn('companies', 'website')) {
                $table->string('website')->nullable();
            }

            if (! Schema::hasColumn('companies', 'office_locations')) {
                $table->json('office_locations')->nullable();
            }

            if (! Schema::hasColumn('companies', 'working_hours')) {
                $table->json('working_hours')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }

            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'logo',
                'cover_image',
                'about',
                'industry',
                'company_size',
                'website',
                'office_locations',
                'working_hours',
            ]);
        });
    }
};

