<?php
namespace Nexus\Modules\Http;

use Nexus\Modules\Http\Request;
use Nexus\Modules\Http\Response;

/**
 * Base controller for RESTful resource operations
 * Provides common CRUD methods that can be overridden in child controllers
 */
abstract class ResourceController extends Controller {
    /**
     * The model class name
     *
     * @var string
     */
    protected $model;

    /**
     * Validation rules for store operation
     *
     * @var array
     */
    protected $storeRules = [];

    /**
     * Validation rules for update operation
     *
     * @var array
     */
    protected $updateRules = [];

    /**
     * Resource name for messages
     *
     * @var string
     */
    protected $resourceName = 'resource';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {
        $query = $this->model::query();

        // Apply any custom query modifications
        $query = $this->modifyIndexQuery($query, $request);

        $resources = $query->get();

        if ($request->expectsJson()) {
            return $this->json([
                'success' => true,
                'data' => $resources,
                'count' => $resources->count()
            ]);
        }

        return $this->view($this->getViewPath('index'), [
            $this->getResourceNamePlural() => $resources,
            'layout' => 'layouts/app'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request) {
        return $this->view($this->getViewPath('create'), [
            'layout' => 'layouts/app'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $data = $request->all();

        // Validate input
        if (!empty($this->storeRules)) {
            validate($data, $this->storeRules);
        }

        // Create resource
        $resource = $this->model::create($data);

        if ($request->expectsJson()) {
            return $this->json([
                'success' => true,
                'data' => $resource,
                'message' => ucfirst($this->resourceName) . ' created successfully'
            ], 201);
        }

        $this->redirect($this->getIndexRoute());
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id) {
        $resource = $this->findResource($id);

        if ($request->expectsJson()) {
            return $this->json([
                'success' => true,
                'data' => $resource
            ]);
        }

        return $this->view($this->getViewPath('show'), [
            $this->resourceName => $resource,
            'layout' => 'layouts/app'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id) {
        $resource = $this->findResource($id);

        return $this->view($this->getViewPath('edit'), [
            $this->resourceName => $resource,
            'layout' => 'layouts/app'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {
        $resource = $this->findResource($id);
        $data = $request->all();

        // Validate input
        if (!empty($this->updateRules)) {
            validate($data, $this->updateRules);
        }

        // Update resource
        $resource->update($id, $data);

        if ($request->expectsJson()) {
            return $this->json([
                'success' => true,
                'data' => $resource,
                'message' => ucfirst($this->resourceName) . ' updated successfully'
            ]);
        }

        $this->redirect($this->getIndexRoute());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id) {
        $resource = $this->findResource($id);
        $resource->delete($id);

        if ($request->expectsJson()) {
            return $this->json([
                'success' => true,
                'message' => ucfirst($this->resourceName) . ' deleted successfully'
            ]);
        }

        $this->redirect($this->getIndexRoute());
    }

    /**
     * Find a resource by ID or throw 404
     */
    protected function findResource($id) {
        $resource = $this->model::find($id);

        if (!$resource) {
            abort(404, ucfirst($this->resourceName) . ' not found');
        }

        return $resource;
    }

    /**
     * Modify the index query before execution
     */
    protected function modifyIndexQuery($query, Request $request) {
        return $query;
    }

    /**
     * Get the view path for a given action
     */
    protected function getViewPath($action) {
        return $this->getResourceNamePlural() . '.' . $action;
    }

    /**
     * Get the plural form of the resource name
     */
    protected function getResourceNamePlural() {
        return $this->resourceName . 's';
    }

    /**
     * Get the index route path
     */
    protected function getIndexRoute() {
        return '/' . $this->getResourceNamePlural();
    }
}