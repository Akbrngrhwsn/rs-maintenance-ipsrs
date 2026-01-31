<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('meetings', function (Blueprint $table) {
            if (!Schema::hasColumn('meetings', 'meeting_date')) {
                $table->date('meeting_date')->nullable()->after('title');
            }
            if (!Schema::hasColumn('meetings', 'minutes')) {
                $table->text('minutes')->nullable()->after('meeting_date');
            }
            if (!Schema::hasColumn('meetings', 'division_role')) {
                $table->string('division_role')->nullable()->after('minutes');
            }
            if (!Schema::hasColumn('meetings', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('division_role');
            }
            if (!Schema::hasColumn('meetings', 'edited_by')) {
                $table->unsignedBigInteger('edited_by')->nullable()->after('created_by');
            }
        });
    }

    public function down()
    {
        Schema::table('meetings', function (Blueprint $table) {
            if (Schema::hasColumn('meetings', 'edited_by')) $table->dropColumn('edited_by');
            if (Schema::hasColumn('meetings', 'created_by')) $table->dropColumn('created_by');
            if (Schema::hasColumn('meetings', 'division_role')) $table->dropColumn('division_role');
            if (Schema::hasColumn('meetings', 'minutes')) $table->dropColumn('minutes');
            if (Schema::hasColumn('meetings', 'meeting_date')) $table->dropColumn('meeting_date');
        });
    }
};
