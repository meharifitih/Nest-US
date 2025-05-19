<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateRentInvoices extends Command
{
    protected $signature = 'rent:generate-invoices';
    protected $description = 'Generate rent invoices based on tenant lease start date';

    public function handle()
    {
        $units = PropertyUnit::whereHas('tenants')->get();
        $now = Carbon::now();

        foreach ($units as $unit) {
            $tenant = $unit->tenants;
            if (!$tenant) continue;

            // Get tenant's lease start date
            $leaseStartDate = Carbon::parse($tenant->lease_start_date);
            $leaseEndDate = Carbon::parse($tenant->lease_end_date);

            // Skip if we're past the lease end date
            if ($now->gt($leaseEndDate)) continue;

            // Get the last invoice for this unit
            $lastInvoice = Invoice::where('unit_id', $unit->id)
                ->orderBy('invoice_month', 'desc')
                ->first();

            $startDate = $lastInvoice ? Carbon::parse($lastInvoice->invoice_month)->addMonth() : $leaseStartDate;

            // Generate invoices based on rent type
            switch ($unit->rent_type) {
                case 'monthly':
                    $this->generateMonthlyInvoice($unit, $tenant, $startDate, $leaseEndDate);
                    break;
                case 'quarterly':
                    $this->generateQuarterlyInvoice($unit, $tenant, $startDate, $leaseEndDate);
                    break;
                case 'six_months':
                    $this->generateSixMonthInvoice($unit, $tenant, $startDate, $leaseEndDate);
                    break;
                case 'yearly':
                    $this->generateYearlyInvoice($unit, $tenant, $startDate, $leaseEndDate);
                    break;
            }
        }

        $this->info('Rent invoices generated successfully.');
    }

    private function generateMonthlyInvoice($unit, $tenant, $startDate, $leaseEndDate)
    {
        $now = Carbon::now();
        
        // Calculate next invoice date based on lease start date
        $nextInvoiceDate = $startDate->copy();
        while ($nextInvoiceDate->lte($now) && $nextInvoiceDate->lte($leaseEndDate)) {
            $this->createInvoice($unit, $tenant, $nextInvoiceDate, $nextInvoiceDate->copy()->endOfMonth());
            $nextInvoiceDate->addMonth();
        }
    }

    private function generateQuarterlyInvoice($unit, $tenant, $startDate, $leaseEndDate)
    {
        $now = Carbon::now();
        
        // Calculate next invoice date based on lease start date
        $nextInvoiceDate = $startDate->copy();
        while ($nextInvoiceDate->lte($now) && $nextInvoiceDate->lte($leaseEndDate)) {
            $this->createInvoice($unit, $tenant, $nextInvoiceDate, $nextInvoiceDate->copy()->addMonths(2)->endOfMonth());
            $nextInvoiceDate->addMonths(3);
        }
    }

    private function generateSixMonthInvoice($unit, $tenant, $startDate, $leaseEndDate)
    {
        $now = Carbon::now();
        
        // Calculate next invoice date based on lease start date
        $nextInvoiceDate = $startDate->copy();
        while ($nextInvoiceDate->lte($now) && $nextInvoiceDate->lte($leaseEndDate)) {
            $this->createInvoice($unit, $tenant, $nextInvoiceDate, $nextInvoiceDate->copy()->addMonths(5)->endOfMonth());
            $nextInvoiceDate->addMonths(6);
        }
    }

    private function generateYearlyInvoice($unit, $tenant, $startDate, $leaseEndDate)
    {
        $now = Carbon::now();
        
        // Calculate next invoice date based on lease start date
        $nextInvoiceDate = $startDate->copy();
        while ($nextInvoiceDate->lte($now) && $nextInvoiceDate->lte($leaseEndDate)) {
            $this->createInvoice($unit, $tenant, $nextInvoiceDate, $nextInvoiceDate->copy()->addMonths(11)->endOfMonth());
            $nextInvoiceDate->addYear();
        }
    }

    private function createInvoice($unit, $tenant, $startDate, $endDate)
    {
        // Check if invoice already exists for this period
        $existingInvoice = Invoice::where('unit_id', $unit->id)
            ->where('invoice_month', $startDate->format('Y-m-d'))
            ->first();

        if ($existingInvoice) return;

        $invoice = new Invoice();
        $invoice->invoice_id = $this->generateInvoiceNumber();
        $invoice->property_id = $unit->property_id;
        $invoice->unit_id = $unit->id;
        $invoice->invoice_month = $startDate->format('Y-m-d');
        $invoice->end_date = $endDate->format('Y-m-d');
        $invoice->status = 'open';
        $invoice->parent_id = $unit->parent_id;
        $invoice->save();

        // Add rent item
        $invoiceItem = new InvoiceItem();
        $invoiceItem->invoice_id = $invoice->id;
        $invoiceItem->invoice_type = 'rent';
        $invoiceItem->amount = $unit->rent;
        $invoiceItem->description = 'Rent for ' . $startDate->format('M Y') . ' to ' . $endDate->format('M Y');
        $invoiceItem->save();

        // Send email notification to tenant
        if ($tenant->email) {
            $data = [
                'name' => $tenant->name,
                'invoice_id' => $invoice->invoice_id,
                'amount' => $unit->rent,
                'due_date' => $endDate->format('Y-m-d'),
                'property_name' => $unit->property->name,
                'unit_name' => $unit->name,
                'period' => $startDate->format('M Y') . ' to ' . $endDate->format('M Y')
            ];

            \Mail::send('emails.invoice_created', $data, function($message) use ($tenant, $invoice) {
                $message->to($tenant->email, $tenant->name)
                    ->subject('New Rent Invoice #' . $invoice->invoice_id . ' Generated');
            });
        }
    }

    private function generateInvoiceNumber()
    {
        $lastInvoice = Invoice::orderBy('id', 'desc')->first();
        $number = $lastInvoice ? $lastInvoice->invoice_id + 1 : 1;
        return str_pad($number, 6, '0', STR_PAD_LEFT);
    }
} 