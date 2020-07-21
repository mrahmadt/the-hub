<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Policy\BulkDestroyPolicy;
use App\Http\Requests\Admin\Policy\DestroyPolicy;
use App\Http\Requests\Admin\Policy\IndexPolicy;
use App\Http\Requests\Admin\Policy\StorePolicy;
use App\Http\Requests\Admin\Policy\UpdatePolicy;
use App\Models\Policy;
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

class PoliciesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @param IndexPolicy $request
     * @return array|Factory|View
     */
    public function index(IndexPolicy $request)
    {
        // create and AdminListing instance for a specific model and
        $data = AdminListing::create(Policy::class)->processRequestAndGet(
            // pass the request with params
            $request,

            // set columns to query
            ['id', 'name'],

            // set columns to searchIn
            ['id', 'name']
        );

        if ($request->ajax()) {
            if ($request->has('bulk')) {
                return [
                    'bulkItems' => $data->pluck('id')
                ];
            }
            return ['data' => $data];
        }

        return view('admin.policy.index', ['data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @throws AuthorizationException
     * @return Factory|View
     */
    public function create()
    {
        $this->authorize('admin.policy.create');

        return view('admin.policy.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePolicy $request
     * @return array|RedirectResponse|Redirector
     */
    public function store(StorePolicy $request)
    {
        // Sanitize input
        $sanitized = $request->getSanitized();

        // Store the Policy
        $policy = Policy::create($sanitized);

        if ($request->ajax()) {
            return ['redirect' => url('admin/policies'), 'message' => trans('brackets/admin-ui::admin.operation.succeeded')];
        }

        return redirect('admin/policies');
    }

    /**
     * Display the specified resource.
     *
     * @param Policy $policy
     * @throws AuthorizationException
     * @return void
     */
    public function show(Policy $policy)
    {
        $this->authorize('admin.policy.show', $policy);

        // TODO your code goes here
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Policy $policy
     * @throws AuthorizationException
     * @return Factory|View
     */
    public function edit(Policy $policy)
    {
        $this->authorize('admin.policy.edit', $policy);


        return view('admin.policy.edit', [
            'policy' => $policy,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePolicy $request
     * @param Policy $policy
     * @return array|RedirectResponse|Redirector
     */
    public function update(UpdatePolicy $request, Policy $policy)
    {
        // Sanitize input
        $sanitized = $request->getSanitized();

        // Update changed values Policy
        $policy->update($sanitized);

        if ($request->ajax()) {
            return [
                'redirect' => url('admin/policies'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return redirect('admin/policies');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DestroyPolicy $request
     * @param Policy $policy
     * @throws Exception
     * @return ResponseFactory|RedirectResponse|Response
     */
    public function destroy(DestroyPolicy $request, Policy $policy)
    {
        $policy->delete();

        if ($request->ajax()) {
            return response(['message' => trans('brackets/admin-ui::admin.operation.succeeded')]);
        }

        return redirect()->back();
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param BulkDestroyPolicy $request
     * @throws Exception
     * @return Response|bool
     */
    public function bulkDestroy(BulkDestroyPolicy $request) : Response
    {
        DB::transaction(static function () use ($request) {
            collect($request->data['ids'])
                ->chunk(1000)
                ->each(static function ($bulkChunk) {
                    Policy::whereIn('id', $bulkChunk)->delete();

                    // TODO your code goes here
                });
        });

        return response(['message' => trans('brackets/admin-ui::admin.operation.succeeded')]);
    }


}
