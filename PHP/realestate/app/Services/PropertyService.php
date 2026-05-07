<?php

namespace App\Services;

use App\Models\PropertyModel;
use App\Models\PropertyImageModel;
use App\Helpers\Logger;

/**
 * PropertyService – Business logic layer for property operations.
 * Controllers stay thin; all heavy logic lives here.
 */
class PropertyService
{
    private PropertyModel      $properties;
    private PropertyImageModel $images;
    private ImageService       $imageService;

    public function __construct()
    {
        $this->properties   = new PropertyModel();
        $this->images       = new PropertyImageModel();
        $this->imageService = new ImageService();
    }

    // ------------------------------------------------------------------ //
    //  Create                                                              //
    // ------------------------------------------------------------------ //

    public function create(array $data, int $agentId, ?array $filesInput = null): array
    {
        // Generate slug
        $data['slug']     = $this->properties->generateSlug($data['title'], $data['city']);
        $data['agent_id'] = $agentId;
        $data['approval_status'] = 'pending'; // always pending until admin approves

        // Boolean checkboxes
        $data['parking']   = isset($data['parking'])   ? 1 : 0;
        $data['furnished'] = isset($data['furnished']) ? 1 : 0;

        try {
            $db = \App::db();
            $db->beginTransaction();

            $propertyId = $this->properties->create($data);

            // Handle image uploads
            if ($filesInput && !empty($filesInput['name'][0])) {
                $this->handleImageUploads((int) $propertyId, $filesInput);
            }

            $db->commit();

            Logger::activity('property_created', $agentId, ['property_id' => $propertyId]);

            return [true, 'Property submitted for admin approval.', (int) $propertyId];

        } catch (\Throwable $e) {
            $db->rollback();
            Logger::error('Property create failed: ' . $e->getMessage());
            return [false, 'Failed to create property. Please try again.', null];
        }
    }

    // ------------------------------------------------------------------ //
    //  Update                                                              //
    // ------------------------------------------------------------------ //

    public function update(int $id, array $data, int $agentId, ?array $filesInput = null): array
    {
        $property = $this->properties->find($id);

        if (!$property) {
            return [false, 'Property not found.'];
        }
        if ((int) $property['agent_id'] !== $agentId) {
            return [false, 'You do not own this property.'];
        }

        // Regenerate slug only if title or city changed
        if ($data['title'] !== $property['title'] || $data['city'] !== $property['city']) {
            $data['slug'] = $this->properties->generateSlug($data['title'], $data['city'], $id);
        }

        // Re-submit for approval after edit
        $data['approval_status'] = 'pending';
        $data['parking']         = isset($data['parking'])   ? 1 : 0;
        $data['furnished']       = isset($data['furnished']) ? 1 : 0;

        try {
            $db = \App::db();
            $db->beginTransaction();

            $this->properties->update($id, $data);

            if ($filesInput && !empty($filesInput['name'][0])) {
                $this->handleImageUploads($id, $filesInput);
            }

            $db->commit();

            Logger::activity('property_updated', $agentId, ['property_id' => $id]);
            return [true, 'Property updated and re-submitted for approval.'];

        } catch (\Throwable $e) {
            $db->rollback();
            Logger::error('Property update failed: ' . $e->getMessage());
            return [false, 'Update failed. Please try again.'];
        }
    }

    // ------------------------------------------------------------------ //
    //  Delete                                                              //
    // ------------------------------------------------------------------ //

    public function delete(int $id, int $agentId, bool $isAdmin = false): array
    {
        $property = $this->properties->find($id);
        if (!$property) return [false, 'Property not found.'];
        if (!$isAdmin && (int) $property['agent_id'] !== $agentId) {
            return [false, 'Access denied.'];
        }

        $this->properties->delete($id); // soft delete
        Logger::activity('property_deleted', $agentId, ['property_id' => $id]);
        return [true, 'Property deleted.'];
    }

    // ------------------------------------------------------------------ //
    //  Approval (Admin)                                                    //
    // ------------------------------------------------------------------ //

    public function approve(int $id, int $adminId): array
    {
        $ok = $this->properties->approve($id);
        if ($ok) Logger::activity('property_approved', $adminId, ['property_id' => $id]);
        return [$ok, $ok ? 'Property approved.' : 'Failed to approve.'];
    }

    public function reject(int $id, int $adminId, string $reason = ''): array
    {
        $ok = $this->properties->reject($id, $reason);
        if ($ok) Logger::activity('property_rejected', $adminId, ['property_id' => $id]);
        return [$ok, $ok ? 'Property rejected.' : 'Failed to reject.'];
    }

    // ------------------------------------------------------------------ //
    //  Image helpers                                                       //
    // ------------------------------------------------------------------ //

    private function handleImageUploads(int $propertyId, array $filesInput): void
    {
        $uploaded = $this->imageService->uploadMultiple($filesInput);
        $isFirst  = !$this->images->getPrimary($propertyId); // no primary yet?

        foreach ($uploaded as $i => $meta) {
            $imageId = $this->images->create([
                'property_id' => $propertyId,
                'file_name'   => $meta['file_name'],
                'original'    => $meta['original'],
                'thumbnail'   => $meta['thumbnail'],
                'webp'        => $meta['webp'],
                'is_primary'  => ($isFirst && $i === 0) ? 1 : 0,
                'sort_order'  => $i,
            ]);

            if ($isFirst && $i === 0) {
                $isFirst = false; // only first upload in this batch is primary
            }
        }
    }

    public function deleteImage(int $imageId, int $propertyId, int $userId, bool $isAdmin = false): array
    {
        if (!$isAdmin) {
            $prop = $this->properties->find($propertyId);
            if (!$prop || (int) $prop['agent_id'] !== $userId) {
                return [false, 'Access denied.'];
            }
        }

        $ok = $this->images->deleteOne($imageId, $propertyId);
        return [$ok, $ok ? 'Image deleted.' : 'Image not found.'];
    }

    public function setPrimaryImage(int $imageId, int $propertyId, int $agentId): array
    {
        $prop = $this->properties->find($propertyId);
        if (!$prop || (int) $prop['agent_id'] !== $agentId) {
            return [false, 'Access denied.'];
        }
        $this->images->setPrimary($imageId, $propertyId);
        return [true, 'Primary image updated.'];
    }

    // ------------------------------------------------------------------ //
    //  Analytics                                                           //
    // ------------------------------------------------------------------ //

    public function trackView(int $propertyId, ?int $userId, string $ip): void
    {
        // Throttle: one view per IP per property per hour
        $cacheKey = ROOT_PATH . '/storage/cache/view_' . md5($ip . '_' . $propertyId) . '.tmp';
        if (file_exists($cacheKey) && (time() - filemtime($cacheKey)) < 3600) return;

        touch($cacheKey);
        $this->properties->incrementViews($propertyId);

        \App::db()->insert('analytics', [
            'property_id' => $propertyId,
            'user_id'     => $userId,
            'session_id'  => session_id(),
            'ip'          => $ip,
            'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'referrer'    => $_SERVER['HTTP_REFERER'] ?? null,
        ]);

        // Track recently viewed
        if ($userId) {
            \App::db()->execute(
                "INSERT INTO recently_viewed (user_id, property_id, viewed_at)
                 VALUES (?, ?, NOW())
                 ON DUPLICATE KEY UPDATE viewed_at = NOW()",
                [$userId, $propertyId]
            );
        }
    }
}
