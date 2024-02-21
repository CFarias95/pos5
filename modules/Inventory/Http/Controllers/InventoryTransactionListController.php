<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Inventory\Models\InventoryTransaction;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Http\Resources\InventoryTransactionListCollection;

class InventoryTransactionListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        //$transactions = InventoryTransaction::all();
        return view('inventory::inventory.transactions');
    }

    public function records()
    {
        $transactions = InventoryTransaction::all();
        
        return new InventoryTransactionListCollection($transactions->paginate(config('tenant.items_per_page')));

    }
    /*public function create()
    {
        return view('inventory_transactions.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            // Aquí irían tus reglas de validación
        ]);

        InventoryTransaction::create($validatedData);

        return redirect()->route('inventory_transactions.index')->with('success', 'Transaction created successfully.');
    }

    public function show(InventoryTransaction $inventoryTransaction)
    {
        return view('inventory_transactions.show', compact('inventoryTransaction'));
    }

    public function edit(InventoryTransaction $inventoryTransaction)
    {
        return view('inventory_transactions.edit', compact('inventoryTransaction'));
    }

    public function update(Request $request, InventoryTransaction $inventoryTransaction)
    {
        $validatedData = $request->validate([
            // Aquí irían tus reglas de validación
        ]);

        $inventoryTransaction->update($validatedData);

        return redirect()->route('inventory_transactions.index')->with('success', 'Transaction updated successfully.');
    }

    public function destroy(InventoryTransaction $inventoryTransaction)
    {
        $inventoryTransaction->delete();

        return redirect()->route('inventory_transactions.index')->with('success', 'Transaction deleted successfully.');
    }*/
}
