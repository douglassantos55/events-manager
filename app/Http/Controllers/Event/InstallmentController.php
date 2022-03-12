<?php

namespace App\Http\Controllers\Event;

use App\Models\Permission;
use App\Models\EventSupplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class InstallmentController extends Controller
{
    public function create(Request $request, EventSupplier $supplier)
    {
        $this->authorize(Permission::ADD_INSTALLMENT->value, $supplier);

        $validated = $request->validate([
            'value' => ['required', 'numeric'],
            'status' => [
                'required',
                Rule::in([Installment::STATUS_PAID, Installment::STATUS_PENDING])
            ],
            'due_date' => ['required', 'date'],
        ]);

        $validated['due_date'] = Carbon::create($validated['due_date']);
        $installment = Installment::make($validated);

        if (!$supplier->canCreateInstallment($installment)) {
            return back()->withErrors([
                'value' => 'The sum of installments exceeds the value hired.',
            ]);
        }

        $supplier->installments()->save($installment);

        return redirect()->route('events.view', ['event' => $supplier->category->event]);
    }

    public function update(Request $request, Installment $installment)
    {
        $this->authorize(Permission::EDIT_INSTALLMENT->value, $installment);

        $validated = $request->validate([
            'value' => ['sometimes', 'required', 'numeric'],
            'status' => [
                'sometimes',
                'required',
                Rule::in([Installment::STATUS_PAID, Installment::STATUS_PENDING])
            ],
            'due_date' => ['sometimes', 'required', 'date'],
        ]);

        if (isset($validated['due_date'])) {
            $validated['due_date'] = Carbon::create($validated['due_date']);
        }

        $installment->update($validated);
        return redirect()->route('events.view', ['event' => $installment->supplier->category->event]);
    }

    public function delete(Installment $installment)
    {
        $this->authorize(Permission::REMOVE_INSTALLMENT->value, $installment);

        $installment->delete();

        return redirect()->route('events.view', ['event' => $installment->supplier->category->event]);
    }
}
