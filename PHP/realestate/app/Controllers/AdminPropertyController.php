<?php

namespace App\Controllers;

use App\Models\PropertyModel;
use App\Models\PropertyImageModel;
use App\Services\PropertyService;

/**
 * AdminPropertyController – Admin manages all properties.
 *
 * GET  /admin/properties              → index()
 * GET  /admin/properties/{id}         → show()
 * POST /admin/properties/{id}/approve → approve()
 * POST /admin/properties/{id}/reject  → reject()
 * POST /admin/properties/{id}/delete  → destroy()
 * POST /admin/properties/{id}/feature → feature()
 */
class AdminPropertyController extends BaseController
{
    private PropertyModel   $propertyModel;
    private PropertyService $service;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->propertyModel = new PropertyModel();
        $this->service       = new PropertyService();
    }

    public function index(): void
    {
        $this->requireAdmin();

        $filters = [
            'approval_status' => $this->request->get('status', ''),
            'type'            => $this->request->get('type', ''),
            'q'               => $this->request->get('q', ''),
        ];

        $page   = max(1, (int) $this->request->get('page', 1));
        $result = $this->propertyModel->adminList($filters, $page);

        $this->view('admin.properties.index', [
            'title'      => 'Manage Properties',
            'properties' => $result['data'],
            'pagination' => $result,
            'filters'    => $filters,
        ]);
    }

    public function show(string $id): void
    {
        $this->requireAdmin();

        $property = $this->propertyModel->find((int) $id);
        if (!$property) $this->abort(404, 'Property not found.');

        $imageModel = new PropertyImageModel();
        $images     = $imageModel->getByProperty((int) $id);

        $this->view('admin.properties.show', [
            'title'    => 'Review: ' . $property['title'],
            'property' => $property,
            'images'   => $images,
        ]);
    }

    public function approve(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $adminId = (int) $this->authUser()['id'];
        [$ok, $message] = $this->service->approve((int) $id, $adminId);

        $this->flash($ok ? 'success' : 'error', $message);
        $this->redirect('/admin/properties');
    }

    public function reject(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $adminId = (int) $this->authUser()['id'];
        $reason  = $this->request->post('reason', '');
        [$ok, $message] = $this->service->reject((int) $id, $adminId, $reason);

        $this->flash($ok ? 'success' : 'error', $message);
        $this->redirect('/admin/properties');
    }

    public function destroy(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $adminId = (int) $this->authUser()['id'];
        [$ok, $message] = $this->service->delete((int) $id, $adminId, true);

        $this->flash($ok ? 'success' : 'error', $message);
        $this->redirect('/admin/properties');
    }

    public function feature(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $days = (int) $this->request->post('days', 30);
        $this->propertyModel->setFeatured((int) $id, $days);

        $this->flash('success', "Property featured for $days days.");
        $this->redirect('/admin/properties');
    }
}
