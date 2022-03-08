<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Permission;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function attach(Request $request, Event $event)
    {
        $this->authorize(Permission::ADD_SUPPLIER->value, $event);

        $validated = $request->validate([
            'value' => ['required', 'numeric'],
            'status' => [
                'required',
                Rule::in(['pending', 'hired']),
            ],
            'supplier' => [
                'required',
                Rule::exists('App\Models\Supplier', 'id')->whereIn('category_id', $event->categories->pluck('id'))
            ],
        ]);

        if (!$event->suppliers->contains($validated['supplier'])) {
            $event->suppliers()->attach($validated['supplier'], $request->except('supplier'));
        }

        return redirect()->route('events.view', ['event' => $event]);
    }

    public function detach(Event $event, Supplier $supplier)
    {
        $this->authorize(Permission::REMOVE_SUPPLIER->value, $event);

        $event->suppliers()->detach($supplier);

        return redirect()->route('events.view', ['event' => $event]);
    }

    public function update(Request $request, Event $event, Supplier $supplier)
    {
        $this->authorize(Permission::EDIT_SUPPLIER->value, $event);

        $request->validate([
            'value' => ['required', 'numeric'],
            'contract.*' => ['sometimes', 'required_if:status,hired', 'file'],
            'status' => [
                'required',
                Rule::in(['pending', 'hired']),
            ],
        ]);

        $event->suppliers()->updateExistingPivot($supplier, $request->only(['value', 'status']));

        if ($request->file('contract')) {
            foreach ($request->file('contract') as $file) {
                $file->store('contracts');
            }
        }

        return redirect()->route('events.view', ['event' => $event]);
    }
}
