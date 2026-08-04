<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\DailyCollection;
use App\Models\DebtPayment;
use App\Models\Delivery;
use App\Models\Expense;
use App\Models\LeakageReturn;
use App\Models\ProductionBatch;
use App\Models\RawMaterialPurchase;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Staff Users
        $manager = User::create([
            'name' => 'Alhaji Anzar (Manager)',
            'email' => 'manager@anzar.com',
            'username' => 'manager',
            'phone' => '08031234567',
            'role' => 'manager',
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);

        $productionStaff = User::create([
            'name' => 'Usman Production Staff',
            'email' => 'production@anzar.com',
            'username' => 'production',
            'phone' => '08059876543',
            'role' => 'production_staff',
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);

        $salesStaff = User::create([
            'name' => 'Kabiru Sales Staff',
            'email' => 'sales@anzar.com',
            'username' => 'sales',
            'phone' => '08023456789',
            'role' => 'sales_staff',
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);

        // 2. Customer Shops
        $aishaStore = Customer::create([
            'shop_name' => 'Aisha Provision Store',
            'owner_name' => 'Mrs. Aisha Bello',
            'phone' => '08011112222',
            'address' => 'Shop 14, Central Market, Kano',
            'status' => 'active',
        ]);

        $belloSupermarket = Customer::create([
            'shop_name' => 'Bello Supermarket',
            'owner_name' => 'Alhaji Bello Kano',
            'phone' => '08033334444',
            'address' => 'No 45 Commercial Avenue, Kano',
            'status' => 'active',
        ]);

        $danlamiMart = Customer::create([
            'shop_name' => 'Danlami Mini Mart',
            'owner_name' => 'Danlami Umar',
            'phone' => '08055556666',
            'address' => 'Zoo Road opposite Hospital, Kano',
            'status' => 'active',
        ]);

        // 3. Raw Material Nylon Purchase
        $rmp1 = RawMaterialPurchase::create([
            'purchase_no' => 'RMP-001',
            'purchase_date' => '2026-07-28',
            'quantity_kg' => 50.00,
            'unit_price' => 1800.00,
            'total_cost' => 90000.00,
            'remarks' => 'High quality 50 micron nylon rolls',
        ]);

        $rmp2 = RawMaterialPurchase::create([
            'purchase_no' => 'RMP-002',
            'purchase_date' => '2026-07-30',
            'quantity_kg' => 60.00,
            'unit_price' => 1750.00,
            'total_cost' => 105000.00,
            'remarks' => 'Standard packaging rolls',
        ]);

        // 4. Production Batches
        $batch1 = ProductionBatch::create([
            'batch_no' => 'PB-001',
            'raw_material_purchase_id' => $rmp1->id,
            'production_date' => '2026-07-29',
            'quantity_used_kg' => 50.00,
            'bags_produced' => 450,
            'produced_by' => $productionStaff->id,
            'status' => 'active',
        ]);

        // 5. Deliveries
        $del1 = Delivery::create([
            'delivery_no' => 'DEL-001',
            'batch_id' => $batch1->id,
            'customer_id' => $aishaStore->id,
            'delivery_date' => '2026-07-29',
            'bags_delivered' => 150,
            'unit_price' => 400.00,
            'total_amount' => 60000.00,
            'paid_amount' => 40000.00,
            'delivered_by' => $salesStaff->id,
        ]);

        $del2 = Delivery::create([
            'delivery_no' => 'DEL-002',
            'batch_id' => $batch1->id,
            'customer_id' => $belloSupermarket->id,
            'delivery_date' => '2026-07-29',
            'bags_delivered' => 200,
            'unit_price' => 400.00,
            'total_amount' => 80000.00,
            'paid_amount' => 80000.00, // Fully paid
            'delivered_by' => $salesStaff->id,
        ]);

        // 6. Outstanding Debt for Aisha Store
        $debt1 = CustomerDebt::create([
            'delivery_id' => $del1->id,
            'customer_id' => $aishaStore->id,
            'batch_id' => $batch1->id,
            'outstanding_amount' => 20000.00,
            'status' => 'open',
        ]);

        // Partial repayment on Debt 1
        DebtPayment::create([
            'debt_id' => $debt1->id,
            'customer_id' => $aishaStore->id,
            'batch_id' => $batch1->id,
            'delivery_id' => $del1->id,
            'payment_date' => '2026-07-30',
            'payment_method' => 'cash',
            'amount' => 5000.00,
            'received_by' => $salesStaff->id,
        ]);

        $debt1->update([
            'outstanding_amount' => 15000.00,
        ]);

        // 7. Daily Collections
        DailyCollection::create([
            'batch_id' => $batch1->id,
            'collection_date' => '2026-07-29',
            'cash_amount' => 75000.00,
            'transfer_amount' => 45000.00,
            'recorded_by' => $salesStaff->id,
            'remarks' => 'Day 1 sales route collection',
        ]);

        // 8. Leakage Returns
        LeakageReturn::create([
            'delivery_id' => $del1->id,
            'batch_id' => $batch1->id,
            'customer_id' => $aishaStore->id,
            'date' => '2026-07-30',
            'returned_pieces' => 12,
            'replacement_issued' => 12,
            'remarks' => 'Returned 12 leaking sachets, replaced immediately',
        ]);

        // 9. Operational Expenses
        Expense::create([
            'expense_date' => '2026-07-29',
            'category' => 'Fuel',
            'description' => 'Diesel fuel for factory generator (50 Litres)',
            'amount' => 45000.00,
            'recorded_by' => $manager->id,
        ]);

        Expense::create([
            'expense_date' => '2026-07-30',
            'category' => 'Water Treatment Chemicals',
            'description' => 'Chlorine and UV filter cartridge replacements',
            'amount' => 28000.00,
            'recorded_by' => $manager->id,
        ]);
    }
}
