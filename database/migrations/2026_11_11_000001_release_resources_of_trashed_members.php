<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Members deleted before the cleanup fix still keep their unique nik,
     * their used member number, and their photo hash. That made those values
     * impossible to reuse on a later import.
     *
     * This one-time cleanup frees those resources for every soft-deleted
     * member: the nik becomes a "DELETED-{id}" marker, the member number is
     * returned to the available pool, and photo records/files are removed.
     */
    public function up(): void
    {
        DB::table('members')
            ->whereNotNull('deleted_at')
            ->orderBy('id')
            ->chunkById(100, function ($members) {
                foreach ($members as $member) {
                    DB::table('members')
                        ->where('id', $member->id)
                        ->update(['nik' => 'DELETED-' . $member->id]);

                    DB::table('member_numbers')
                        ->where('member_id', $member->id)
                        ->update([
                            'status' => 'available',
                            'member_id' => null,
                        ]);

                    $photos = DB::table('member_photos')
                        ->where('member_id', $member->id)
                        ->get(['id', 'photo_path']);

                    foreach ($photos as $photo) {
                        if ($photo->photo_path) {
                            Storage::disk('local')->delete($photo->photo_path);
                        }

                        DB::table('member_photos')->where('id', $photo->id)->delete();
                    }
                }
            });
    }

    /**
     * This is a data cleanup; the original values stay recorded in the audit
     * log, so there is nothing to restore automatically.
     */
    public function down(): void
    {
        // Intentionally left empty.
    }
};
