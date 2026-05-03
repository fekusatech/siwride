<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function salary(Request $request)
    {
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);
        $period = $request->query('period', 1);

        if ($period == 1) {
            $startDate = "{$year}-{$month}-01";
            $endDate = "{$year}-{$month}-15";
            $payoutDate = "{$year}-{$month}-25";
        } else {
            $startDate = "{$year}-{$month}-16";
            $endDate = now()->parse("{$year}-{$month}-01")->setYear($year)->setMonth($month)->endOfMonth()->toDateString();
            $payoutDate = now()->parse("{$year}-{$month}-01")->setYear($year)->setMonth($month)->addMonth()->day(10)->toDateString();
        }

        $recap = User::where('role', 'driver')
            ->withCount(['ordersAsDriver as total_jobs_completed' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate])
                    ->where('status', 'selesai')
                    ->where('is_cancelled', false)
                    ->where('is_cash', false);
            }])
            ->withSum(['ordersAsDriver as total_salary' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate])
                    ->where('status', 'selesai')
                    ->where('is_cancelled', false)
                    ->where('is_cash', false);
            }], 'price')
            ->get()
            ->filter(function ($driver) {
                return $driver->total_jobs_completed > 0;
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'report_period' => "{$startDate} to {$endDate}",
                'payout_date' => $payoutDate,
                'drivers_recap' => $recap->map(function ($driver) {
                    return [
                        'driver_id' => $driver->id,
                        'driver_name' => "{$driver->firstname} {$driver->lastname}",
                        'total_jobs_completed' => $driver->total_jobs_completed,
                        'total_salary' => (float) ($driver->total_salary ?? 0),
                    ];
                }),
            ],
        ]);
    }
}
