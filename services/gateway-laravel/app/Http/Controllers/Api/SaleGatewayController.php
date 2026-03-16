<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SaleGatewayController extends Controller
{
    public function index()
    {
        $response = Http::withHeaders([
            'X-Gateway-Token' => env('GATEWAY_SERVICE_TOKEN'),
        ])->get(env('SALES_SERVICE_URL') . '/sales');

        return response()->json($response->json(), $response->status());
    }

    public function show(string $id)
    {
        $response = Http::withHeaders([
            'X-Gateway-Token' => env('GATEWAY_SERVICE_TOKEN'),
        ])->get(env('SALES_SERVICE_URL') . '/sales/' . $id);

        return response()->json($response->json(), $response->status());
    }

    public function byUser(string $userId)
    {
        $response = Http::withHeaders([
            'X-Gateway-Token' => env('GATEWAY_SERVICE_TOKEN'),
        ])->get(env('SALES_SERVICE_URL') . '/sales/user/' . $userId);

        return response()->json($response->json(), $response->status());
    }    

    public function byDateRange(Request $request)
    {
        $validated = $request->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date',
        ]);

        $response = Http::withHeaders([
            'X-Gateway-Token' => env('GATEWAY_SERVICE_TOKEN'),
        ])->get(env('SALES_SERVICE_URL') . '/sales/date-range/search', $validated);

        return response()->json($response->json(), $response->status());
    }   
    
    public function processSale(Request $request)
    {
        $validated = $request->validate([
            'productId' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $gatewayHeaders = [
            'X-Gateway-Token' => env('GATEWAY_SERVICE_TOKEN'),
        ];

        $user = auth('api')->user();

        $inventoryResponse = Http::withHeaders($gatewayHeaders)
            ->get(env('INVENTORY_SERVICE_URL') . '/products/' . $validated['productId']);

        if (! $inventoryResponse->successful()) {
            return response()->json([
                'message' => 'No se pudo consultar el producto en inventario.',
                'inventory_response' => $inventoryResponse->json(),
            ], $inventoryResponse->status());
        }

        $product = $inventoryResponse->json();
        $currentStock = (int) ($product['stock'] ?? 0);

        if ($currentStock < $validated['quantity']) {
            return response()->json([
                'message' => 'Stock insuficiente.',
            ], 400);
        }

        $salePayload = [
            'userId' => $user->id,
            'productId' => $validated['productId'],
            'productName' => $product['name'],
            'quantity' => $validated['quantity'],
            'unitPrice' => $product['price'],
        ];

        $salesResponse = Http::withHeaders($gatewayHeaders)
            ->post(env('SALES_SERVICE_URL') . '/sales', $salePayload);

        if (! $salesResponse->successful()) {
            return response()->json([
                'message' => 'No se pudo registrar la venta.',
                'sales_response' => $salesResponse->json(),
            ], $salesResponse->status());
        }

        $stockResponse = Http::withHeaders($gatewayHeaders)
            ->patch(env('INVENTORY_SERVICE_URL') . '/products/' . $validated['productId'] . '/stock', [
                'quantity' => $validated['quantity'],
            ]);

        if (! $stockResponse->successful()) {
            return response()->json([
                'message' => 'La venta fue registrada, pero no se pudo actualizar el stock.',
                'sale' => $salesResponse->json(),
                'inventory_response' => $stockResponse->json(),
            ], 500);
        }

        return response()->json([
            'message' => 'Venta procesada correctamente.',
            'sale' => $salesResponse->json()['sale'] ?? $salesResponse->json(),
            'inventory' => $stockResponse->json(),
        ], 201);
    }    

}
