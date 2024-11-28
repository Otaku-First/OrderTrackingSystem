<?php

namespace App\Http\Controllers\Order;

use App\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Order\Requests\ChangeOrderStatusRequest;
use App\Http\Controllers\Order\Requests\IndexOrderRequest;
use App\Http\Controllers\Order\Requests\StoreOrderRequest;
use App\Http\Controllers\Order\Requests\UpdateOrderRequest;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

/**
 * @OA\Info(
 *     title="Order API",
 *     version="1.0.0",
 *     description="API для управління замовленнями"
 * )
 *
 * @OA\Tag(
 *     name="Orders",
 *     description="API для роботи з замовленнями"
 * )
 *
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     required={"product_name", "amount", "status"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="product_name", type="string", example="Example Product"),
 *     @OA\Property(property="amount", type="number", example=150.75),
 *     @OA\Property(property="status", type="string", example="new"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00Z")
 * )
 *
 * @OA\Schema(
 *     schema="StoreOrderRequest",
 *     type="object",
 *     required={"product_name", "amount"},
 *     @OA\Property(property="product_name", type="string", example="Example Product", description="Назва продукту"),
 *     @OA\Property(property="amount", type="number", example=100.50, description="Сума замовлення")
 * )
 *
 * @OA\Schema(
 *     schema="UpdateOrderRequest",
 *     type="object",
 *     required={},
 *     @OA\Property(property="product_name", type="string", example="Updated Product", description="Оновлена назва продукту"),
 *     @OA\Property(property="amount", type="number", example=200.75, description="Оновлена сума замовлення"),
 *     @OA\Property(property="status", type="string", example="completed", description="Статус замовлення")
 * )
 *
 * @OA\Schema(
 *      schema="ChangeOrderStatusRequest",
 *      type="object",
 *      required={"status"},
 *      @OA\Property(property="status", type="string", example="completed", description="Новий статус замовлення")
 *  )
 */
class OrderController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Отримати список замовлень",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер сторінки для пагінації",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="sort_field",
     *         in="query",
     *         description="Поле для сортування",
     *         required=false,
     *         @OA\Schema(type="string", example="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Напрямок сортування (asc, desc)",
     *         required=false,
     *         @OA\Schema(type="string", example="asc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список замовлень",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Order")
     *         )
     *     )
     * )
     */
    public function index(IndexOrderRequest $request)
    {
        $params = $request->validated();


        $sortField = $params['sort_field'] ?? 'created_at';
        $sortOrder = $params['sort_order'] ?? 'desc';
        $filters = $params['filters'] ?? [];




        $cacheKey = 'orders:user:' . auth()->id()
            . ':sort:' . $sortField
            . ':order:' . $sortOrder
            . ':filters:' . json_encode($filters)
            . ':page:' . ($params['page'] ?? 1);


        $orders = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($filters, $sortField, $sortOrder) {
            $query = Order::where('user_id', auth()->id());
            return $this->applyFiltersAndSorting($query, $filters, $sortField, $sortOrder)
                ->simplePaginate(10);
        });

        return response()->json($orders);
    }


    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     summary="Отримати замовлення за ID",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID замовлення",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Деталі замовлення",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Замовлення не знайдено"
     *     )
     * )
     */
    public function show(int $id)
    {
        $order = Order::where('user_id', auth()->id())
            ->with(['user' => function ($query) {
                $query->select('id', 'name');
            }])
            ->find($id);


        if (!$order) {
            return response()->json([
                'error' => 'Замовлення не знайдено',
            ], 404);
        }

        return response()->json($order->makeHidden(['user_id']));
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Створити нове замовлення",
     *     tags={"Orders"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreOrderRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Замовлення успішно створено",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     )
     * )
     */
    public function store(StoreOrderRequest $request)
    {
        $order = Order::create([
            'user_id' => auth()->id(),
            'status' => OrderStatusEnum::NEW->name,
            ...$request->validated(),
        ]);

        // Очищення кешу після створення нового замовлення
        $this->clearOrderCache();

        return response()->json($order, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/orders/{id}",
     *     summary="Оновити замовлення",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID замовлення",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateOrderRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Замовлення успішно оновлено",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     )
     * )
     */
    public function update(UpdateOrderRequest $request, int $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'error' => 'Замовлення не знайдено',
            ], 404);
        }

        $order->fill([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        $order->save();

        $this->clearOrderCache();

        return response()->json([
            'message' => 'Замовлення успішно оновлено',
            'order' => $order,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/orders/{id}/change-status",
     *     summary="Змінити статус замовлення",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID замовлення",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ChangeOrderStatusRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Статус замовлення успішно змінено",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Замовлення не знайдено"
     *     )
     * )
     */
    public function changeStatus(ChangeOrderStatusRequest $request, int $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'error' => 'Замовлення не знайдено',
            ], 404);
        }

        $order->fill([
            'status' => $request->validated('status'),
        ]);

        $order->save();

        $this->clearOrderCache();

        return response()->json([
            'message' => 'Замовлення успішно оновлено',
            'order' => $order,
        ]);
    }
    /**
     * @OA\Delete(
     *     path="/api/orders/{id}",
     *     summary="Видалити замовлення",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID замовлення",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Замовлення успішно видалено"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Замовлення не знайдено"
     *     )
     * )
     */
    public function destroy(int $id)
    {
        $deleted = Order::destroy($id);

        if ($deleted) {
            $this->clearOrderCache();
        }

        return response()->json([
            'message' => $deleted ? 'Замовлення успішно видалено' : 'Замовлення не знайдено',
        ]);
    }

    private function clearOrderCache()
    {
        $userId = auth()->id();
        Cache::tags('orders_user_' . $userId)->flush();
    }

    private function applyFiltersAndSorting($query, array $filters, $sortField, $sortOrder)
    {

        if (!empty($filters['product_name'])) {
            $query->where('product_name', 'like', '%' . $filters['product_name'] . '%');
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['amount_min'])) {
            $query->where('amount', '>=', $filters['amount_min']);
        }

        if (!empty($filters['amount_max'])) {
            $query->where('amount', '<=', $filters['amount_max']);
        }


        $allowedSortFields = ['product_name', 'amount', 'status', 'created_at'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    /**
     * @OA\Get(
     *     path="/api/orders/export-csv",
     *     summary="Експортувати замовлення у CSV",
     *     tags={"Orders"},
     *     @OA\Response(
     *         response=200,
     *         description="CSV файл успішно створено"
     *     )
     * )
     */
    public function exportToCsv(Request $request)
    {
        $orders = Order::where('user_id', auth()->id())->get();

        $fileName = 'orders_' . Str::slug(auth()->user()->name) . '_' . now()->format('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['ID', 'Product Name', 'Amount', 'Status', 'Created At']);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->product_name,
                    $order->amount,
                    $order->status,
                    $order->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
