<?php

namespace App\Console\Commands;

use App\Models\Driver;
use App\Models\Payroll;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ImportDriverData extends Command
{
    protected $signature = 'siwride:import-drivers';

    protected $description = 'Import driver and payroll data from spreadsheet snippet';

    public function handle()
    {
        $data = '1	siw01	Nanankjhe	19	1.745.000	3	290.000	100.000
2	siw02	Rahman	16	2.040.000	0	0	100.000
3	siw03	Eko Satrio	0	0	0	0	0
4	siw04	Wawan	6	890.000	0	0	100.000
5	siw05	Arya	6	510.000	1	60.000	100.000
6	siw06	Angga	11	1.410.000	17	1.600.000	100.000
7	siw07	Joe / Cash 	0	0	3	390.000	0
8	siw08	Teddy	3	420.000	8	710.000	100.000
9	siw09	Dimas	2	170.000	0	0	100.000
10	siw10	Ardian	0	0	0	0	0
11	siw11	Zaky	0	0	0	0	0
12	siw12	Wildan	0	0	0	0	0
13	siw13	Nyoman Edy	0	0	0	0	0
14	siw14	Bayu	0	0	0	0	0
15	siw15	Yan Tande	0	0	0	0	0
16	siw16	Pak Urip	0	0	0	0	0
17	siw17	Yayan	2	340.000	4	310.000	0
18	siw18	Toni	0	0	0	0	0
19	siw19	Basri	0	0	0	0	0
20	siw20	Ivan	0	0	0	0	0
21	siw21	Anom	0	0	0	0	0
22	siw22	Lutfi	0	0	0	0	0
23	siw23	Angga / cash	0	0	1	250.000	0
24	siw24		0	0	0	0	0
25	siw25	Sby	2	300.000	0	0	0';

        $lines = explode("\n", $data);
        $count = 0;

        DB::transaction(function () use ($lines, &$count) {
            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue;
                }
                $cols = explode("\t", $line);
                if (count($cols) < 3) {
                    continue;
                }

                $nid = trim($cols[1]);
                $name = trim($cols[2]) ?: "Driver $nid";
                $job1 = (int) ($cols[3] ?? 0);
                $salary1 = (int) str_replace('.', '', $cols[4] ?? '0');
                $job2 = (int) ($cols[5] ?? 0);
                $salary2 = (int) str_replace('.', '', $cols[6] ?? '0');
                $admin = (int) str_replace('.', '', $cols[7] ?? '0');

                // Create or Update Driver
                $driver = Driver::updateOrCreate(
                    ['nid' => $nid],
                    [
                        'name' => $name,
                        'email' => strtolower($nid).'@siwride.com',
                        'phone' => '08'.mt_rand(10000000, 99999999), // Placeholder
                        'password' => Hash::make('password'),
                        'status' => 'active',
                    ]
                );

                // Create Payroll for period 1
                if ($job1 > 0 || $salary1 > 0) {
                    Payroll::create([
                        'driver_id' => $driver->id,
                        'period_label' => '2026-03-A (1-15)',
                        'total_jobs' => $job1,
                        'amount' => $salary1,
                        'admin_fee' => 0, // Admin fee logic could be complex, assuming it's for the whole month
                        'net_amount' => $salary1,
                        'status' => 'draft',
                    ]);
                }

                // Create Payroll for period 2
                if ($job2 > 0 || $salary2 > 0) {
                    // We apply the admin fee to the second period or split it?
                    // Usually admin fees are deducted at the end. I'll put it in period B.
                    Payroll::create([
                        'driver_id' => $driver->id,
                        'period_label' => '2026-03-B (16-31)',
                        'total_jobs' => $job2,
                        'amount' => $salary2,
                        'admin_fee' => $admin,
                        'net_amount' => $salary2 - $admin,
                        'status' => 'draft',
                    ]);
                }

                $count++;
            }
        });

        $this->info("Imported $count drivers successfully.");
    }
}
