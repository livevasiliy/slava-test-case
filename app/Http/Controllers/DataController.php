<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Models\ImportRow;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $importedData = ImportRow::select('*')
            ->get()
            ->groupBy('date');

        // Преобразуем сгруппированные данные в массив
        /** @var Collection $formattedData */
        $formattedData = $importedData->map(function ($items, $date) {
            return [
                'date' => $date,
                'items' => DataResource::collection($items),
            ];
        })->values();

        // Реализуем пагинацию вручную
        $perPage = $request->input('per_page', 10);
        $currentPage = $request->input('page', 1);

        // Используем forPage для получения элементов для текущей страницы
        $paginatedData = $formattedData->forPage($currentPage, $perPage)->all();

        return response()->json($paginatedData);
    }
}
