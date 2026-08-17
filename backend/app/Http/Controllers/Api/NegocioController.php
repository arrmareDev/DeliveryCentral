<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NegocioController extends Controller
{
    // GET /admin/negocios
    public function index(Request $request): JsonResponse
    {
        $query = Negocio::withCount('despachos');

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
            'data'  => collect($paginated->items())->map(fn($n) => $this->format($n)),
            'meta'  => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
            ],
        ]);
    }

    // POST /admin/negocios
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'slug'        => 'required|string|max:50|unique:negocios,slug|alpha_dash',
            'webhook_url' => 'nullable|url',
            'direccion'   => 'nullable|string|max:255',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
        ]);

        $negocio = Negocio::create([
            'name'           => $data['name'],
            'slug'           => $data['slug'],
            'api_key'        => Negocio::generateApiKey(),
            'webhook_url'    => $data['webhook_url'] ?? null,
            'webhook_secret' => Str::random(40),
            'direccion'      => $data['direccion'] ?? null,
            'lat'            => $data['lat'] ?? null,
            'lng'            => $data['lng'] ?? null,
            'activo'         => true,
        ]);

        return $this->created($this->format($negocio, true));
    }

    // GET /admin/negocios/{id}
    public function show(int $id): JsonResponse
    {
        $negocio = Negocio::withCount('despachos')->find($id);
        if (!$negocio) return $this->notFound();

        return $this->success($this->format($negocio, true));
    }

    // PUT /admin/negocios/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $negocio = Negocio::find($id);
        if (!$negocio) return $this->notFound();

        $data = $request->validate([
            'name'        => 'sometimes|string|max:150',
            'webhook_url' => 'nullable|url',
            'direccion'   => 'nullable|string|max:255',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
            'activo'      => 'sometimes|boolean',
        ]);

        $negocio->update($data);

        return $this->success($this->format($negocio, true), 'Actualizado');
    }

    // POST /admin/negocios/{id}/regenerate-key
    public function regenerateKey(int $id): JsonResponse
    {
        $negocio = Negocio::find($id);
        if (!$negocio) return $this->notFound();

        $negocio->update([
            'api_key'        => Negocio::generateApiKey(),
            'webhook_secret' => Str::random(40),
        ]);

        return $this->success($this->format($negocio, true), 'API key regenerada');
    }

    // DELETE /admin/negocios/{id}
    public function destroy(int $id): JsonResponse
    {
        $negocio = Negocio::find($id);
        if (!$negocio) return $this->notFound();

        $negocio->delete();
        return $this->success(null, 'Negocio eliminado');
    }

    private function format(Negocio $n, bool $withSecrets = false): array
    {
        $data = [
            'id'              => $n->id,
            'name'            => $n->name,
            'slug'            => $n->slug,
            'webhook_url'     => $n->webhook_url,
            'direccion'       => $n->direccion,
            'lat'             => $n->lat,
            'lng'             => $n->lng,
            'activo'          => $n->activo,
            'total_despachos' => $n->despachos_count ?? 0,
            'created_at'      => $n->created_at?->toISOString(),
        ];

        if ($withSecrets) {
            $data['api_key']        = $n->api_key;
            $data['webhook_secret'] = $n->webhook_secret;
        }

        return $data;
    }
}
