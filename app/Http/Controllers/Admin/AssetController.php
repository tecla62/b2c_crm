<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AssetAssignment;
use App\Models\AssetEmployee;
use App\Models\AssetItem;
use App\Models\AssetMovement;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetItem::with('createdBy');

        if ($s = $request->search) {
            $query->where(function ($q) use ($s) {
                $q->where('asset_code', 'like', "%{$s}%")
                    ->orWhere('name', 'like', "%{$s}%")
                    ->orWhere('category', 'like', "%{$s}%")
                    ->orWhere('location', 'like', "%{$s}%");
            });
        }

        foreach (['status', 'condition', 'category'] as $filter) {
            if ($value = $request->{$filter}) {
                $query->where($filter, $value);
            }
        }

        if ($request->stock === 'available') {
            $query->whereRaw('(total_quantity - issued_quantity - damaged_quantity - maintenance_quantity - lost_quantity) > 0');
        } elseif ($request->stock === 'low') {
            $query->whereRaw('(total_quantity - issued_quantity - damaged_quantity - maintenance_quantity - lost_quantity) > 0')
                ->whereRaw('(total_quantity - issued_quantity - damaged_quantity - maintenance_quantity - lost_quantity) <= minimum_quantity');
        } elseif ($request->stock === 'out') {
            $query->whereRaw('(total_quantity - issued_quantity - damaged_quantity - maintenance_quantity - lost_quantity) <= 0');
        }

        $summaryAssets = (clone $query)->get();
        $summary = [
            'items' => $summaryAssets->count(),
            'total' => $summaryAssets->sum('total_quantity'),
            'available' => $summaryAssets->sum(fn($asset) => $asset->available_quantity),
            'issued' => $summaryAssets->sum('issued_quantity'),
            'problem' => $summaryAssets->sum(fn($asset) => $asset->damaged_quantity + $asset->maintenance_quantity + $asset->lost_quantity),
        ];

        $assets = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = AssetItem::whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        return view('admin.assets.index', compact('assets', 'summary', 'categories'));
    }

    public function assignments(Request $request)
    {
        $query = AssetAssignment::with('assetItem', 'employee', 'customer')
            ->whereIn('status', ['issued', 'partial_returned']);

        if ($request->filled('employee_id')) {
            $query->where('asset_employee_id', $request->employee_id);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($s = $request->search) {
            $query->where(function ($q) use ($s) {
                $q->whereHas('assetItem', fn($aq) => $aq->where('name', 'like', "%{$s}%")->orWhere('asset_code', 'like', "%{$s}%"))
                    ->orWhereHas('employee', fn($eq) => $eq->where('name', 'like', "%{$s}%")->orWhere('mobile', 'like', "%{$s}%"))
                    ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$s}%")->orWhere('company_name', 'like', "%{$s}%"));
            });
        }

        $assignmentsSummary = $query->latest('issue_date')->latest()->get();
        $assignmentGroups = $assignmentsSummary
            ->groupBy(fn($assignment) => $assignment->assignment_group_code ?: 'assignment-' . $assignment->id)
            ->values();
        $page = LengthAwarePaginator::resolveCurrentPage();
        $assignments = new LengthAwarePaginator(
            $assignmentGroups->forPage($page, 15)->values(),
            $assignmentGroups->count(),
            15,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $summary = [
            'open' => $assignmentGroups->count(),
            'pending_qty' => $assignmentsSummary->sum(fn($assignment) => $assignment->balance_quantity),
            'employees' => AssetEmployee::where('status', 'active')->count(),
            'customers' => Customer::count(),
        ];

        $employees = AssetEmployee::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get(['id', 'name', 'company_name', 'mobile']);

        return view('admin.assets.assignments', compact('assignments', 'summary', 'employees', 'customers'));
    }

    public function createAssignment()
    {
        $assets = AssetItem::where('status', 'active')->orderBy('name')->get();
        $employees = AssetEmployee::where('status', 'active')->orderBy('name')->get();
        $customers = Customer::orderBy('name')->get(['id', 'name', 'company_name', 'mobile']);

        return view('admin.assets.assignment-create', compact('assets', 'employees', 'customers'));
    }

    public function showAssignmentGroup(string $assignmentGroup)
    {
        $assignments = AssetAssignment::with('assetItem', 'employee', 'customer')
            ->where('assignment_group_code', $assignmentGroup)
            ->orderBy('id')
            ->get();

        abort_if($assignments->isEmpty(), 404);

        return view('admin.assets.assignment-show', [
            'assignments' => $assignments,
            'assignment' => $assignments->first(),
            'groupReference' => $this->assignmentGroupReference($assignmentGroup),
            'pendingQuantity' => $assignments->sum(fn($item) => $item->balance_quantity),
        ]);
    }

    public function history(Request $request)
    {
        $query = AssetMovement::with('assetItem', 'employee', 'customer', 'createdBy');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('asset_item_id')) {
            $query->where('asset_item_id', $request->asset_item_id);
        }

        if ($s = $request->search) {
            $query->where(function ($q) use ($s) {
                $q->whereHas('assetItem', fn($aq) => $aq->where('name', 'like', "%{$s}%")->orWhere('asset_code', 'like', "%{$s}%"))
                    ->orWhereHas('employee', fn($eq) => $eq->where('name', 'like', "%{$s}%"))
                    ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$s}%")->orWhere('company_name', 'like', "%{$s}%"))
                    ->orWhere('reference_no', 'like', "%{$s}%");
            });
        }

        $movements = $query->latest('movement_date')->latest('id')->paginate(15)->withQueryString();
        $assets = AssetItem::orderBy('name')->get(['id', 'name', 'asset_code']);

        return view('admin.assets.history', compact('movements', 'assets'));
    }

    public function editData(AssetItem $assetItem)
    {
        return response()->json($assetItem);
    }

    public function create()
    {
        $categories = AssetItem::whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        return view('admin.assets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateAsset($request);
        $data['asset_code'] = $data['asset_code'] ?: $this->generateAssetCode();
        $data['created_by'] = auth()->id();

        $asset = AssetItem::create($data);
        ActivityLog::log('CREATE', 'Assets', "Created asset: {$asset->name} ({$asset->total_quantity} {$asset->unit})", $asset);

        return response()->json([
            'success' => true,
            'message' => "Asset created successfully. Asset ID: {$asset->asset_code}",
        ]);
    }

    public function storeEmployee(Request $request)
    {
        $data = $request->validate([
            'employee_code' => ['nullable', 'string', 'max:60', Rule::unique('asset_employees')->whereNull('deleted_at')],
            'name' => 'required|string|max:150',
            'mobile' => 'nullable|string|max:30',
            'designation' => 'nullable|string|max:120',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);
        $data['employee_code'] = $data['employee_code'] ?: $this->generateEmployeeCode();
        $data['created_by'] = auth()->id();

        $employee = AssetEmployee::create($data);
        ActivityLog::log('CREATE', 'Asset Employees', "Created asset employee: {$employee->name}", $employee);

        return response()->json([
            'success' => true,
            'message' => "Employee added successfully. Code: {$employee->employee_code}",
            'employee' => $employee,
        ]);
    }

    public function storeAssignmentBatch(Request $request)
    {
        $data = $request->validate([
            'asset_employee_id' => 'nullable|exists:asset_employees,id',
            'customer_id' => 'required|exists:customers,id',
            'issue_date' => 'required|date',
            'reference_no' => 'nullable|string|max:120',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.asset_item_id' => 'required|exists:asset_items,id',
            'items.*.quantity' => 'required|integer|min:1|max:999999',
        ]);

        try {
            DB::transaction(function () use ($data) {
                $assignmentGroupCode = (string) Str::uuid();
                foreach ($data['items'] as $item) {
                    $asset = AssetItem::whereKey($item['asset_item_id'])->lockForUpdate()->firstOrFail();
                    $quantity = (int) $item['quantity'];

                    if ($asset->available_quantity < $quantity) {
                        throw new \InvalidArgumentException("Not enough stock for {$asset->name}. Available: {$asset->available_quantity}");
                    }

                    $asset->increment('issued_quantity', $quantity);
                    $assignment = $asset->assignments()->create([
                        'assignment_group_code' => $assignmentGroupCode,
                        'asset_employee_id' => $data['asset_employee_id'] ?? null,
                        'customer_id' => $data['customer_id'],
                        'quantity' => $quantity,
                        'returned_quantity' => 0,
                        'issue_date' => $data['issue_date'],
                        'status' => 'issued',
                        'notes' => $data['notes'] ?? null,
                        'created_by' => auth()->id(),
                    ]);

                    $asset->movements()->create([
                        'type' => 'issue',
                        'quantity' => $quantity,
                        'movement_date' => $data['issue_date'],
                        'asset_employee_id' => $data['asset_employee_id'] ?? null,
                        'customer_id' => $data['customer_id'],
                        'asset_assignment_id' => $assignment->id,
                        'reference_no' => $data['reference_no'] ?? null,
                        'notes' => $data['notes'] ?? null,
                        'created_by' => auth()->id(),
                    ]);
                }
            });
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        ActivityLog::log('CREATE', 'Asset Assignments', 'Assigned multiple assets to customer site');

        return response()->json(['success' => true, 'message' => 'Assets assigned successfully.']);
    }

    public function returnAssignment(Request $request, AssetAssignment $assetAssignment)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1|max:999999',
            'return_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($assetAssignment, $data) {
                $assetAssignment->refresh();

                if (!in_array($assetAssignment->status, ['issued', 'partial_returned'], true)) {
                    throw new \InvalidArgumentException('This assignment is already fully returned.');
                }

                $quantity = (int) $data['quantity'];
                if ($assetAssignment->balance_quantity < $quantity) {
                    throw new \InvalidArgumentException('Return quantity is more than pending quantity.');
                }

                $asset = AssetItem::whereKey($assetAssignment->asset_item_id)->lockForUpdate()->firstOrFail();
                if ($asset->issued_quantity < $quantity) {
                    throw new \InvalidArgumentException('Issued stock is lower than return quantity.');
                }

                $asset->decrement('issued_quantity', $quantity);
                $assetAssignment->returned_quantity += $quantity;
                $assetAssignment->return_date = $data['return_date'];
                $assetAssignment->status = $assetAssignment->balance_quantity <= 0 ? 'returned' : 'partial_returned';
                $assetAssignment->save();

                $asset->movements()->create([
                    'type' => 'return',
                    'quantity' => $quantity,
                    'movement_date' => $data['return_date'],
                    'asset_employee_id' => $assetAssignment->asset_employee_id,
                    'customer_id' => $assetAssignment->customer_id,
                    'asset_assignment_id' => $assetAssignment->id,
                    'notes' => $data['notes'] ?? null,
                    'created_by' => auth()->id(),
                ]);
            });
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        ActivityLog::log('UPDATE', 'Asset Assignments', "Returned asset assignment #{$assetAssignment->id}");

        return response()->json(['success' => true, 'message' => 'Asset returned to godown successfully.']);
    }

    public function returnAssignmentGroup(Request $request, string $assignmentGroup)
    {
        $data = $request->validate([
            'return_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $assignments = AssetAssignment::where('assignment_group_code', $assignmentGroup)
            ->whereIn('status', ['issued', 'partial_returned'])
            ->get();

        if ($assignments->isEmpty()) {
            return response()->json(['message' => 'This assignment is already fully returned.'], 422);
        }

        try {
            DB::transaction(function () use ($assignments, $data) {
                foreach ($assignments as $assignment) {
                    $assignment->refresh();
                    $quantity = $assignment->balance_quantity;
                    if ($quantity <= 0) {
                        continue;
                    }

                    $asset = AssetItem::whereKey($assignment->asset_item_id)->lockForUpdate()->firstOrFail();
                    if ($asset->issued_quantity < $quantity) {
                        throw new \InvalidArgumentException('Issued stock is lower than return quantity.');
                    }

                    $asset->decrement('issued_quantity', $quantity);
                    $assignment->returned_quantity += $quantity;
                    $assignment->return_date = $data['return_date'];
                    $assignment->status = 'returned';
                    $assignment->save();

                    $asset->movements()->create([
                        'type' => 'return',
                        'quantity' => $quantity,
                        'movement_date' => $data['return_date'],
                        'asset_employee_id' => $assignment->asset_employee_id,
                        'customer_id' => $assignment->customer_id,
                        'asset_assignment_id' => $assignment->id,
                        'notes' => $data['notes'] ?? null,
                        'created_by' => auth()->id(),
                    ]);
                }
            });
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        ActivityLog::log('UPDATE', 'Asset Assignments', "Returned assignment group {$assignmentGroup}");

        return response()->json(['success' => true, 'message' => 'All assets in this assignment returned to godown successfully.']);
    }

    public function update(Request $request, AssetItem $assetItem)
    {
        $data = $this->validateAsset($request, $assetItem);
        $data['asset_code'] = $data['asset_code'] ?: $assetItem->asset_code;

        $reserved = $assetItem->issued_quantity + $assetItem->damaged_quantity + $assetItem->maintenance_quantity + $assetItem->lost_quantity;
        if ((int) $data['total_quantity'] < $reserved) {
            return response()->json([
                'message' => 'Total quantity cannot be less than issued/damaged/maintenance/lost quantity.',
            ], 422);
        }

        $assetItem->update($data);
        ActivityLog::log('UPDATE', 'Assets', "Updated asset: {$assetItem->name}", $assetItem);

        return response()->json(['success' => true, 'message' => 'Asset updated successfully.']);
    }

    public function movement(Request $request, AssetItem $assetItem)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['stock_in', 'issue', 'return', 'damaged', 'maintenance', 'maintenance_return', 'lost'])],
            'quantity' => 'required|integer|min:1|max:999999',
            'movement_date' => 'required|date',
            'asset_employee_id' => 'nullable|exists:asset_employees,id',
            'customer_id' => 'nullable|exists:customers,id',
            'asset_assignment_id' => 'nullable|exists:asset_assignments,id',
            'reference_no' => 'nullable|string|max:120',
            'notes' => 'nullable|string',
        ]);

        if ($data['type'] === 'issue') {
            $request->validate([
                'asset_employee_id' => 'required|exists:asset_employees,id',
                'customer_id' => 'required|exists:customers,id',
            ]);
        }

        if ($data['type'] === 'return') {
            $request->validate([
                'asset_assignment_id' => 'required|exists:asset_assignments,id',
            ]);
        }

        try {
            DB::transaction(function () use ($assetItem, &$data) {
                $assetItem->refresh();
                $assignment = $this->applyMovement($assetItem, $data);
                if ($assignment) {
                    $data['asset_assignment_id'] = $assignment->id;
                    $data['asset_employee_id'] = $assignment->asset_employee_id;
                    $data['customer_id'] = $assignment->customer_id;
                }

                $data['created_by'] = auth()->id();
                $assetItem->movements()->create($data);

                ActivityLog::log('UPDATE', 'Assets', "Asset movement {$data['type']}: {$assetItem->name}", $assetItem);
            });
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully.',
            'asset' => $assetItem->fresh(),
        ]);
    }

    public function destroy(AssetItem $assetItem)
    {
        ActivityLog::log('DELETE', 'Assets', "Deleted asset: {$assetItem->name}", $assetItem);
        $assetItem->delete();

        return response()->json(['success' => true, 'message' => 'Asset deleted successfully.']);
    }

    private function validateAsset(Request $request, ?AssetItem $assetItem = null): array
    {
        return $request->validate([
            'asset_code' => ['nullable', 'string', 'max:60', Rule::unique('asset_items')->ignore($assetItem?->id)],
            'name' => 'required|string|max:180',
            'category' => 'nullable|string|max:100',
            'unit' => 'required|string|max:30',
            'location' => 'nullable|string|max:150',
            'total_quantity' => 'required|integer|min:0|max:999999',
            'minimum_quantity' => 'required|integer|min:0|max:999999',
            'condition' => ['required', Rule::in(['new', 'good', 'repair', 'damaged'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'notes' => 'nullable|string',
        ]);
    }

    private function applyMovement(AssetItem $assetItem, array $data): ?AssetAssignment
    {
        $type = $data['type'];
        $quantity = (int) $data['quantity'];

        if ($type === 'stock_in') {
            $assetItem->increment('total_quantity', $quantity);
            return null;
        }

        return match ($type) {
            'issue' => $this->issueAsset($assetItem, $data),
            'return' => $this->returnAsset($assetItem, $data),
            'damaged' => $this->reserveAvailable($assetItem, 'damaged_quantity', $quantity, 'Not enough available stock to mark damaged.'),
            'maintenance' => $this->reserveAvailable($assetItem, 'maintenance_quantity', $quantity, 'Not enough available stock to send maintenance.'),
            'maintenance_return' => $this->releaseReserved($assetItem, 'maintenance_quantity', $quantity, 'Return quantity is more than maintenance quantity.'),
            'lost' => $this->reserveAvailable($assetItem, 'lost_quantity', $quantity, 'Not enough available stock to mark lost.'),
        };
    }

    private function issueAsset(AssetItem $assetItem, array $data): AssetAssignment
    {
        $quantity = (int) $data['quantity'];
        if ($assetItem->available_quantity < $quantity) {
            throw new \InvalidArgumentException('Not enough available stock to issue.');
        }

        $assetItem->increment('issued_quantity', $quantity);
        return $assetItem->assignments()->create([
            'asset_employee_id' => $data['asset_employee_id'],
            'customer_id' => $data['customer_id'],
            'quantity' => $quantity,
            'returned_quantity' => 0,
            'issue_date' => $data['movement_date'],
            'status' => 'issued',
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }

    private function returnAsset(AssetItem $assetItem, array $data): AssetAssignment
    {
        $quantity = (int) $data['quantity'];
        $assignment = AssetAssignment::where('asset_item_id', $assetItem->id)
            ->whereIn('status', ['issued', 'partial_returned'])
            ->find($data['asset_assignment_id']);

        if (!$assignment) {
            throw new \InvalidArgumentException('This issue record is already closed or does not belong to this asset.');
        }

        if ($assignment->balance_quantity < $quantity) {
            throw new \InvalidArgumentException('Return quantity is more than pending quantity for this employee.');
        }

        if ($assetItem->issued_quantity < $quantity) {
            throw new \InvalidArgumentException('Return quantity is more than issued quantity.');
        }

        $assetItem->decrement('issued_quantity', $quantity);
        $assignment->returned_quantity += $quantity;
        $assignment->return_date = $data['movement_date'];
        $assignment->status = $assignment->balance_quantity <= 0 ? 'returned' : 'partial_returned';
        $assignment->save();

        return $assignment;
    }

    private function reserveAvailable(AssetItem $assetItem, string $column, int $quantity, string $message): ?AssetAssignment
    {
        if ($assetItem->available_quantity < $quantity) {
            throw new \InvalidArgumentException($message);
        }

        $assetItem->increment($column, $quantity);

        return null;
    }

    private function releaseReserved(AssetItem $assetItem, string $column, int $quantity, string $message): ?AssetAssignment
    {
        if ($assetItem->{$column} < $quantity) {
            throw new \InvalidArgumentException($message);
        }

        $assetItem->decrement($column, $quantity);

        return null;
    }

    private function generateAssetCode(): string
    {
        $prefix = 'AST-' . now()->format('Ym') . '-';
        $last = AssetItem::withTrashed()->where('asset_code', 'like', $prefix . '%')
            ->orderByDesc('asset_code')
            ->value('asset_code');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function assignmentGroupReference(string $assignmentGroup): string
    {
        return 'ASN-' . strtoupper(substr(str_replace('-', '', $assignmentGroup), 0, 10));
    }

    private function generateEmployeeCode(): string
    {
        $prefix = 'EMP-' . now()->format('Y') . '-';
        $last = AssetEmployee::withTrashed()
            ->where('employee_code', 'like', $prefix . '%')
            ->orderByDesc('employee_code')
            ->value('employee_code');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
