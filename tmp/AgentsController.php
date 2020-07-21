<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Agent\BulkDestroyAgent;
use App\Http\Requests\Admin\Agent\DestroyAgent;
use App\Http\Requests\Admin\Agent\IndexAgent;
use App\Http\Requests\Admin\Agent\StoreAgent;
use App\Http\Requests\Admin\Agent\UpdateAgent;
use App\Models\Agent;
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

class AgentsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @param IndexAgent $request
     * @return array|Factory|View
     */
    public function index(IndexAgent $request)
    {
        // create and AdminListing instance for a specific model and
        $data = AdminListing::create(Agent::class)->processRequestAndGet(
            // pass the request with params
            $request,

            // set columns to query
            ['id', 'name', 'user_agent'],

            // set columns to searchIn
            ['id', 'name', 'user_agent']
        );

        if ($request->ajax()) {
            if ($request->has('bulk')) {
                return [
                    'bulkItems' => $data->pluck('id')
                ];
            }
            return ['data' => $data];
        }

        return view('admin.agent.index', ['data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @throws AuthorizationException
     * @return Factory|View
     */
    public function create()
    {
        $this->authorize('admin.agent.create');

        return view('admin.agent.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreAgent $request
     * @return array|RedirectResponse|Redirector
     */
    public function store(StoreAgent $request)
    {
        // Sanitize input
        $sanitized = $request->getSanitized();

        // Store the Agent
        $agent = Agent::create($sanitized);

        if ($request->ajax()) {
            return ['redirect' => url('admin/agents'), 'message' => trans('brackets/admin-ui::admin.operation.succeeded')];
        }

        return redirect('admin/agents');
    }

    /**
     * Display the specified resource.
     *
     * @param Agent $agent
     * @throws AuthorizationException
     * @return void
     */
    public function show(Agent $agent)
    {
        $this->authorize('admin.agent.show', $agent);

        // TODO your code goes here
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Agent $agent
     * @throws AuthorizationException
     * @return Factory|View
     */
    public function edit(Agent $agent)
    {
        $this->authorize('admin.agent.edit', $agent);


        return view('admin.agent.edit', [
            'agent' => $agent,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateAgent $request
     * @param Agent $agent
     * @return array|RedirectResponse|Redirector
     */
    public function update(UpdateAgent $request, Agent $agent)
    {
        // Sanitize input
        $sanitized = $request->getSanitized();

        // Update changed values Agent
        $agent->update($sanitized);

        if ($request->ajax()) {
            return [
                'redirect' => url('admin/agents'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return redirect('admin/agents');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DestroyAgent $request
     * @param Agent $agent
     * @throws Exception
     * @return ResponseFactory|RedirectResponse|Response
     */
    public function destroy(DestroyAgent $request, Agent $agent)
    {
        $agent->delete();

        if ($request->ajax()) {
            return response(['message' => trans('brackets/admin-ui::admin.operation.succeeded')]);
        }

        return redirect()->back();
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param BulkDestroyAgent $request
     * @throws Exception
     * @return Response|bool
     */
    public function bulkDestroy(BulkDestroyAgent $request) : Response
    {
        DB::transaction(static function () use ($request) {
            collect($request->data['ids'])
                ->chunk(1000)
                ->each(static function ($bulkChunk) {
                    Agent::whereIn('id', $bulkChunk)->delete();

                    // TODO your code goes here
                });
        });

        return response(['message' => trans('brackets/admin-ui::admin.operation.succeeded')]);
    }
}
