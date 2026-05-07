<?php

namespace App\Controllers;

use App\Models\PropertyModel;
use App\Models\PropertyImageModel;
use App\Services\PropertyService;

/**
 * AgentPropertyController – Agent manages their own listings.
 *
 * GET  /agent/properties              → index()
 * GET  /agent/properties/create       → create()
 * POST /agent/properties/create       → store()
 * GET  /agent/properties/{id}/edit    → edit()
 * POST /agent/properties/{id}/edit    → update()
 * POST /agent/properties/{id}/delete  → destroy()
 * POST /agent/properties/image/delete → deleteImage()
 * POST /agent/properties/image/primary→ setPrimary()
 */
class AgentPropertyController extends BaseController
{
    private PropertyModel      $propertyModel;
    private PropertyImageModel $imageModel;
    private PropertyService    $service;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->propertyModel = new PropertyModel();
        $this->imageModel    = new PropertyImageModel();
        $this->service       = new PropertyService();
    }

    // ------------------------------------------------------------------ //
    //  Index                                                               //
    // ------------------------------------------------------------------ //

    public function index(): void
    {
        $this->requireRole('agent', 'admin');
        $agentId = (int) $this->authUser()['id'];
        $page    = max(1, (int) $this->request->get('page', 1));

        $result = $this->propertyModel->getByAgent($agentId, $page);

        $this->view('agent.properties.index', [
            'title'      => 'My Properties',
            'properties' => $result['data'],
            'pagination' => $result,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Create                                                              //
    // ------------------------------------------------------------------ //

    public function create(): void
    {
        $this->requireRole('agent', 'admin');
        $this->view('agent.properties.form', [
            'title'    => 'Add New Property',
            'property' => null,
            'images'   => [],
            'errors'   => $_SESSION['_validation_errors'] ?? [],
            'old'      => $_SESSION['_old_input'] ?? [],
        ]);
        unset($_SESSION['_validation_errors'], $_SESSION['_old_input']);
    }

    public function store(): void
    {
        $this->requireRole('agent', 'admin');
        $this->verifyCsrf();

        $data = $this->validate([
            'title'       => 'required|min:5|max:255',
            'description' => 'required|min:20',
            'type'        => 'required|in:apartment,house,commercial,land,villa,office',
            'status'      => 'required|in:sale,rent,pending',
            'price'       => 'required|numeric',
            'address'     => 'required|max:255',
            'city'        => 'required|max:100',
            'area'        => 'required|max:100',
            'division'    => 'required|max:100',
        ]);

        $agentId    = (int) $this->authUser()['id'];
        $filesInput = $_FILES['images'] ?? null;

        [$ok, $message, $propertyId] = $this->service->create($data, $agentId, $filesInput);

        if (!$ok) {
            $this->flash('error', $message);
            $this->redirect('/agent/properties/create');
            return;
        }

        $this->flash('success', $message);
        $this->redirect('/agent/properties');
    }

    // ------------------------------------------------------------------ //
    //  Edit                                                                //
    // ------------------------------------------------------------------ //

    public function edit(string $id): void
    {
        $this->requireRole('agent', 'admin');
        $agentId  = (int) $this->authUser()['id'];
        $property = $this->propertyModel->find((int) $id);

        if (!$property || (int) $property['agent_id'] !== $agentId) {
            $this->abort(403, 'Access denied.');
            return;
        }

        $images = $this->imageModel->getByProperty((int) $id);

        $this->view('agent.properties.form', [
            'title'    => 'Edit Property',
            'property' => $property,
            'images'   => $images,
            'errors'   => $_SESSION['_validation_errors'] ?? [],
            'old'      => $_SESSION['_old_input'] ?? [],
        ]);
        unset($_SESSION['_validation_errors'], $_SESSION['_old_input']);
    }

    public function update(string $id): void
    {
        $this->requireRole('agent', 'admin');
        $this->verifyCsrf();

        $data = $this->validate([
            'title'       => 'required|min:5|max:255',
            'description' => 'required|min:20',
            'type'        => 'required|in:apartment,house,commercial,land,villa,office',
            'status'      => 'required|in:sale,rent,pending',
            'price'       => 'required|numeric',
            'address'     => 'required|max:255',
            'city'        => 'required|max:100',
            'area'        => 'required|max:100',
            'division'    => 'required|max:100',
        ]);

        $agentId    = (int) $this->authUser()['id'];
        $filesInput = $_FILES['images'] ?? null;

        [$ok, $message] = $this->service->update((int) $id, $data, $agentId, $filesInput);

        $this->flash($ok ? 'success' : 'error', $message);
        $this->redirect($ok ? '/agent/properties' : "/agent/properties/$id/edit");
    }

    // ------------------------------------------------------------------ //
    //  Delete                                                              //
    // ------------------------------------------------------------------ //

    public function destroy(string $id): void
    {
        $this->requireRole('agent', 'admin');
        $this->verifyCsrf();

        $agentId = (int) $this->authUser()['id'];
        [$ok, $message] = $this->service->delete((int) $id, $agentId);

        $this->flash($ok ? 'success' : 'error', $message);
        $this->redirect('/agent/properties');
    }

    // ------------------------------------------------------------------ //
    //  Image management (AJAX)                                             //
    // ------------------------------------------------------------------ //

    public function deleteImage(): void
    {
        $this->requireRole('agent', 'admin');
        $this->verifyCsrf();

        $imageId    = (int) $this->request->post('image_id');
        $propertyId = (int) $this->request->post('property_id');
        $agentId    = (int) $this->authUser()['id'];

        [$ok, $message] = $this->service->deleteImage($imageId, $propertyId, $agentId);
        $this->json(['status' => $ok, 'message' => $message]);
    }

    public function setPrimaryImage(): void
    {
        $this->requireRole('agent', 'admin');
        $this->verifyCsrf();

        $imageId    = (int) $this->request->post('image_id');
        $propertyId = (int) $this->request->post('property_id');
        $agentId    = (int) $this->authUser()['id'];

        [$ok, $message] = $this->service->setPrimaryImage($imageId, $propertyId, $agentId);
        $this->json(['status' => $ok, 'message' => $message]);
    }
}
