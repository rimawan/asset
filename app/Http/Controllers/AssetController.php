<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssetStore;
use App\Service\Asset;
use App\Service\DataMessage;
use App\Service\DatatableGenerator;
use App\Service\ImageService;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Log;
use JildertMiedema\LaravelPlupload\Facades\Plupload;
use Symfony\Component\HttpFoundation\Response;

class AssetController extends Controller
{
    use DataMessage;

    protected $assetService;
    /**
     * @var ImageService
     */
    private $imageService;

    /**
     * AssetController constructor.
     * @param $assetTypeService
     */
    public function __construct(Asset $assetService, ImageService $imageService)
    {
        $this->assetService = $assetService;
        $this->imageService = $imageService;
    }

    public function index(Request $request)
    {
        $sName = '';
        $sLocation = '';
        $sType = '';
        if ($request->has('submit')) {
            $sName = $request->input('name');
            $sLocation = $request->input('location_id');
            $sType = $request->input('asset_type_id');
        }
        $data['sName'] = $sName;
        $data['sLocation'] = $sLocation;
        $data['sType'] = $sType;
        $data['location'] = $this->assetService->location()->locationNestedSelect('location_id');
        $data['assetType'] = $this->assetService->assetType()->assetTypeSelect('asset_type_id');
        return view('assets.list', $data);
    }

    public function anyData(Request $request)
    {
//        Log::warning('s_name ==> ' . $request->input('s_name'));
//        Log::warning('s_location ==> ' . $request->input('s_location'));
//        Log::warning('s_type ==> ' . $request->input('s_type'));
        return $this->assetService->datatableData($request->all());
    }

    public function create()
    {
        $data['location'] = $this->assetService->location()->locationNestedSelect('location_id', null, false);
        $data['assetType'] = $this->assetService->assetType()->assetTypeSelect('asset_type_id', null, false);
        $data['assetFormUrl'] = url('/asset/asset-type-form/');

        return view('assets.add', $data);
    }

    public function store(AssetStore $request)
    {
        // var_dump($request->all()); exit;
        $this->assetService->store($request->except(['_token']));

        return redirect('/asset')->with($this->getMessage('store'));
    }

    public function detail($assetId)
    {
        $asset = $this->assetService->getAssetById($assetId);
        $data['asset'] = $asset;
        return view('assets.detail', $data);
    }

    public function edit(Request $request, $id)
    {
        return 'edit';
    }

    public function destroy($id)
    {
        return redirect('/asset')->with(['message' => 'Delete button is executed.']);
    }

    public function assetTypeForm($assetType)
    {
        $data['assetType'] = $assetType;
        $data['assetFormUrl'] = url('/asset/asset-type-form/');
        $data['performance'] = $this->assetService->assetPerformance()->assetPerformanceSelect('asset_performance_id');
        $data['condition'] = $this->assetService->assetCondition()->assetConditionSelect('asset_condition_id');
        echo \View::make('assets.asset-type-form', $data)->render();
    }

    public function uploadImage(Request $request) {
        return Plupload::receive('file', function ($file) {
            return [
                'path' => $this->imageService->saveImage($file),
                'status' => 'OK'
            ];
        });
    }

}
