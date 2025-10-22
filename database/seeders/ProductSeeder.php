<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'Caneta Esferográfica Azul',
            'Caneta Esferográfica Preta',
            'Caneta Gel Vermelha',
            'Lápis Preto HB',
            'Lápis de Cor 12 Cores',
            'Borracha Branca',
            'Apontador com Depósito',
            'Caderno Universitário 200 folhas',
            'Caderno de Desenho',
            'Bloco de Notas Adesivas',
            'Marcador de Texto Amarelo',
            'Marcador de Texto Verde',
            'Marcador de Quadro Branco',
            'Grampeador Pequeno',
            'Caixa de Grampos nº10',
            'Pasta Plástica com Elástico',
            'Envelope A4 Pardo',
            'Envelope Ofício Branco',
            'Clips de Papel nº2',
            'Clips de Papel nº5',
            'Fita Adesiva Transparente',
            'Fita Crepe',
            'Tesoura Escolar',
            'Régua 30cm',
            'Corretivo Líquido',
            'Corretivo em Fita',
            'Pincel Atômico Preto',
            'Papel Sulfite A4 500 folhas',
            'Cartolina Colorida',
            'Papel Kraft Rolo',
        ];

        foreach ($items as $index => $name) {
            $cost = random_int(100, 1000) / 100; // 1.00 a 10.00
            $margin = random_int(20, 60); // margem %
            $sale = round($cost * (1 + $margin / 100), 2);

            $product = Product::create([
                'sku' => 'SKU-'.Str::padLeft((string) ($index + 1), 4, '0'),
                'name' => $name,
                'description' => "Produto de papelaria: {$name}",
                'cost_price' => $cost,
                'sale_price' => $sale,
            ]);

            // Definir last_updated
            if ($index < 10) {
                // Entre 90 e 120 dias atrás
                $daysAgo = random_int(90, 120);
                $lastUpdated = Carbon::now()->subDays($daysAgo);
            } else {
                $lastUpdated = Carbon::now();
            }

            Inventory::create([
                'product_id' => $product->id,
                'quantity' => random_int(5, 100),
                'last_updated' => $lastUpdated,
            ]);
        }
    }
}
