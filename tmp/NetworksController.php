<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Network\BulkDestroyNetwork;
use App\Http\Requests\Admin\Network\DestroyNetwork;
use App\Http\Requests\Admin\Network\IndexNetwork;
use App\Http\Requests\Admin\Network\StoreNetwork;
use App\Http\Requests\Admin\Network\UpdateNetwork;
use App\Models\Network;
use Brackets\AdminListing\Facades\AdminListing;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NetworksController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @param IndexNetwork $request
     * @return array|Factory|View
     */
    public function index(IndexNetwork $request)
    {
        // create and AdminListing instance for a specific model and
        $data = AdminListing::create(Network::class)->processRequestAndGet(
            // pass the request with params
            $request,

            // set columns to query
            ['id', 'name', 'networks'],

            // set columns to searchIn
            ['id', 'name', 'networks']
        );

        if ($request->ajax()) {
            if ($request->has('bulk')) {
                return [
                    'bulkItems' => $data->pluck('id')
                ];
            }
            return ['data' => $data];
        }

        return view('admin.network.index', ['data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @throws AuthorizationException
     * @return Factory|View
     */
    public function create()
    {
        $this->authorize('admin.network.create');

        return view('admin.network.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreNetwork $request
     * @return array|RedirectResponse|Redirector
     */
    public function store(StoreNetwork $request)
    {
        // Sanitize input
        $sanitized = $request->getSanitized();

        // Store the Network
        $network = Network::create($sanitized);

        if ($request->ajax()) {
            return ['redirect' => url('admin/networks'), 'message' => trans('brackets/admin-ui::admin.operation.succeeded')];
        }

        return redirect('admin/networks');
    }

    /**
     * Display the specified resource.
     *
     * @param Network $network
     * @throws AuthorizationException
     * @return void
     */
    public function show(Network $network)
    {
        $this->authorize('admin.network.show', $network);

        // TODO your code goes here
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Network $network
     * @throws AuthorizationException
     * @return Factory|View
     */
    public function edit(Network $network)
    {
        $this->authorize('admin.network.edit', $network);


        return view('admin.network.edit', [
            'network' => $network,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateNetwork $request
     * @param Network $network
     * @return array|RedirectResponse|Redirector
     */
    public function update(UpdateNetwork $request, Network $network)
    {
        // Sanitize input
        $sanitized = $request->getSanitized();

        // Update changed values Network
        $network->update($sanitized);

        if ($request->ajax()) {
            return [
                'redirect' => url('admin/networks'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return redirect('admin/networks');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DestroyNetwork $request
     * @param Network $network
     * @throws Exception
     * @return ResponseFactory|RedirectResponse|Response
     */
    public function destroy(DestroyNetwork $request, Network $network)
    {
        $network->delete();

        if ($request->ajax()) {
            return response(['message' => trans('brackets/admin-ui::admin.operation.succeeded')]);
        }

        return redirect()->back();
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param BulkDestroyNetwork $request
     * @throws Exception
     * @return Response|bool
     */
    public function bulkDestroy(BulkDestroyNetwork $request) : Response
    {
        DB::transaction(static function () use ($request) {
            collect($request->data['ids'])
                ->chunk(1000)
                ->each(static function ($bulkChunk) {
                    Network::whereIn('id', $bulkChunk)->delete();

                    // TODO your code goes here
                });
        });

        return response(['message' => trans('brackets/admin-ui::admin.operation.succeeded')]);
    }
}
