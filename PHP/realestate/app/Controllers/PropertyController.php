<?php

namespace App\Controllers;

use App\Models\PropertyModel;
use App\Models\PropertyImageModel;
use App\Services\PropertyService;

/**
 * PropertyController – Public-facing property pages.
 *
 * GET  /properties          → index()   listing + filters
 * GET  /property/{slug}     → show()    single property detail
 * GET  /properties/search   → search()  AJAX live search
 */
class PropertyController extends BaseController
{
    private PropertyModel   $propertyModel;
    private PropertyService $service;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->propertyModel = new PropertyModel();
        $this->service       = new PropertyService();
    }

    // ------------------------------------------------------------------ //
    //  Listing page                                                        //
    // ------------------------------------------------------------------ //

    public function index(): void
    {
        $filters = [
            'type'      => $this->request->get('type'),
            'status'    => $this->request->get('status'),
            'city'      => $this->request->get('city'),
            'area'      => $this->request->get('area'),
            'min_price' => $this->request->get('min_price'),
            'max_price' => $this->request->get('max_price'),
            'bedrooms'  => $this->request->get('bedrooms'),
            'furnished' => $this->request->get('furnished'),
            'q'         => $this->request->get('q'),
            'sort'      => $this->request->get('sort', 'newest'),
        ];

        $page   = max(1, (int) $this->request->get('page', 1));
        $result = $this->propertyModel->getPublicListing($filters, $page);

        $this->view('property.index', [
            'title'      => 'Properties ' . ($filters['city'] ? 'in ' . ucfirst($filters['city']) : ''),
            'properties' => $result['data'],
            'pagination' => $result,
            'filters'    => $filters,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Single property                                                     //
    // ------------------------------------------------------------------ //

    public function show(string $slug): void
    {
        $property = $this->propertyModel->getBySlug($slug);

        if (!$property) {
            $this->abort(404, 'Property not found.');
            return;
        }

        // Track view
        $userId = $this->authUser()['id'] ?? null;
        $this->service->trackView((int) $property['id'], $userId, $this->request->ip());

        // Images
        $imageModel = new PropertyImageModel();
        $images     = $imageModel->getByProperty((int) $property['id']);

        // Similar
        $similar = $this->propertyModel->getSimilar(
            (int) $property['id'], $property['type'], $property['city']
        );

        $this->view('property.show', [
            'title'       => $property['title'] . ' – ' . $property['city'],
            'description' => $property['meta_description'] ?? mb_strimwidth(strip_tags($property['description']), 0, 160, '…'),
            'property'    => $property,
            'images'      => $images,
            'similar'     => $similar,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  AJAX live search                                                    //
    // ------------------------------------------------------------------ //

    public function search(): void
    {
        $q = $this->request->get('q', '');

        if (strlen($q) < 2) {
            $this->json(['results' => []]);
            return;
        }

        $rows = $this->db->select(
            "SELECT p.id, p.title, p.city, p.area, p.price, p.type, p.slug, pi.file_name AS thumb
               FROM properties p
               LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.is_primary = 1
              WHERE p.approval_status = 'approved' AND p.deleted_at IS NULL
                AND (p.title LIKE ? OR p.city LIKE ? OR p.area LIKE ? OR p.address LIKE ?)
              ORDER BY p.is_featured DESC, p.views DESC
              LIMIT 8",
            array_fill(0, 4, '%' . $q . '%')
        );

        $this->json(['results' => $rows]);
    }
}
