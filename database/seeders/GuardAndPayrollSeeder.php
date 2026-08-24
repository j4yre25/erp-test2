<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Deployment;
use App\Models\PayrollPeriod;
use App\Models\DailyTimeRecord;
use App\Models\DailyTimeRecordStatus;
use App\Models\PayrollPeriodStatus;
use Illuminate\Support\Carbon;

class GuardAndPayrollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dtrPresentStatus = DailyTimeRecordStatus::firstOrCreate(['name' => 'present', 'label' => 'Present', 'color' => 'green']);
        $dtrAbsentStatus = DailyTimeRecordStatus::firstOrCreate(['name' => 'absent', 'label' => 'Absent', 'color' => 'red']);
        
        $payrollOpenStatus = PayrollPeriodStatus::firstOrCreate(['name' => 'Open', 'code' => 'open']);
        $payrollClosedStatus = PayrollPeriodStatus::firstOrCreate(['name' => 'Closed', 'code' => 'closed']);

        $client = Client::firstOrCreate(
            ['name' => 'Acme Corp'],
            [
                'billing_address' => '123 Acme St',
                'payroll_period' => 'semi-monthly',
                'cutoff_type' => 'standard',
                'first_cutoff_day' => 15,
                'second_cutoff_day' => 30,
                'payroll_frequency' => 'bi-weekly',
                'company_address' => '123 Acme St',
                'contact_person' => 'John Doe'
            ]
        );

        $guards = Employee::factory()->count(5)->create([
            'employee_type' => 'guard'
        ]);

        foreach ($guards as $guard) {
            Deployment::firstOrCreate(
                ['client_id' => $client->id, 'employee_id' => $guard->id],
                [
                    'post_name' => 'Main Gate',
                    'start_date' => Carbon::now()->subMonths(6),
                    'status' => 'active',
                    'billing_rate' => 1000.00
                ]
            );
        }
        
        $deployments = Deployment::where('client_id', $client->id)->get();
        
        $payrollPeriod = PayrollPeriod::firstOrCreate(
            ['client_id' => $client->id, 'start_date' => Carbon::now()->startOfMonth()],
            [
                'payroll_period_status_id' => $payrollOpenStatus->id,
                'end_date' => Carbon::now()->startOfMonth()->addDays(14)
            ]
        );

        foreach ($deployments as $deployment) {
            for ($i = 0; $i < 15; $i++) {
                $workDate = Carbon::now()->startOfMonth()->addDays($i);
                
                if ($workDate->isWeekday()) {
                    DailyTimeRecord::firstOrCreate(
                        [
                            'payroll_period_id' => $payrollPeriod->id,
                            'deployment_id' => $deployment->id,
                            'work_date' => $workDate->format('Y-m-d')
                        ],
                        [
                            'time_in' => $workDate->copy()->setHour(8),
                            'time_out' => $workDate->copy()->setHour(17),
                            'regular_hours' => 8,
                            'overtime_hours' => 0,
                            'night_diff_hours' => 0,
                            'daily_time_record_status_id' => $dtrPresentStatus->id,
                        ]
                    );
                }
            }
        }
    }
}
