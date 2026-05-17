<?php
namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Stock;
use App\Services\WildberriesApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FetchDataController extends Controller
{
    public function index()
    {
        return view('fetch-data');
    }

    public function run(Request $request, WildberriesApiService $api)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
        ]);

        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        $results = [];

        // 1. Продажи
        try {
            $sales = $api->fetchAll('sales', [
                'dateFrom' => $dateFrom,
                'dateTo'   => $dateTo,
            ]);
            $this->saveSales($sales);
            $results['sales'] = ['status' => 'success', 'count' => count($sales)];
        } catch (\Exception $e) {
            $results['sales'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

// ЗАДЕРЖКА 10 СЕКУНД МЕЖДУ ЭНДПОИНТАМИ
        sleep(10);

// 2. Заказы
        try {
            $orders = $api->fetchAll('orders', [
                'dateFrom' => $dateFrom,
                'dateTo'   => $dateTo,
            ]);
            $this->saveOrders($orders);
            $results['orders'] = ['status' => 'success', 'count' => count($orders)];
        } catch (\Exception $e) {
            $results['orders'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        sleep(10);

// 3. Склады - запускаем с задержкой
        try {
            $stocks = $api->fetchAll('stocks', [
                'dateFrom' => Carbon::now()->toDateString(),
            ]);
            $this->saveStocks($stocks);
            $results['stocks'] = ['status' => 'success', 'count' => count($stocks)];
        } catch (\Exception $e) {
            $results['stocks'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        sleep(10);

// 4. Доходы
        try {
            $incomes = $api->fetchAll('incomes', [
                'dateFrom' => $dateFrom,
                'dateTo'   => $dateTo,
            ]);
            $this->saveIncomes($incomes);
            $results['incomes'] = ['status' => 'success', 'count' => count($incomes)];
        } catch (\Exception $e) {
            $results['incomes'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        return view('fetch-data', [
            'results'  => $results,
            'dateFrom' => $dateFrom,
            'dateTo'   => $dateTo,
        ]);
    }

    private function saveSales(array $sales): void
    {
        foreach ($sales as $item) {
            if (empty($item['sale_id'])) {
                continue;
            }
            Sale::updateOrCreate(
                ['sale_id' => $item['sale_id']],
                $item
            );
        }
    }

    private function saveOrders(array $orders): void
    {
        foreach ($orders as $item) {
            Order::updateOrCreate(
                [
                    'g_number' => $item['g_number'] ?? null,
                    'date'     => $item['date'] ?? null,
                    'nm_id'    => $item['nm_id'] ?? null,
                ],
                $item
            );
        }
    }

    private function saveStocks(array $stocks): void
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

            // Приводим булевы значения к типу bool
            if ($data['is_supply'] === null) {
                $data['is_supply'] = false;
            }
            if ($data['is_realization'] === null) {
                $data['is_realization'] = false;
            }

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

    private function saveIncomes(array $incomes): void
    {
        foreach ($incomes as $item) {
            if (empty($item['income_id'])) {
                continue;
            }
            Income::updateOrCreate(
                ['income_id' => $item['income_id']],
                $item
            );
        }
    }
}
