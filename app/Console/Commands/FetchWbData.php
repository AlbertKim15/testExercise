<?php
namespace App\Console\Commands;

use App\Models\Income;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Stock;
use App\Services\WildberriesApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FetchWbData extends Command
{
    /**
     * Имя и сигнатура консольной команды.
     * php artisan wb:fetch
     */
    protected $signature = 'wb:fetch
                            {--dateFrom= : Дата начала Y-m-d}
                            {--dateTo= : Дата окончания Y-m-d}';

    /**
     * Описание команды.
     */
    protected $description = 'Загрузить данные из API Wildberries (продажи, заказы, склады, доходы)';

    /**
     * Выполнение команды.
     */
    public function handle(WildberriesApiService $api)
    {
        // Определяем даты
        $dateFrom = $this->option('dateFrom') ?? Carbon::now()->subDays(30)->toDateString();
        $dateTo   = $this->option('dateTo') ?? Carbon::now()->toDateString();

        $this->info("=================================");
        $this->info("Загрузка данных с {$dateFrom} по {$dateTo}");
        $this->info("=================================");

        // 1. Продажи
        $this->newLine();
        $this->info("1. Загрузка продаж...");
        try {
            $sales = $api->fetchAll('sales', [
                'dateFrom' => $dateFrom,
                'dateTo'   => $dateTo,
            ]);
            $this->saveSales($sales);
            $this->info("[OK] Сохранено продаж: " . count($sales));
        } catch (\Exception $e) {
            $this->error("[ERROR] Ошибка при загрузке продаж: " . $e->getMessage());
        }

        // 2. Заказы
        $this->newLine();
        $this->info("2. Загрузка заказов...");
        try {
            $orders = $api->fetchAll('orders', [
                'dateFrom' => $dateFrom,
                'dateTo'   => $dateTo,
            ]);
            $this->saveOrders($orders);
            $this->info("[OK] Сохранено заказов: " . count($orders));
        } catch (\Exception $e) {
            $this->error("[ERROR] Ошибка при загрузке заказов: " . $e->getMessage());
        }

        // 3. Склады (только за текущий день)
        $this->newLine();
        $this->info("3. Загрузка остатков на складах...");
        try {
            $stocks = $api->fetchAll('stocks', [
                'dateFrom' => Carbon::now()->toDateString(),
            ]);
            $this->saveStocks($stocks);
            $this->info("[OK] Сохранено остатков: " . count($stocks));
        } catch (\Exception $e) {
            $this->error("[ERROR] Ошибка при загрузке складов: " . $e->getMessage());
        }

        // 4. Доходы
        $this->newLine();
        $this->info("4. Загрузка доходов...");
        try {
            $incomes = $api->fetchAll('incomes', [
                'dateFrom' => $dateFrom,
                'dateTo'   => $dateTo,
            ]);
            $this->saveIncomes($incomes);
            $this->info("[OK] Сохранено доходов: " . count($incomes));
        } catch (\Exception $e) {
            $this->error("[ERROR] Ошибка при загрузке доходов: " . $e->getMessage());
        }

        $this->newLine();
        $this->info("Загрузка завершена");
    }

    /**
     * Сохранить продажи в БД
     */
    protected function saveSales(array $sales): void
    {
        foreach ($sales as $item) {
            // Проверяем, что sale_id существует
            if (! isset($item['sale_id']) || empty($item['sale_id'])) {
                continue; // пропускаем запись без идентификатора
            }

            Sale::updateOrCreate(
                ['sale_id' => $item['sale_id']],
                [
                    'g_number'            => $item['g_number'] ?? null,
                    'date'                => $item['date'] ?? null,
                    'last_change_date'    => $item['last_change_date'] ?? null,
                    'supplier_article'    => $item['supplier_article'] ?? null,
                    'tech_size'           => $item['tech_size'] ?? null,
                    'barcode'             => $item['barcode'] ?? null,
                    'total_price'         => $item['total_price'] ?? null,
                    'discount_percent'    => $item['discount_percent'] ?? null,
                    'is_supply'           => $item['is_supply'] ?? false,
                    'is_realization'      => $item['is_realization'] ?? false,
                    'promo_code_discount' => $item['promo_code_discount'] ?? null,
                    'warehouse_name'      => $item['warehouse_name'] ?? null,
                    'country_name'        => $item['country_name'] ?? null,
                    'oblast_okrug_name'   => $item['oblast_okrug_name'] ?? null,
                    'region_name'         => $item['region_name'] ?? null,
                    'income_id'           => $item['income_id'] ?? null,
                    'sale_id'             => $item['sale_id'],
                    'odid'                => $item['odid'] ?? null,
                    'spp'                 => $item['spp'] ?? null,
                    'for_pay'             => $item['for_pay'] ?? null,
                    'finished_price'      => $item['finished_price'] ?? null,
                    'price_with_disc'     => $item['price_with_disc'] ?? null,
                    'nm_id'               => $item['nm_id'] ?? null,
                    'subject'             => $item['subject'] ?? null,
                    'category'            => $item['category'] ?? null,
                    'brand'               => $item['brand'] ?? null,
                    'is_storno'           => $item['is_storno'] ?? null,
                ]
            );
        }
    }

    /**
     * Сохранить заказы в БД
     */
    protected function saveOrders(array $orders): void
    {
        foreach ($orders as $item) {
            Order::updateOrCreate(
                [
                    'g_number' => $item['g_number'] ?? null,
                    'date'     => $item['date'] ?? null,
                    'nm_id'    => $item['nm_id'] ?? null,
                ],
                [
                    'last_change_date' => $item['last_change_date'] ?? null,
                    'supplier_article' => $item['supplier_article'] ?? null,
                    'tech_size'        => $item['tech_size'] ?? null,
                    'barcode'          => $item['barcode'] ?? null,
                    'total_price'      => $item['total_price'] ?? null,
                    'discount_percent' => $item['discount_percent'] ?? null,
                    'warehouse_name'   => $item['warehouse_name'] ?? null,
                    'oblast'           => $item['oblast'] ?? null,
                    'income_id'        => $item['income_id'] ?? 0,
                    'odid'             => $item['odid'] ?? '0',
                    'nm_id'            => $item['nm_id'] ?? null,
                    'subject'          => $item['subject'] ?? null,
                    'category'         => $item['category'] ?? null,
                    'brand'            => $item['brand'] ?? null,
                    'is_cancel'        => $item['is_cancel'] ?? false,
                    'cancel_dt'        => $item['cancel_dt'] ?? null,
                ]
            );
        }
    }

    /**
     * Сохранить остатки складов в БД
     */
    protected function saveStocks(array $stocks): void
    {
        foreach ($stocks as $item) {
            $data = [
                'date'               => $item['date'] ?? date('Y-m-d'),
                'last_change_date'   => $item['last_change_date'] ?? null,
                'supplier_article'   => $item['supplier_article'] ?? null,
                'tech_size'          => $item['tech_size'] ?? null,
                'barcode'            => $item['barcode'] ?? null,
                'quantity'           => $item['quantity'] ?? 0,
                'is_supply'          => $item['is_supply'] ?? false,
                'is_realization'     => $item['is_realization'] ?? false,
                'quantity_full'      => $item['quantity_full'] ?? 0,
                'warehouse_name'     => $item['warehouse_name'] ?? null,
                'in_way_to_client'   => $item['in_way_to_client'] ?? 0,
                'in_way_from_client' => $item['in_way_from_client'] ?? 0,
                'nm_id'              => $item['nm_id'] ?? null,
                'subject'            => $item['subject'] ?? null,
                'category'           => $item['category'] ?? null,
                'brand'              => $item['brand'] ?? null,
                'sc_code'            => $item['sc_code'] ?? null,
                'price'              => $item['price'] ?? null,
                'discount'           => $item['discount'] ?? null,
            ];

            Stock::updateOrCreate(
                [
                    'nm_id'          => $item['nm_id'] ?? null,
                    'warehouse_name' => $item['warehouse_name'] ?? null,
                    'date'           => $item['date'] ?? date('Y-m-d'),
                ],
                $data
            );
        }
    }

    /**
     * Сохранить доходы в БД
     */
    protected function saveIncomes(array $incomes): void
    {
        foreach ($incomes as $item) {
            Income::updateOrCreate(
                ['income_id' => $item['income_id'] ?? null],
                [
                    'number'           => $item['number'] ?? '',
                    'date'             => $item['date'] ?? null,
                    'last_change_date' => $item['last_change_date'] ?? null,
                    'supplier_article' => $item['supplier_article'] ?? null,
                    'tech_size'        => $item['tech_size'] ?? null,
                    'barcode'          => $item['barcode'] ?? null,
                    'quantity'         => $item['quantity'] ?? 0,
                    'total_price'      => $item['total_price'] ?? '0',
                    'date_close'       => $item['date_close'] ?? null,
                    'warehouse_name'   => $item['warehouse_name'] ?? null,
                    'nm_id'            => $item['nm_id'] ?? null,
                ]
            );
        }
    }
}
