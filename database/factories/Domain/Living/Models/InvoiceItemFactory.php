<?php

namespace Database\Factories\Domain\Living\Models;

use App\Domain\Living\Models\Invoice;
use App\Domain\Living\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'label' => 'Sewa Bulanan',
            'item_type' => 'rent',
            'quantity' => 1,
            'unit_price' => 1500000,
            'amount' => 1500000,
        ];
    }
}
