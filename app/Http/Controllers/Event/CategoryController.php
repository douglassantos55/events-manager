<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Permission;
use App\Models\SupplierCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function attach(Request $request, Event $event)
    {
        $this->authorize(Permission::ADD_CATEGORY->value, $event);

        $validated = $request->validate([
            'category' => ['required', 'exists:App\Models\SupplierCategory,id'],
            'budget' => ['required', 'numeric'],
        ]);

        if (!$event->categories->contains($validated['category'])) {
            $event->categories()->attach($validated['category'], $request->only('budget'));
        }

        return redirect()->route('events.view', ['event' => $event]);
    }

    public function detach(Event $event, SupplierCategory $category)
    {
        $this->authorize(Permission::REMOVE_CATEGORY->value, $event);

        $event->categories()->detach($category);
        $event->suppliers()->detach($event->getSuppliersFor($category->id));

        return redirect()->route('events.view', ['event' => $event]);
    }
}
