<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RestaurantController extends Controller
{
    // GET /admin/restaurants
    public function index(Request $request): JsonResponse
    {
        $query = Restaurant::withCount('despachos');

        if ($request->filled('buscar')) {
            $query->where('name', 'ilike', '%' . $request->query('buscar') . '%');
        }

        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');

        $allowedSorts = ['name', 'created_at', 'despachos_count'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy === 'despachos_count' ? 'despachos_count' : $sortBy, $sortDir === 'asc' ? 'asc' : 'desc');

        $perPage = min((int) $request->query('per_page', 12), 50);
        $paginated = $query->paginate($perPage);

        return $this->success([
            'data'  => collect($paginated->items())->map(fn($r) => $this->format($r)),
            'meta'  => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
            ],
        ]);
    }

    // POST /admin/restaurants
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'slug'        => 'required|string|max:50|unique:restaurants,slug|alpha_dash',
            'webhook_url' => 'nullable|url',
        ]);

        $restaurant = Restaurant::create([
            'name'           => $data['name'],
            'slug'           => $data['slug'],
            'api_key'        => Restaurant::generateApiKey(),
            'webhook_url'    => $data['webhook_url'] ?? null,
            'webhook_secret' => Str::random(40),
            'activo'         => true,
        ]);

        return $this->created($this->format($restaurant, true));
    }

    // GET /admin/restaurants/{id}
    public function show(int $id): JsonResponse
    {
        $restaurant = Restaurant::withCount('despachos')->find($id);
        if (!$restaurant) return $this->notFound();

        return $this->success($this->format($restaurant, true));
    }

    // PUT /admin/restaurants/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $restaurant = Restaurant::find($id);
        if (!$restaurant) return $this->notFound();

        $data = $request->validate([
            'name'        => 'sometimes|string|max:150',
            'webhook_url' => 'nullable|url',
            'activo'      => 'sometimes|boolean',
        ]);

        $restaurant->update($data);

        return $this->success($this->format($restaurant, true), 'Actualizado');
    }

    // POST /admin/restaurants/{id}/regenerate-key
    public function regenerateKey(int $id): JsonResponse
    {
        $restaurant = Restaurant::find($id);
        if (!$restaurant) return $this->notFound();

        $restaurant->update([
            'api_key'        => Restaurant::generateApiKey(),
            'webhook_secret' => Str::random(40),
        ]);

        return $this->success($this->format($restaurant, true), 'API key regenerada');
    }

    // DELETE /admin/restaurants/{id}
    public function destroy(int $id): JsonResponse
    {
        $restaurant = Restaurant::find($id);
        if (!$restaurant) return $this->notFound();

        $restaurant->delete();
        return $this->success(null, 'Restaurante eliminado');
    }

    private function format(Restaurant $r, bool $withSecrets = false): array
    {
        $data = [
            'id'             => $r->id,
            'name'           => $r->name,
            'slug'           => $r->slug,
            'webhook_url'    => $r->webhook_url,
            'activo'         => $r->activo,
            'total_despachos' => $r->despachos_count ?? 0,
            'created_at'     => $r->created_at?->toISOString(),
        ];

        if ($withSecrets) {
            $data['api_key']        = $r->api_key;
            $data['webhook_secret'] = $r->webhook_secret;
        }

        return $data;
    }
}
