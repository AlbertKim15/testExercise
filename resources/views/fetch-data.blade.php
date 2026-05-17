<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WB API Fetcher</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f3f4f6;
            padding: 40px 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 24px;
            margin-bottom: 24px;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 24px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        label {
            display: block;
            font-weight: 500;
            margin-bottom: 6px;
            color: #374151;
        }
        input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #3b82f6;
            ring: 2px solid #3b82f6;
        }
        .row {
            display: flex;
            gap: 16px;
        }
        .row .form-group {
            flex: 1;
        }
        button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover {
            background: #2563eb;
        }
        button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        .result {
            margin-top: 16px;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
        }
        .result.success {
            background: #d1fae5;
            color: #065f46;
        }
        .result.error {
            background: #fee2e2;
            color: #991b1b;
        }
        .result.info {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-list {
            margin-top: 20px;
        }
        .status-item {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status-item:last-child {
            border-bottom: none;
        }
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge.success {
            background: #d1fae5;
            color: #065f46;
        }
        .badge.error {
            background: #fee2e2;
            color: #991b1b;
        }
        .badge.count {
            background: #e5e7eb;
            color: #374151;
        }
        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f4f6;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .button-group {
            display: flex;
            gap: 12px;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>WB API Fetcher</h1>
            <div class="subtitle">Загрузка данных о продажах, заказах, складах и доходах</div>

            <form method="POST" action="{{ route('fetch.run') }}" id="fetchForm">
                @csrf
                <div class="row">
                    <div class="form-group">
                        <label>Дата от (dateFrom)</label>
                        <input type="date" name="date_from" value="{{ old('date_from', $dateFrom ?? '2026-05-01') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Дата до (dateTo)</label>
                        <input type="date" name="date_to" value="{{ old('date_to', $dateTo ?? '2026-05-31') }}" required>
                    </div>
                </div>
                <div class="button-group">
                    <button type="submit" id="submitBtn">Загрузить данные</button>
                    <div id="loading" style="display: none;">
                        <div class="loading"></div>
                        <span style="margin-left: 8px; color: #6b7280;">Загрузка...</span>
                    </div>
                </div>
            </form>

            @if(isset($results))
                <div class="status-list">
                    <div class="status-item">
                        <span>📦 Продажи</span>
                        @if($results['sales']['status'] === 'success')
                            <span class="badge success">✅ {{ $results['sales']['count'] }} записей</span>
                        @else
                            <span class="badge error">❌ {{ $results['sales']['message'] ?? 'Ошибка' }}</span>
                        @endif
                    </div>
                    <div class="status-item">
                        <span>📦 Заказы</span>
                        @if($results['orders']['status'] === 'success')
                            <span class="badge success">✅ {{ $results['orders']['count'] }} записей</span>
                        @else
                            <span class="badge error">❌ {{ $results['orders']['message'] ?? 'Ошибка' }}</span>
                        @endif
                    </div>
                    <div class="status-item">
                        <span>📦 Склады</span>
                        @if($results['stocks']['status'] === 'success')
                            <span class="badge success">✅ {{ $results['stocks']['count'] }} записей</span>
                        @else
                            <span class="badge error">❌ {{ $results['stocks']['message'] ?? 'Ошибка' }}</span>
                        @endif
                    </div>
                    <div class="status-item">
                        <span>📦 Доходы</span>
                        @if($results['incomes']['status'] === 'success')
                            <span class="badge success">✅ {{ $results['incomes']['count'] }} записей</span>
                        @else
                            <span class="badge error">❌ {{ $results['incomes']['message'] ?? 'Ошибка' }}</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="card">
            <h2 style="font-size: 18px; margin-bottom: 12px;">Количество записей в БД</h2>
            <div class="status-item">
                <span>Продажи</span>
                <span class="badge count">{{ \App\Models\Sale::count() }} записей</span>
            </div>
            <div class="status-item">
                <span>Заказы</span>
                <span class="badge count">{{ \App\Models\Order::count() }} записей</span>
            </div>
            <div class="status-item">
                <span>Склады</span>
                <span class="badge count">{{ \App\Models\Stock::count() }} записей</span>
            </div>
            <div class="status-item">
                <span>Доходы</span>
                <span class="badge count">{{ \App\Models\Income::count() }} записей</span>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('fetchForm').addEventListener('submit', function() {
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('loading').style.display = 'flex';
        });
    </script>
</body>
</html>
