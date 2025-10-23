<?php

namespace Database\Factories;

use App\Enums\SaleStatus;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalCost = $this->faker->randomFloat(2, 100, 1000);
        $totalAmount = $totalCost * $this->faker->randomFloat(2, 1.2, 2.5);
        $totalProfit = $totalAmount - $totalCost;

        return [
            'total_amount' => $totalAmount,
            'total_cost' => $totalCost,
            'total_profit' => $totalProfit,
            'status' => SaleStatus::Processing,
        ];
    }

    /**
     * Indicate that the sale is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SaleStatus::Completed,
        ]);
    }

    /**
     * Indicate that the sale is processing.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SaleStatus::Processing,
        ]);
    }

    /**
     * Indicate that the sale has failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SaleStatus::Failed,
        ]);
    }

    /**
     * Indicate that the sale is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SaleStatus::Pending,
        ]);
    }
}
