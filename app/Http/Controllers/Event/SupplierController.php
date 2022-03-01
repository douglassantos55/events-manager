<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function attach(Request $request, Event $event)
    {
        $this->authorize(Permission::ADD_SUPPLIER->value, $event);

        $validated = $request->validate([
            'value' => ['required', 'numeric'],
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
}
