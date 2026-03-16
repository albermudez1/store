<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProductGatewayController extends Controller
{
    public function index()
    {
        $response = Http::withHeaders([
            'X-Gateway-Token' => env('GATEWAY_SERVICE_TOKEN'),
        ])->get(env('INVENTORY_SERVICE_URL') . '/products');

        return response()->json($response->json(), $response->status());
    }

    public function show(string $id)
    {
        $response = Http::withHeaders([
            'X-Gateway-Token' => env('GATEWAY_SERVICE_TOKEN'),
        ])->get(env('INVENTORY_SERVICE_URL') . '/products/' . $id);

        return response()->json($response->json(), $response->status());
    }
    
    public function stock(string $id)
    {
        $response = Http::withHeaders([
            'X-Gateway-Token' => env('GATEWAY_SERVICE_TOKEN'),
        ])->get(env('INVENTORY_SERVICE_URL') . '/products/' . $id . '/stock');

        return response()->json($response->json(), $response->status());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $response = Http::withHeaders([
            'X-Gateway-Token' => env('GATEWAY_SERVICE_TOKEN'),
        ])->post(env('INVENTORY_SERVICE_URL') . '/products', $validated);

        return response()->json($response->json(), $response->status());
    }    

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $response = Http::withHeaders([
            'X-Gateway-Token' => env('GATEWAY_SERVICE_TOKEN'),
        ])->put(env('INVENTORY_SERVICE_URL') . '/products/' . $id, $validated);

        return response()->json($response->json(), $response->status());
    }   
    
    public function destroy(string $id)
    {
        $response = Http::withHeaders([
            'X-Gateway-Token' => env('GATEWAY_SERVICE_TOKEN'),
        ])->delete(env('INVENTORY_SERVICE_URL') . '/products/' . $id);

        return response()->json($response->json(), $response->status());
    }
    
    public function decreaseStock(Request $request, string $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $response = Http::withHeaders([
            'X-Gateway-Token' => env('GATEWAY_SERVICE_TOKEN'),
        ])->patch(env('INVENTORY_SERVICE_URL') . '/products/' . $id . '/stock', $validated);

        return response()->json($response->json(), $response->status());
    }    

}
