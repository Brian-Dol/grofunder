<?php

namespace App\Console\Commands;

use App\Models\Payments;
use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CreateSubscription extends Command
{
    protected $signature = 'subscription:create {--user-id=1} {--days=365}';
    protected $description = 'Create an active subscription for a user';

    public function handle()
    {
        $userId = $this->option('user-id');
        $days = $this->option('days');

        $user = User::find($userId);
        if (!$user) {
            $this->error("User ID {$userId} not found");
            return 1;
        }

        // Check if subscription already exists
        $existing = Payments::where('payer_id', $userId)
            ->where('payment_expires_at', '>', Carbon::now())
            ->first();

        if ($existing) {
            $this->info("User already has an active subscription");
            return 0;
        }

        // Create new subscription
        Payments::create([
            'payer_id' => $userId,
            'organization_id' => $user->organization_id,
            'payment_amount' => 120,
            'payment_made_at' => Carbon::now(),
            'payment_expires_at' => Carbon::now()->addDays($days),
            'transaction_reference' => 'manual-' . uniqid(),
        ]);

        $this->info("Subscription created for {$user->email} expiring in {$days} days");
        return 0;
    }
}
