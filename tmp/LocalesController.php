<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Locale\BulkDestroyLocale;
use App\Http\Requests\Admin\Locale\DestroyLocale;
use App\Http\Requests\Admin\Locale\IndexLocale;
use App\Http\Requests\Admin\Locale\StoreLocale;
use App\Http\Requests\Admin\Locale\UpdateLocale;
use App\Models\Locale;
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

class LocalesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @param IndexLocale $request
     * @return array|Factory|View
     */
    public function index(IndexLocale $request)
    {
        // create and AdminListing instance for a specific model and
        $data = AdminListing::create(Locale::class)->processRequestAndGet(
            // pass the request with params
            $request,

            // set columns to query
            ['id', 'name', 'locale'],

            // set columns to searchIn
            ['name']
        );

        if ($request->ajax()) {
            if ($request->has('bulk')) {
                return [
                    'bulkItems' => $data->pluck('id')
                ];
            }
            return ['data' => $data];
        }

        return view('admin.locale.index', ['data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @throws AuthorizationException
     * @return Factory|View
     */
    public function create()
    {
        $this->authorize('admin.locale.create');

        return view('admin.locale.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreLocale $request
     * @return array|RedirectResponse|Redirector
     */
    public function store(StoreLocale $request)
    {
        // Sanitize input
        $sanitized = $request->getSanitized();

        // Store the Locale
        $locale = Locale::create($sanitized);

        if ($request->ajax()) {
            return ['redirect' => url('admin/locales'), 'message' => trans('brackets/admin-ui::admin.operation.succeeded')];
        }

        return redirect('admin/locales');
    }

    /**
     * Display the specified resource.
     *
     * @param Locale $locale
     * @throws AuthorizationException
     * @return void
     */
    public function show(Locale $locale)
    {
        $this->authorize('admin.locale.show', $locale);

        // TODO your code goes here
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Locale $locale
     * @throws AuthorizationException
     * @return Factory|View
     */
    public function edit(Locale $locale)
    {
        $this->authorize('admin.locale.edit', $locale);


        return view('admin.locale.edit', [
            'locale' => $locale,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateLocale $request
     * @param Locale $locale
     * @return array|RedirectResponse|Redirector
     */
    public function update(UpdateLocale $request, Locale $locale)
    {
        // Sanitize input
        $sanitized = $request->getSanitized();

        // Update changed values Locale
        $locale->update($sanitized);

        if ($request->ajax()) {
            return [
                'redirect' => url('admin/locales'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return redirect('admin/locales');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DestroyLocale $request
     * @param Locale $locale
     * @throws Exception
     * @return ResponseFactory|RedirectResponse|Response
     */
    public function destroy(DestroyLocale $request, Locale $locale)
    {
        $locale->delete();

        if ($request->ajax()) {
            return response(['message' => trans('brackets/admin-ui::admin.operation.succeeded')]);
        }

        return redirect()->back();
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param BulkDestroyLocale $request
     * @throws Exception
     * @return Response|bool
     */
    public function bulkDestroy(BulkDestroyLocale $request) : Response
    {
        DB::transaction(static function () use ($request) {
            collect($request->data['ids'])
                ->chunk(1000)
                ->each(static function ($bulkChunk) {
                    Locale::whereIn('id', $bulkChunk)->delete();

                    // TODO your code goes here
                });
        });

        return response(['message' => trans('brackets/admin-ui::admin.operation.succeeded')]);
    }
}
