<?php

namespace App\Models;

/**
 * PropertyImageModel – Images and videos for a property.
 */
class PropertyImageModel extends BaseModel
{
    protected string $table      = 'property_images';
    protected bool   $timestamps = false;

    protected array $fillable = [
        'property_id','file_name','original','thumbnail','webp','is_primary','sort_order',
    ];

    public function getByProperty(int $propertyId): array
    {
        return $this->db->select(
            "SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, sort_order ASC",
            [$propertyId]
        );
    }

    public function getPrimary(int $propertyId): ?array
    {
        return $this->db->selectOne(
            "SELECT * FROM property_images WHERE property_id = ? AND is_primary = 1",
            [$propertyId]
        );
    }

    public function setPrimary(int $imageId, int $propertyId): void
    {
        // Clear existing primary
        $this->db->execute(
            "UPDATE property_images SET is_primary = 0 WHERE property_id = ?",
            [$propertyId]
        );
        // Set new primary
        $this->db->execute(
            "UPDATE property_images SET is_primary = 1 WHERE id = ? AND property_id = ?",
            [$imageId, $propertyId]
        );
    }

    public function deleteByProperty(int $propertyId): void
    {
        $images = $this->getByProperty($propertyId);
        foreach ($images as $img) {
            $this->deleteFiles($img);
        }
        $this->db->delete('property_images', 'property_id = ?', [$propertyId]);
    }

    public function deleteOne(int $imageId, int $propertyId): bool
    {
        $img = $this->db->selectOne(
            "SELECT * FROM property_images WHERE id = ? AND property_id = ?",
            [$imageId, $propertyId]
        );
        if (!$img) return false;

        $this->deleteFiles($img);
        $this->db->delete('property_images', 'id = ?', [$imageId]);

        // If deleted image was primary, promote the first remaining image
        if ($img['is_primary']) {
            $next = $this->db->selectOne(
                "SELECT id FROM property_images WHERE property_id = ? ORDER BY sort_order ASC LIMIT 1",
                [$propertyId]
            );
            if ($next) {
                $this->setPrimary((int)$next['id'], $propertyId);
            }
        }
        return true;
    }

    private function deleteFiles(array $img): void
    {
        $basePath = ROOT_PATH . '/public/uploads/images/';
        foreach (['file_name', 'thumbnail', 'webp'] as $field) {
            if (!empty($img[$field]) && file_exists($basePath . $img[$field])) {
                unlink($basePath . $img[$field]);
            }
        }
    }
}
