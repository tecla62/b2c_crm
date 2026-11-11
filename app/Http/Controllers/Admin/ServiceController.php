<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::with('createdBy', 'category');

        if ($search = $request->search) {
            $query->where(fn($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"));
        }
        if ($status = $request->status) $query->where('status', $status);
        if ($category = $request->category) $query->where('category_id', $category);

        $services   = $query->latest()->paginate(10)->withQueryString();
        $categories = ServiceCategory::where('status','active')->orderBy('name')->get();

        return view('admin.services.index', compact('services', 'categories'));
    }

    public function editData(Service $service)
    {
        return response()->json($service);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:service_categories,id',
            'title'       => 'required|string|max:150|unique:services,title',
            'sub_title'   => 'nullable|string|max:255',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
        ]);

        $data['price'] = $data['price'] ?? 0;
        $data['created_by'] = auth()->id();
        $service = Service::create($data);
        ActivityLog::log('CREATE', 'Services', "Created service: {$service->title}", $service);

        return response()->json(['success' => true, 'message' => "Service \"{$service->title}\" created successfully.", 'service' => $service]);
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:service_categories,id',
            'title'       => ['required', 'string', 'max:150', Rule::unique('services')->ignore($service->id)],
            'sub_title'   => 'nullable|string|max:255',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
        ]);

        $service->update($data);
        ActivityLog::log('UPDATE', 'Services', "Updated service: {$service->title}", $service);

        return response()->json(['success' => true, 'message' => 'Service updated successfully.', 'service' => $service->fresh()]);
    }

    public function destroy(Service $service)
    {
        ActivityLog::log('DELETE', 'Services', "Deleted service: {$service->title}", $service);
        $service->delete();
        return response()->json(['success' => true, 'message' => 'Service deleted successfully.']);
    }

    public function toggleStatus(Service $service)
    {
        $status = $service->status === 'active' ? 'inactive' : 'active';
        $service->update(['status' => $status]);
        ActivityLog::log('STATUS_CHANGE', 'Services', "Changed service {$service->title} status to {$status}", $service);
        return response()->json(['success' => true, 'message' => "Service marked as {$status}.", 'status' => $status]);
    }

    // API endpoint — used by quotation/invoice to search & fetch services
    public function search(Request $request)
    {
        $services = Service::with('category')
            ->where('status', 'active')
            ->where('title', 'like', '%' . $request->q . '%')
            ->select('id', 'category_id', 'title', 'sub_title', 'price', 'description')
            ->limit(20)
            ->get();

        return response()->json($services);
    }
}
