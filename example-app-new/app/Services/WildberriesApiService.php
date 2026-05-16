<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $limit    = 100; // уменьшил до 100

        while (true) {
            $params = array_merge($params, [
                'key'   => $this->key,
                'page'  => $page,
                'limit' => $limit,
            ]);

            Log::info("Запрос к API: {$endpoint}, страница {$page}");

            $response = Http::timeout(30)->get("{$this->baseUrl}/api/{$endpoint}", $params);

            if (! $response->successful()) {
                throw new \Exception("API error: {$response->status()} - {$response->body()}");
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

            // ЗАДЕРЖКА 1 СЕКУНДА МЕЖДУ ЗАПРОСАМИ
            sleep(1);
        }

        return $allItems;
    }
}
