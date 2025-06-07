<?php

namespace App\Console\Commands;

use App\Models\Hoa;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateHoaFees extends Command
{
    protected $signature = 'hoa:generate-fees';
    protected $description = 'Generate HOA fees based on their frequency';

    public function handle()
    {
        $this->info('Starting HOA fee generation...');
        
        // Get all active HOAs that are paid
        $activeHoas = Hoa::where('status', 'paid')
            ->whereNotNull('paid_date')
            ->get();

        foreach ($activeHoas as $hoa) {
            try {
                $lastDueDate = Carbon::parse($hoa->due_date);
                $nextDueDate = $this->calculateNextDueDate($lastDueDate, $hoa->frequency);
                
                // Only create new HOA if next due date is in the future
                if ($nextDueDate->isFuture()) {
                    // Check if HOA for next period already exists
                    $existingHoa = Hoa::where('property_id', $hoa->property_id)
                        ->where('unit_id', $hoa->unit_id)
                        ->where('hoa_type_id', $hoa->hoa_type_id)
                        ->where('due_date', $nextDueDate)
                        ->first();

                    if (!$existingHoa) {
                        Hoa::create([
                            'property_id' => $hoa->property_id,
                            'unit_id' => $hoa->unit_id,
                            'hoa_type_id' => $hoa->hoa_type_id,
                            'amount' => $hoa->amount,
                            'frequency' => $hoa->frequency,
                            'status' => 'open',
                            'due_date' => $nextDueDate,
                            'description' => "Auto-generated HOA fee for {$hoa->frequency} period",
                            'created_by' => $hoa->created_by,
                        ]);

                        $this->info("Created new HOA fee for property {$hoa->property_id}, unit {$hoa->unit_id}");
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error generating HOA fee: " . $e->getMessage());
                $this->error("Error processing HOA ID {$hoa->id}: " . $e->getMessage());
            }
        }

        $this->info('HOA fee generation completed.');
    }

    private function calculateNextDueDate(Carbon $lastDueDate, string $frequency): Carbon
    {
        return match ($frequency) {
            'monthly' => $lastDueDate->copy()->addMonth(),
            'quarterly' => $lastDueDate->copy()->addMonths(3),
            'semi_annual' => $lastDueDate->copy()->addMonths(6),
            'annual' => $lastDueDate->copy()->addYear(),
            default => $lastDueDate->copy()->addMonth(),
        };
    }
} 