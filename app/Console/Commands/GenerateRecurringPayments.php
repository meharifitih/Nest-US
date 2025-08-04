<?php

namespace App\Console\Commands;

use App\Models\TenantPayment;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateRecurringPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:generate-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate recurring payments for tenants';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting recurring payment generation...');

        // Get all active recurring payments
        $recurringPayments = TenantPayment::where('is_recurring', true)
            ->where('status', 'completed')
            ->where('next_payment_date', '<=', now())
            ->with(['tenant.user'])
            ->get();

        $generatedCount = 0;

        foreach ($recurringPayments as $payment) {
            try {
                // Calculate next payment date
                $nextPaymentDate = $this->calculateNextPaymentDate($payment->next_payment_date, $payment->recurring_interval);
                
                // Create new payment record
                $newPayment = TenantPayment::create([
                    'tenant_id' => $payment->tenant_id,
                    'invoice_id' => $payment->invoice_id,
                    'amount' => $payment->amount,
                    'payment_type' => $payment->payment_type,
                    'payment_method' => 'pending',
                    'due_date' => $nextPaymentDate,
                    'is_recurring' => true,
                    'recurring_interval' => $payment->recurring_interval,
                    'next_payment_date' => $this->calculateNextPaymentDate($nextPaymentDate, $payment->recurring_interval),
                    'parent_id' => $payment->parent_id,
                    'notes' => "Auto-generated recurring payment from payment #{$payment->id}",
                ]);

                // Update the original payment's next payment date
                $payment->update([
                    'next_payment_date' => $newPayment->next_payment_date,
                ]);

                $generatedCount++;
                $this->info("Generated payment #{$newPayment->id} for tenant {$payment->tenant->user->name}");

            } catch (\Exception $e) {
                $this->error("Failed to generate payment for tenant {$payment->tenant->user->name}: {$e->getMessage()}");
            }
        }

        $this->info("Generated {$generatedCount} recurring payments successfully.");
        return 0;
    }

    /**
     * Calculate the next payment date based on interval
     *
     * @param Carbon $currentDate
     * @param string $interval
     * @return Carbon
     */
    private function calculateNextPaymentDate($currentDate, $interval)
    {
        switch ($interval) {
            case 'monthly':
                return $currentDate->copy()->addMonth();
            case 'quarterly':
                return $currentDate->copy()->addMonths(3);
            case 'yearly':
                return $currentDate->copy()->addYear();
            default:
                return $currentDate->copy()->addMonth();
        }
    }
} 