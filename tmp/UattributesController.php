<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Uattribute\BulkDestroyUattribute;
use App\Http\Requests\Admin\Uattribute\DestroyUattribute;
use App\Http\Requests\Admin\Uattribute\IndexUattribute;
use App\Http\Requests\Admin\Uattribute\StoreUattribute;
use App\Http\Requests\Admin\Uattribute\UpdateUattribute;
use App\Models\Uattribute;
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

class UattributesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @param IndexUattribute $request
     * @return array|Factory|View
     */
    public function index(IndexUattribute $request)
    {
        // create and AdminListing instance for a specific model and
        $data = AdminListing::create(Uattribute::class)->processRequestAndGet(
            // pass the request with params
            $request,

            // set columns to query
            ['id', 'name'],

            // set columns to searchIn
            ['id', 'name', 'attributes']
        );

        if ($request->ajax()) {
            if ($request->has('bulk')) {
                return [
                    'bulkItems' => $data->pluck('id')
                ];
            }
            return ['data' => $data];
        }

        return view('admin.uattribute.index', ['data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @throws AuthorizationException
     * @return Factory|View
     */
    public function create()
    {
        $this->authorize('admin.uattribute.create');

        return view('admin.uattribute.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreUattribute $request
     * @return array|RedirectResponse|Redirector
     */
    public function store(StoreUattribute $request)
    {
        // Sanitize input
        $sanitized = $request->getSanitized();

        // Store the Uattribute
        $uattribute = Uattribute::create($sanitized);

        if ($request->ajax()) {
            return ['redirect' => url('admin/uattributes'), 'message' => trans('brackets/admin-ui::admin.operation.succeeded')];
        }

        return redirect('admin/uattributes');
    }

    /**
     * Display the specified resource.
     *
     * @param Uattribute $uattribute
     * @throws AuthorizationException
     * @return void
     */
    public function show(Uattribute $uattribute)
    {
        $this->authorize('admin.uattribute.show', $uattribute);

        // TODO your code goes here
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Uattribute $uattribute
     * @throws AuthorizationException
     * @return Factory|View
     */
    public function edit(Uattribute $uattribute)
    {
        $this->authorize('admin.uattribute.edit', $uattribute);


        return view('admin.uattribute.edit', [
            'uattribute' => $uattribute,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUattribute $request
     * @param Uattribute $uattribute
     * @return array|RedirectResponse|Redirector
     */
    public function update(UpdateUattribute $request, Uattribute $uattribute)
    {
        // Sanitize input
        $sanitized = $request->getSanitized();

        // Update changed values Uattribute
        $uattribute->update($sanitized);

        if ($request->ajax()) {
            return [
                'redirect' => url('admin/uattributes'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return redirect('admin/uattributes');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DestroyUattribute $request
     * @param Uattribute $uattribute
     * @throws Exception
     * @return ResponseFactory|RedirectResponse|Response
     */
    public function destroy(DestroyUattribute $request, Uattribute $uattribute)
    {
        $uattribute->delete();

        if ($request->ajax()) {
            return response(['message' => trans('brackets/admin-ui::admin.operation.succeeded')]);
        }

        return redirect()->back();
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param BulkDestroyUattribute $request
     * @throws Exception
     * @return Response|bool
     */
    public function bulkDestroy(BulkDestroyUattribute $request) : Response
    {
        DB::transaction(static function () use ($request) {
            collect($request->data['ids'])
                ->chunk(1000)
                ->each(static function ($bulkChunk) {
                    Uattribute::whereIn('id', $bulkChunk)->delete();

                    // TODO your code goes here
                });
        });

        return response(['message' => trans('brackets/admin-ui::admin.operation.succeeded')]);
    }
}
