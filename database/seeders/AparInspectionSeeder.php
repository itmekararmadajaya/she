<?php

namespace Database\Seeders;

use App\Models\AparInspection;
use App\Models\AparInspectionDetail;
use App\Models\ItemCheck;
use App\Models\MasterApar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class AparInspectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allApar = MasterApar::all();

        // Skip 3 APAR terakhir
        $inspectedApars = $allApar->sortBy('id')->slice(0, max(0, $allApar->count() - 3));
        $itemChecks = ItemCheck::all();
        $user = User::first();

        if ($inspectedApars->isEmpty() || $itemChecks->isEmpty() || !$user) return;

        foreach ($inspectedApars as $apid => $apar) {
            $inspection = AparInspection::create([
                'master_apar_id' => $apar->id,
                'user_id' => $user->id,
                'date' => Carbon::now()->subDays(rand(0, Carbon::now()->day - 1)),
            ]);

            foreach ($itemChecks as $key => $item) {
                AparInspectionDetail::create([
                    'apar_inspection_id' => $inspection->id,
                    'item_check_id' => $item->id,
                    'value' => $apid % 2 == 0 ? 'B' : Arr::random(['B', 'R', 'T/A']),
                    'remark' => $key % 2 == 0 ? 'this is remark' : null,
                ]);
            }
        }
    }
}
