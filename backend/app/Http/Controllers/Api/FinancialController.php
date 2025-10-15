<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Income;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controlador Financiero
 * 
 * Gestiona ingresos, egresos y reportes financieros
 */
class FinancialController extends Controller
{
    /**
     * Listar ingresos con filtros
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function incomes(Request $request): JsonResponse
    {
        $query = Income::with([
            'campaign:id,name',
            'registeredBy:id,name'
        ]);

        // Filtro por campaña
        if ($request->has('campaign_id')) {
            $query->byCampaign($request->campaign_id);
        }

        // Filtro por fuente
        if ($request->has('source')) {
            $query->bySource($request->source);
        }

        // Filtro por rango de fechas
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->inDateRange($request->start_date, $request->end_date);
        }

        // Ordenar por fecha de ingreso (más recientes primero)
        $query->orderBy('income_date', 'desc');

        $incomes = $query->paginate($request->get('per_page', 15));

        return response()->json($incomes, 200);
    }

    /**
     * Registrar un nuevo ingreso
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function storeIncome(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'campaign_id' => 'nullable|exists:campaigns,id',
            'concept' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'source' => 'required|in:sterilization,donation,fundraising,other',
            'income_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
        ]);

        $income = Income::create([
            ...$validated,
            'registered_by' => Auth::id(),
        ]);

        $income->load(['campaign:id,name', 'registeredBy:id,name']);

        return response()->json([
            'message' => 'Ingreso registrado exitosamente',
            'income' => $income,
        ], 201);
    }

    /**
     * Actualizar un ingreso
     * 
     * @param Request $request
     * @param Income $income
     * @return JsonResponse
     */
    public function updateIncome(Request $request, Income $income): JsonResponse
    {
        $validated = $request->validate([
            'campaign_id' => 'nullable|exists:campaigns,id',
            'concept' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'source' => 'sometimes|required|in:sterilization,donation,fundraising,other',
            'income_date' => 'sometimes|required|date',
            'reference_number' => 'nullable|string|max:255',
        ]);

        $income->update($validated);
        $income->load(['campaign:id,name', 'registeredBy:id,name']);

        return response()->json([
            'message' => 'Ingreso actualizado exitosamente',
            'income' => $income,
        ], 200);
    }

    /**
     * Eliminar un ingreso (soft delete)
     * 
     * @param Income $income
     * @return JsonResponse
     */
    public function destroyIncome(Income $income): JsonResponse
    {
        $income->delete();

        return response()->json([
            'message' => 'Ingreso eliminado exitosamente',
        ], 200);
    }

    /**
     * Listar egresos con filtros
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function expenses(Request $request): JsonResponse
    {
        $query = Expense::with([
            'campaign:id,name',
            'registeredBy:id,name'
        ]);

        // Filtro por campaña
        if ($request->has('campaign_id')) {
            $query->byCampaign($request->campaign_id);
        }

        // Filtro por categoría
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        // Filtro por rango de fechas
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->inDateRange($request->start_date, $request->end_date);
        }

        // Ordenar por fecha de egreso (más recientes primero)
        $query->orderBy('expense_date', 'desc');

        $expenses = $query->paginate($request->get('per_page', 15));

        return response()->json($expenses, 200);
    }

    /**
     * Registrar un nuevo egreso
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function storeExpense(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'campaign_id' => 'nullable|exists:campaigns,id',
            'concept' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|in:medical,transportation,supplies,marketing,administrative,other',
            'expense_date' => 'required|date',
            'invoice_number' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
        ]);

        $expense = Expense::create([
            ...$validated,
            'registered_by' => Auth::id(),
        ]);

        $expense->load(['campaign:id,name', 'registeredBy:id,name']);

        return response()->json([
            'message' => 'Egreso registrado exitosamente',
            'expense' => $expense,
        ], 201);
    }

    /**
     * Actualizar un egreso
     * 
     * @param Request $request
     * @param Expense $expense
     * @return JsonResponse
     */
    public function updateExpense(Request $request, Expense $expense): JsonResponse
    {
        $validated = $request->validate([
            'campaign_id' => 'nullable|exists:campaigns,id',
            'concept' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'category' => 'sometimes|required|in:medical,transportation,supplies,marketing,administrative,other',
            'expense_date' => 'sometimes|required|date',
            'invoice_number' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
        ]);

        $expense->update($validated);
        $expense->load(['campaign:id,name', 'registeredBy:id,name']);

        return response()->json([
            'message' => 'Egreso actualizado exitosamente',
            'expense' => $expense,
        ], 200);
    }

    /**
     * Eliminar un egreso (soft delete)
     * 
     * @param Expense $expense
     * @return JsonResponse
     */
    public function destroyExpense(Expense $expense): JsonResponse
    {
        $expense->delete();

        return response()->json([
            'message' => 'Egreso eliminado exitosamente',
        ], 200);
    }

    /**
     * Obtener resumen financiero general
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function summary(Request $request): JsonResponse
    {
        $incomeQuery = Income::query();
        $expenseQuery = Expense::query();

        // Filtro opcional por campaña
        if ($request->has('campaign_id')) {
            $incomeQuery->byCampaign($request->campaign_id);
            $expenseQuery->byCampaign($request->campaign_id);
        }

        // Filtro opcional por rango de fechas
        if ($request->has('start_date') && $request->has('end_date')) {
            $incomeQuery->inDateRange($request->start_date, $request->end_date);
            $expenseQuery->inDateRange($request->start_date, $request->end_date);
        }

        $totalIncome = $incomeQuery->sum('amount');
        $totalExpense = $expenseQuery->sum('amount');

        $summary = [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance' => $totalIncome - $totalExpense,
            'income_by_source' => Income::query()
                ->when($request->has('campaign_id'), fn($q) => $q->byCampaign($request->campaign_id))
                ->when($request->has('start_date') && $request->has('end_date'), 
                    fn($q) => $q->inDateRange($request->start_date, $request->end_date))
                ->select('source', DB::raw('SUM(amount) as total'))
                ->groupBy('source')
                ->get(),
            'expense_by_category' => Expense::query()
                ->when($request->has('campaign_id'), fn($q) => $q->byCampaign($request->campaign_id))
                ->when($request->has('start_date') && $request->has('end_date'), 
                    fn($q) => $q->inDateRange($request->start_date, $request->end_date))
                ->select('category', DB::raw('SUM(amount) as total'))
                ->groupBy('category')
                ->get(),
        ];

        return response()->json($summary, 200);
    }

    /**
     * Obtener reporte financiero detallado por campaña
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function reportByCampaign(Request $request): JsonResponse
    {
        $campaigns = \App\Models\Campaign::with(['incomes', 'expenses'])
            ->when($request->has('status'), fn($q) => $q->where('status', $request->status))
            ->get()
            ->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'status' => $campaign->status,
                    'total_income' => $campaign->getTotalIncome(),
                    'total_expense' => $campaign->getTotalExpense(),
                    'balance' => $campaign->getBalance(),
                ];
            });

        return response()->json($campaigns, 200);
    }
}
