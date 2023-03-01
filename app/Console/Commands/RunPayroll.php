<?php

namespace App\Console\Commands;

use App\Jobs\RunPayrollJob;
use Illuminate\Console\Command;

class RunPayroll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:payroll {--company=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Payroll';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Run Payroll");
        $month = date('m-Y');
        $company = $this->option('company') ?? 'all';
        dispatch(new RunPayrollJob($month, $company))->onQueue('run_payroll');
    }
}
