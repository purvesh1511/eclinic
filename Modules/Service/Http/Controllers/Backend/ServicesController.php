<?php

namespace Modules\Service\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Modules\Category\Models\Category;
use Modules\CustomField\Models\CustomField;
use Modules\CustomField\Models\CustomFieldGroup;
use Modules\Service\Http\Requests\ServiceRequest;
use Modules\Service\Models\Service;
use Modules\Service\Models\ServiceProviderServices;
use Modules\Service\Models\ServiceEmployee;
use Modules\Service\Models\ServiceGallery;
use Yajra\DataTables\DataTables;

class ServicesController extends Controller
{
    // use Authorizable;
    protected string $exportClass = '\App\Exports\ServicesExport';

    public function __construct()
    {
        // Page Title
        $this->module_title = 'service.title';
        // module name
        $this->module_name = 'services';

        // module icon
        $this->module_icon = 'fa-solid fa-clipboard-list';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
        $this->middleware(['permission:add_service'])->only('index');
        $this->middleware(['permission:edit_service'])->only('edit', 'update');
        $this->middleware(['permission:add_service'])->only('store');
        $this->middleware(['permission:delete_service'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $module_action = 'List';
        $columns = CustomFieldGroup::columnJsonValues(new Service());
        $customefield = CustomField::exportCustomFields(new Service());

        $categories = Category::whereNull('parent_id')->get();
        $subcategories = Category::whereNotNull('parent_id')->get();

        $export_import = true;
        $export_columns = [
            [
                'value' => 'name',
                'text' => ' Name',
            ],
            [
                'value' => 'default_price',
                'text' => 'Default Price',
            ],
            [
                'value' => 'duration_min',
                'text' => 'Duration Min',
            ],
            [
                'value' => 'category',
                'text' => 'Category',
            ],
            [
                'value' => 'service_providers',
                'text' => 'service_providers Count',
            ],
            [
                'value' => 'employees',
                'text' => 'Employee Count',
            ],
            [
                'value' => 'status',
                'text' => 'Status',

            ],
        ];
        $export_url = route('backend.services.export');

        return view('service::backend.services.index_datatable', compact('module_action', 'filter', 'categories', 'subcategories', 'columns', 'customefield', 'export_import', 'export_columns', 'export_url'));
    }

    /**
     * Select Options for Select 2 Request/ Response.
     *
     * @return Response
     */
    public function index_list(Request $request)
    {
        $employee_id = $request->employee_id;
        $category_id = $request->category_id;
        $service_provider_id = $request->service_provider_id;
        $data = Service::with('employee', 'service_providers');

        if (isset($employee_id)) {
            $data = $data->whereHas('employee', function ($q) use ($employee_id) {
                $q->where('employee_id', $employee_id);
            });
        }

        if (isset($category_id)) {
            $data->where('category_id', $category_id);
        }

        if (isset($service_provider_id)) {
            $data = $data->whereHas('service_providers', function ($q) use ($service_provider_id) {
                $q->where('service_provider_id', $service_provider_id);
            });
        }

        $data = $data->get();

        return response()->json($data);
    }

    /* category wise service list */
    public function categort_services_list(Request $request)
    {
        $category = $request->category_id;
        $categoryService = Service::where('category_id', $category)->get();

        return $categoryService;
    }

    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                $services = Service::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('messages.bulk_service_update');
                break;

            case 'delete':

                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }

                Service::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_service_delete');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => __('messages.bulk_update')]);
    }

    public function update_status(Request $request, Service $id)
    {
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('service_providers.status_update')]);
    }

    public function index_data(Datatables $datatable, Request $request)
    {
        $module_name = $this->module_name;
        $query = Service::query()
            ->with(['category', 'sub_category'])
            ->withCount(['service_providers', 'employee']);

        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }
        if (isset($filter)) {
            if (isset($filter['category_id'])) {
                $query->where('category_id', $filter['category_id']);
            }
        }

        if (isset($filter)) {
            if (isset($filter['sub_category_id'])) {
                $query->where('sub_category_id', $filter['sub_category_id']);
            }
        }

        $datatable = $datatable->eloquent($query)
            ->addColumn('check', function ($data) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$data->id.'"  name="datatable_ids[]" value="'.$data->id.'" onclick="dataTableRowCheck('.$data->id.')">';
            })
            ->addColumn('image', function ($data) {
                return '<img src='.$data->file_url." class='avatar avatar-50 rounded-pill'>";
            })
            ->addColumn('action', function ($data) use ($module_name) {
                return view('service::backend.services.action_column', compact('module_name', 'data'));
            })
            ->editColumn('employee_count', function ($data) {
                return "<b>$data->employee_count</b>  <button type='button' data-assign-module='".$data->id."' data-assign-target='#service-employee-assign-form' data-assign-event='employee_assign' class='btn btn-primary btn-sm rounded text-nowrap tbl-btn-add' data-bs-toggle='tooltip' title=". __('service.assign_staff_to_service')."><i class='ph ph-plus'></i></button>";
            })
            ->editColumn('default_price', function ($data) {
                return \Currency::format($data->default_price);
            })
            ->editColumn('duration_min', function ($data) {
                return $data->duration_min.' Min';
            })
            ->editColumn('status', function ($row) {
                $checked = '';
                if ($row->status) {
                    $checked = 'checked="checked"';
                }

                return '
                    <div class="form-check form-switch ">
                        <input type="checkbox" data-url="'.route('backend.services.update_status', $row->id).'" data-token="'.csrf_token().'" class="switch-status-change form-check-input"  id="datatable-row-'.$row->id.'"  name="status" value="'.$row->id.'" '.$checked.'>
                    </div>
                ';
            })
            ->editColumn('category_id', function ($data) {
                $category = isset($data->category->name) ? $data->category->name : '-';
                if (isset($data->sub_category->name)) {
                    $category = $category.' > '.$data->sub_category->name;
                }

                return $category;
            })
            ->filterColumn('category', function ($query, $keyword) {
                $query->whereHas('category', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', '%'.$keyword.'%');
                });
            })
            ->editColumn('updated_at', function ($data) {
                $module_name = $this->module_name;

                $diff = Carbon::now()->diffInHours($data->updated_at);

                if ($diff < 25) {
                    return $data->updated_at->diffForHumans();
                } else {
                    return $data->updated_at->isoFormat('llll');
                }
            })

            ->orderColumns(['id'], '-:column $1');
        if (! request()->is_single_service_provider) {
            $datatable->editColumn('service_providers_count', function ($data) {
                return "<b>$data->service_providers_count</b>  <button type='button' data-assign-module='".$data->id."' data-assign-target='#service-service-provider-assign-form' data-assign-event='service_provider_assign' class='btn btn-primary btn-sm rounded text-nowrap ' data-bs-toggle='tooltip' title=". __('service_providers.assign_service_provider_to_service')."><i class='ph ph-plus'></i></button>";
            });
        }

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatable, Service::CUSTOM_FIELD_MODEL, null);

        return $datatable->rawColumns(array_merge(['action', 'image', 'status', 'check', 'service_providers_count', 'employee_count'], $customFieldColumns))
            ->toJson();
    }

    public function index_list_data(Request $request)
    {
        $term = trim($request->q);

        $query_data = User::role('employee')->where(function ($q) {
            if (! empty($term)) {
                $q->orWhere('name', 'LIKE', "%$term%");
            }
        })->get();

        $data = [];

        foreach ($query_data as $row) {
            $data[] = [
                'id' => $row->id,
                'name' => $row->first_name.$row->last_name,
                'avatar' => $row->profile_image,
            ];
        }

        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $module_action = 'Create';

        return view('service::backend.services.create', compact('module_action'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(ServiceRequest $request)
    {
        $data = $request->except('file_url');

        $query = Service::create($data);

        if ($request->custom_fields_data) {
            $query->updateCustomFieldData(json_decode($request->custom_fields_data));
        }

        if ($request->hasFile('file_url')) {
            storeMediaFile($query, $request->file('file_url'));
        }

        $message = __('messages.create_form', ['form' => __('service.singular_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $module_action = 'Show';

        $data = Service::findOrFail($id);

        return view('service::backend.services.show', compact('module_action', "$data"));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $data = Service::findOrFail($id);

        if (! is_null($data)) {
            $custom_field_data = $data->withCustomFields();
            $data['custom_field_data'] = collect($custom_field_data->custom_fields_data)
                ->filter(function ($value) {
                    return $value !== null;
                })
                ->toArray();
        }

        return response()->json(['data' => $data, 'status' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(ServiceRequest $request, $id)
    {
        $data = Service::findOrFail($id);

        $request_data = $request->except('file_url');

        if ($data->default_price !== floatval($request_data['default_price'])) {
            ServiceProviderServices::where('service_id', $id)->update(['service_price' => $data->default_price]);
        }
        if ($data->duration_min !== $request_data['duration_min']) {
            ServiceProviderServices::where('service_id', $id)->update(['duration_min' => $data->duration_min]);
        }

        $data->update($request_data);

        if ($request->custom_fields_data) {
            $data->updateCustomFieldData(json_decode($request->custom_fields_data));
        }

        if ($request->file_url == null) {
            $data->clearMediaCollection('file_url');
        }

        if ($request->hasFile('file_url')) {
            storeMediaFile($data, $request->file('file_url'), 'file_url');
        }

        $message = __('messages.update_form', ['form' => __('service.singular_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        if (env('IS_DEMO')) {
            return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
        }

        $data = Service::findOrFail($id);

        $data->service_providers()->delete();

        $data->employee()->delete();

        $data->delete();

        $message = __('messages.delete_form', ['form' => __('service.singular_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    /**
     * List of trashed ertries
     * works if the softdelete is enabled.
     *
     * @return Response
     */
    public function trashed()
    {
        $module_name_singular = Str::singular($this->module_name);

        $module_action = 'Trash List';

        $data = Service::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate();

        return view('service::backend.services.trash', compact("$data", 'module_name_singular', 'module_action'));
    }

    /**
     * Restore a soft deleted entry.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function restore($id)
    {
        $data = Service::withTrashed()->find($id);
        $data->restore();

        $message = __('messages.service_data');

        return response()->json(['message' => $message, 'status' => true]);
    }

    public function assign_employee_list($id)
    {
        $service_user = ServiceEmployee::whereHas('employee', function($q) {
            return $q->whereNull('deleted_at');
        })->with('employee')->where('service_id', $id)->get();

        $service_user = $service_user->each(function ($data) {
            $data['name'] = $data->employee->first_name;
            $data['avatar'] = $data->employee->profile_image;

            return $data;
        });

        return response()->json(['status' => true, 'data' => $service_user]);
    }

    public function assign_employee_update($id, Request $request)
    {
        ServiceEmployee::where('service_id', $id)->delete();
        foreach ($request->employees as $key => $value) {
            ServiceEmployee::create([
                'service_id' => $id,
                'employee_id' => $value['employee_id'],
            ]);
        }

        return response()->json(['status' => true, 'message' => __('messages.service_staff_update')]);
    }

    // =========Service Staff Assign list and Assign update ======= //

    public function assign_service_provider_list($id)
    {
        $service_service_provider = ServiceProviderServices::with('serviceProvider')->where('service_id', $id)->get();
        $service_service_provider = $service_service_provider->each(function ($data) {
            $data['name'] = $data->serviceProvider->name;

            return $data;
        });

        return response()->json(['status' => true, 'data' => $service_service_provider]);
    }

    public function assign_service_provider_update($id, Request $request)
    {
        ServiceProviderServices::where('service_id', $id)->delete();
        foreach ($request->service_providers as $key => $value) {
            ServiceProviderServices::create([
                'service_id' => $id,
                'service_provider_id' => $value['service_provider_id'],
                'service_price' => $value['service_price'] ?? 0,
                'duration_min' => $value['duration_min'],
            ]);
        }

        return response()->json(['status' => true, 'message' => __('messages.service_service_provider_update')]);
    }

    public function getGalleryImages($id)
    {
        $service = Service::findOrFail($id);

        $data = ServiceGallery::where('service_id', $id)->get();

        return response()->json(['data' => $data, 'service' => $service, 'status' => true]);
    }

    public function uploadGalleryImages(Request $request, $id)
    {
        $gallery = collect($request->gallery, true);

        $images = ServiceGallery::where('service_id', $id)->whereNotIn('id', $gallery->pluck('id'))->get();

        foreach ($images as $key => $value) {
            $value->clearMediaCollection('gallery_images');
            $value->delete();
        }

        foreach ($gallery as $key => $value) {
            if ($value['id'] == 'null') {
                $serviceGallery = ServiceGallery::create([
                    'service_id' => $id,
                ]);

                $serviceGallery->addMedia($value['file'])->toMediaCollection('gallery_images');

                $serviceGallery->full_url = $serviceGallery->getFirstMediaUrl('gallery_images');
                $serviceGallery->save();
            }
        }

        return response()->json(['message' => __('messages.service_gallery_update'), 'status' => true]);
    }
}
