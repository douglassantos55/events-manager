<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Permission;
use App\Models\SupplierCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function attach(Request $request, Event $event, SupplierCategory $category)
    {
        $this->authorize(Permission::ADD_CATEGORY->value, $event);

        $validated = $request->validate([
            'budget' => ['required', 'numeric'],
        ]);

        if (!$event->categories->contains($category)) {
            $event->categories()->attach($category, $validated);
        }

        return redirect()->route('events.view', ['event' => $event]);
    }
}
