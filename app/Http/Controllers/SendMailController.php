<?php

namespace App\Http\Controllers;

use App\Mail\AparRefillNotification;
use App\Mail\AparRusakNotification;
use App\Mail\AparUsedNotification;
use App\Models\AparInspection;
use App\Models\MasterApar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMailController extends Controller
{
    public function aparRefill(){
        try {
            $aparPerluRefill = MasterApar::with('gedung')->whereDate('tgl_refill', '<=', Carbon::now()->subYears(2))->get();

            if(!empty($aparPerluRefill)){
                $adminUsers = User::role('admin')->get();

                foreach ($adminUsers as $user) {
                    Mail::to($user->email)->send(new AparRefillNotification($aparPerluRefill));
                }

                Log::info('Email laporan APAR refill dikirim ke semua admin');
                
                return response('Email laporan APAR refill dikirim ke semua admin', 200)
                ->header('Content-Type', 'text/plain');
            }
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return response('Email laporan APAR refill gagal dikirim', 500)
            ->header('Content-Type', 'text/plain');
        }
    }

    public function aparRusak(){
        try {
            $start_date = Carbon::now()->startOfMonth()->toDateString();
            $end_date = Carbon::now()->endOfMonth()->toDateString();

            $aparRusak = AparInspection::
                with(['masterApar.gedung', 'user', 'details'])
                ->whereBetween('date', [$start_date, $end_date])
                ->whereHas('details', function ($q) {
                    $q->where('value', '!=', 'B');
                })
                ->get();

            if($aparRusak->isNotEmpty()){
                $adminUsers = User::role('admin')->get();
                foreach ($adminUsers as $user) {
                    Mail::to($user->email)->send(new AparRusakNotification($aparRusak));
                }
                
                Log::info('Email laporan APAR rusak dikirim ke semua admin');
                    
                return response('Email laporan APAR rusak dikirim ke semua admin', 200)
                    ->header('Content-Type', 'text/plain');
            }
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return response('Email laporan APAR rusak gagal dikirim', 500)
            ->header('Content-Type', 'text/plain');
        }
    }

    public function aparUsed(){
        try {
            $penggunaanApar = PenggunaanApar::with(['masterApar.gedung', 'user'])->latest()->first();

            if ($penggunaanApar) {
                $adminUsers = User::role('admin')->get();
                foreach ($adminUsers as $user) {
                    Mail::to($user->email)->send(new AparUsedNotification($penggunaanApar));
                }

                Log::info('Email notifikasi APAR digunakan dikirim ke semua admin');
                
                return response('Email notifikasi APAR digunakan berhasil dikirim', 200)
                    ->header('Content-Type', 'text/plain');
            }
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return response('Email notifikasi APAR digunakan gagal dikirim', 500)
                ->header('Content-Type', 'text/plain');
        }
    }
}
