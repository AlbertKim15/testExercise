<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class WildberriesApiService
{
    protected string $baseUrl;
    protected string $key;

    public function __construct()
    {
        $this->baseUrl = 'http://109.73.206.144:6969';
        $this->key     = 'E6kUTYrYwZq2tN4QEtyzsbEBk3ie';
    }

    public function fetchAll(string $endpoint, array $params = []): array
    {
        $page     = 1;
        $allItems = [];
        $limit    = 100;

        while (true) {
            $params = array_merge($params, [
                'key'   => $this->key,
                'page'  => $page,
                'limit' => $limit,
            ]);

            $response = Http::timeout(30)->get("{$this->baseUrl}/api/{$endpoint}", $params);

            // Только при ошибке 429 делаем задержку
            if ($response->status() == 429) {
                sleep(5); // Ждем 5 секунд и повторяем ТОТ ЖЕ запрос
                continue;
            }

            if (! $response->successful()) {
                throw new \Exception("API error: {$response->status()}");
            }

            $data  = $response->json();
            $items = $data['data'] ?? [];

            if (empty($items)) {
                break;
            }

            $allItems = array_merge($allItems, $items);

            $currentPage = $data['meta']['current_page'] ?? $page;
            $lastPage    = $data['meta']['last_page'] ?? $currentPage;

            if ($currentPage >= $lastPage) {
                break;
            }

            $page++;
            // НЕТ ЗАДЕРЖКИ МЕЖДУ СТРАНИЦАМИ
        }

        return $allItems;
    }
}
