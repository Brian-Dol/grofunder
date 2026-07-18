<?php

namespace App\Console\Commands;

use App\Mail\AgentPerformanceReportMail;
use App\Mail\SystemAnalyticsReportMail;
use App\Mail\InvestorPortfolioReportMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendScheduledReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:send-scheduled {--type=all} {--date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled performance reports to agents, admins, and investors';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : now();

        // Calculate date range (last 30 days by default)
        $startDate = $date->clone()->subDays(30)->startOfDay();
        $endDate = $date->clone()->endOfDay();

        $this->info("Sending scheduled reports for period: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

        if (in_array($type, ['all', 'agent'])) {
            $this->sendAgentReports($startDate, $endDate);
        }

        if (in_array($type, ['all', 'system'])) {
            $this->sendSystemReports($startDate, $endDate);
        }

        if (in_array($type, ['all', 'investor'])) {
            $this->sendInvestorReports($startDate, $endDate);
        }

        $this->info('Reports sent successfully!');
    }

    /**
     * Send agent performance reports to all agents
     */
    private function sendAgentReports(Carbon $startDate, Carbon $endDate): void
    {
        $agents = User::role('agent')
            ->whereNotNull('cooperative_id')
            ->with('cooperative')
            ->get();

        $this->withProgressBar($agents)->each(function (User $agent) use ($startDate, $endDate) {
            try {
                Mail::to($agent->email)
                    ->send(new AgentPerformanceReportMail($agent->cooperative, $startDate, $endDate));
                
                \Illuminate\Support\Facades\Log::info('Agent report sent', [
                    'user_id' => $agent->id,
                    'agent' => $agent->name,
                    'cooperative_id' => $agent->cooperative_id,
                    'email' => $agent->email,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send agent report', [
                    'user_id' => $agent->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("\nFailed to send report to {$agent->email}: {$e->getMessage()}");
            }
        });

        $this->newLine();
        $this->info("Sent {$agents->count()} agent reports");
    }

    /**
     * Send system analytics reports to all admins
     */
    private function sendSystemReports(Carbon $startDate, Carbon $endDate): void
    {
        $admins = User::role(['super_admin', 'admin'])
            ->get();

        $this->withProgressBar($admins)->each(function (User $admin) use ($startDate, $endDate) {
            try {
                Mail::to($admin->email)
                    ->send(new SystemAnalyticsReportMail($startDate, $endDate));
                
                \Illuminate\Support\Facades\Log::info('System report sent', [
                    'user_id' => $admin->id,
                    'admin' => $admin->name,
                    'email' => $admin->email,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send system report', [
                    'user_id' => $admin->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("\nFailed to send report to {$admin->email}: {$e->getMessage()}");
            }
        });

        $this->newLine();
        $this->info("Sent {$admins->count()} system reports");
    }

    /**
     * Send investor portfolio reports to all investors
     */
    private function sendInvestorReports(Carbon $startDate, Carbon $endDate): void
    {
        $investors = User::role('investor')
            ->get();

        $this->withProgressBar($investors)->each(function (User $investor) use ($startDate, $endDate) {
            try {
                Mail::to($investor->email)
                    ->send(new InvestorPortfolioReportMail($investor, $startDate, $endDate));
                
                \Illuminate\Support\Facades\Log::info('Investor report sent', [
                    'user_id' => $investor->id,
                    'investor' => $investor->name,
                    'email' => $investor->email,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send investor report', [
                    'user_id' => $investor->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("\nFailed to send report to {$investor->email}: {$e->getMessage()}");
            }
        });

        $this->newLine();
        $this->info("Sent {$investors->count()} investor reports");
    }
}
