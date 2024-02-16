<?php
namespace AdminTool\Http\Controllers;
use AdminTool\Exports\AreasExport;
use AdminTool\Http\Controllers\Controller;
use AdminTool\Http\Requests\Areas\CreateRequest;
use AdminTool\Http\Resources\AreasResource;
use AdminTool\Models\Area;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use AdminTool\Models\Country;
use AdminTool\Exports\CountriesExport;
use AdminTool\Http\Controllers\GeneralController;
use AdminTool\Http\Requests\Areas\GetRegionsRequest;
use AdminTool\Services\SynchronizationService;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
class AreasController extends Controller
{
    /********* Initialize Permission based Middlewares  ***********/
    public function __construct()
    {
        $this->middleware(‘auth:tenant’);;
        $this->middleware(‘has_tenant’);
        $this->middleware(‘scope:admin_tool’);
    }
    /********* Getter For Pagination, Searching And Sorting  ***********/
    public function getter($req = null, $export = null)
    {
        if ($req != null) {
            $areas = Area::withAll()->WithParent();
            if ($req->trash) {
                if ($req->trash == ‘with’) {
                    $areas = $areas->withTrashed();
                }
                if ($req->trash == ‘only’) {
                    $areas = $areas->onlyTrashed();
                }
            }
            if ($req->column && $req->column != null && $req->search != null) {
                $areas = $areas->whereLike($req->column, $req->search);
            } else if ($req->search && $req->search != null) {
                $areas = $areas->whereLike([‘name’, ‘description’], $req->search);
            }
            if ($req->sort_field != null && $req->sort_type != null) {
                $areas = $areas->OrderBy($req->sort_field, $req->sort_type);
            } else {
                $areas = $areas->OrderBy(‘id’, ‘desc’);
            }
            if ($export != null) { // for export do not paginate
                return $areas->get();
            }
            $areas = $areas->paginate($req->perPage);
            return AreasResource::collection($areas)->response()->getData(true);
        }
        return AreasResource::collection(Area::withAll()->WithParent()->orderBy(‘id’, ‘desc’)->paginate(10))->response()->getData(true);
    }
    /********* FETCH ALL Areas ***********/
    /**
     * @return JsonResponse
     */
    public function index()
    {
        $areas = $this->getter();
        $response = generateResponse($areas, count($areas[‘data’]) > 0 ? true : false, ‘Areas Fetched Successfully’, null, ‘collection’);
        return response()->json($response, 200);
    }
    /********* Export  CSV And Excel  **********/
    /**
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request)
    {
        $areas = $this->getter($request, “export”);
        if (in_array($request->export, [‘csv,xlsx’])) {
            $extension = $request->export;
        } else {
            $extension = ‘xlsx’;
        }
        $filename = “areas.” . $extension;
        return Excel::download(new AreasExport($areas), $filename);
    }
    /********* FILTER Areas FOR Search ***********/
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function filter(Request $request)
    {
        $areas = $this->getter($request);
        $response = generateResponse($areas, count($areas[‘data’]) > 0 ? true : false, ‘Filter Areas Successfully’, null, ‘collection’);
        return response()->json($response, 200);
    }
    /********* ADD NEW Areas ***********/
    /**
     * @param CreateRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function store(CreateRequest $request)
    {
        try {
            DB::connection(‘admin_tool’)->beginTransaction();
            $area = Area::query()->create($request->all());
            $area = Area::find($area->id);
            $area = new AreasResource($area);
            $response = generateResponse($area, true, ‘Areas Created Successfully’, null, ‘object’);
            DB::connection(‘admin_tool’)->commit();
        } catch (\Exception $e) {
            DB::connection(‘admin_tool’)->rollback();
            $response = generateResponse(null, false, $e->getMessage(), null, ‘object’);
        }
        return response()->json($response, 200);
    }
    /********* View RECORD TO EDIT Or Display ***********/
    /**
     * @param $area
     * @return JsonResponse
     */
    public function show($area)
    {
        $area = Area::withAll()->WithParent()->find($area);
        if ($area) {
            $area = new AreasResource($area);
            $response = generateResponse($area, true, ‘Areas Fetched Successfully’, null, ‘object’);
        } else {
            $response = generateResponse(null, false, ‘Areas Not Found’, null, ‘object’);
        }
        return response()->json($response, 200);
    }
    /********* UPDATE Country ***********/
    /**
     * @param CreateRequest $request
     * @param $area
     * @return JsonResponse
     * @throws \Throwable
     */
    public function update(CreateRequest $request, $area)
    {
        $area = Area::find($area);
        try {
            DB::connection(‘admin_tool’)->beginTransaction();
            $area->update($request->all());
            $response = generateResponse(null, true, ‘Areas Updated Successfully’, null, ‘object’);
            DB::connection(‘admin_tool’)->commit();
        } catch (\Exception $e) {
            DB::connection(‘admin_tool’)->rollback();
            $response = generateResponse(null, false, $e->getMessage(), null, ‘object’);
        }
        return response()->json($response, 200);
    }
    /********* UPDATE Areas DistributorStatus***********/
    /**
     * @param Request $request
     * @param $area
     * @return JsonResponse
     */
    public function updateStatus(Request $request, $area)
    {
        $area = Area::find($area);
        $area->update([
            ‘is_active’ => $area->is_active == 1 ? 0 : 1
        ]);
        $response = generateResponse(null, true, ‘Areas DistributorStatus Updated Successfully’, null, ‘object’);
        return response()->json($response, 200);
    }
    /********* DELETE Areas ***********/
    /**
     * @param Request $request
     * @param $area
     * @return JsonResponse
     */
    public function destroy(Request $request, $area)
    {
        $area = Area::withTrashed()->find($area);
        if ($area->trashed()) {
            $response = generateResponse(null, false, ‘Areas is already in trash’, null, ‘object’);
        } else {
            $area->delete();
            $response = generateResponse(null, true, ‘Areas Deleted Successfully’, null, ‘object’);
        }
        return response()->json($response, 200);
    }
    /*********Permanently DELETE Areas ***********/
    /**
     * @param Request $request
     * @param $area
     * @return JsonResponse
     */
    public function destroyPermanently(Request $request, $area)
    {
        $area = Area::withTrashed()->find($area);
        if ($area) {
            if ($area->trashed()) {
                $area->forceDelete();
                $response = generateResponse(null, true, ‘Areas Deleted Successfully’, null, ‘object’);
            } else {
                $response = generateResponse(null, false, ‘Areas is not in trash to delete permanently’, null, ‘object’);
            }
        } else {
            $response = generateResponse(null, false, ‘Areas not found’, null, ‘object’);
        }
        return response()->json($response, 200);
    }
    /********* Restore Areas ***********/
    /**
     * @param Request $request
     * @param $area
     * @return JsonResponse
     */
    public function restore(Request $request, $area)
    {
        $area = Area::withTrashed()->find($area);
        if ($area->trashed()) {
            $area->restore();
            $response = generateResponse(null, true, ‘Areas Restored Successfully’, null, ‘object’);
        } else {
            $response = generateResponse(null, false, ‘Areas is not trashed’, null, ‘object’);
        }
        return response()->json($response, 200);
    }
    /********* Get All Countries For Adding Site ***********/
  public function getRegions(Request $request)
  {
    $data = AdminToolGeneralController::getRegions($request);
    return response()->json($data, 200);
  }
}