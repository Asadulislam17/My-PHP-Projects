<?php

namespace App\Models;

/**
 * PropertyModel – All property data operations.
 */
class PropertyModel extends BaseModel
{
    protected string $table      = 'properties';
    protected bool   $softDelete = true;
    protected bool   $timestamps = true;

    protected array $fillable = [
        'agent_id','title','slug','description','type','status',
        'approval_status','price','area_sqft','bedrooms','bathrooms',
        'floors','parking','furnished','address','city','area',
        'division','zip_code','latitude','longitude','is_featured',
        'featured_until','meta_title','meta_description',
        'youtube_url','virtual_tour_url',
    ];

    // ------------------------------------------------------------------ //
    //  Listings with joins                                                 //
    // ------------------------------------------------------------------ //

    /**
     * Approved public listing with agent info + primary image.
     */
    public function getPublicListing(array $filters = [], int $page = 1): array
    {
        $where  = ["p.approval_status = 'approved'", "p.deleted_at IS NULL"];
        $params = [];

        if (!empty($filters['type'])) {
            $where[]  = "p.type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['status'])) {
            $where[]  = "p.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['city'])) {
            $where[]  = "p.city LIKE ?";
            $params[] = '%' . $filters['city'] . '%';
        }
        if (!empty($filters['area'])) {
            $where[]  = "p.area LIKE ?";
            $params[] = '%' . $filters['area'] . '%';
        }
        if (!empty($filters['min_price'])) {
            $where[]  = "p.price >= ?";
            $params[] = (float) $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $where[]  = "p.price <= ?";
            $params[] = (float) $filters['max_price'];
        }
        if (!empty($filters['bedrooms'])) {
            $where[]  = "p.bedrooms >= ?";
            $params[] = (int) $filters['bedrooms'];
        }
        if (!empty($filters['furnished'])) {
            $where[]  = "p.furnished = 1";
        }
        if (!empty($filters['q'])) {
            $where[]  = "(p.title LIKE ? OR p.address LIKE ? OR p.city LIKE ? OR p.area LIKE ?)";
            $term     = '%' . $filters['q'] . '%';
            $params   = array_merge($params, [$term, $term, $term, $term]);
        }

        $orderMap = [
            'newest'   => 'p.created_at DESC',
            'oldest'   => 'p.created_at ASC',
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'popular'  => 'p.views DESC',
        ];
        $order = $orderMap[$filters['sort'] ?? 'newest'] ?? 'p.created_at DESC';

        // Featured first, then by chosen order
        $orderClause = "p.is_featured DESC, $order";

        $sql = "SELECT p.*,
                       u.name AS agent_name, u.phone AS agent_phone, u.avatar AS agent_avatar,
                       pi.file_name AS thumb
                  FROM properties p
                  JOIN users u ON u.id = p.agent_id
                  LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.is_primary = 1
                 WHERE " . implode(' AND ', $where) . "
                 ORDER BY $orderClause";

        return $this->paginate($sql, $params, $page);
    }

    /**
     * Single property with full agent info.
     */
    public function getBySlug(string $slug): ?array
    {
        return $this->db->selectOne(
            "SELECT p.*,
                    u.name AS agent_name, u.phone AS agent_phone,
                    u.email AS agent_email, u.avatar AS agent_avatar, u.bio AS agent_bio
               FROM properties p
               JOIN users u ON u.id = p.agent_id
              WHERE p.slug = ? AND p.approval_status = 'approved' AND p.deleted_at IS NULL",
            [$slug]
        );
    }

    /**
     * Agent's own properties (any approval status).
     */
    public function getByAgent(int $agentId, int $page = 1): array
    {
        $sql = "SELECT p.*, pi.file_name AS thumb
                  FROM properties p
                  LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.is_primary = 1
                 WHERE p.agent_id = ? AND p.deleted_at IS NULL
                 ORDER BY p.created_at DESC";
        return $this->paginate($sql, [$agentId], $page);
    }

    /**
     * Admin: all properties with filters.
     */
    public function adminList(array $filters = [], int $page = 1): array
    {
        $where  = ["p.deleted_at IS NULL"];
        $params = [];

        if (!empty($filters['approval_status'])) {
            $where[]  = "p.approval_status = ?";
            $params[] = $filters['approval_status'];
        }
        if (!empty($filters['type'])) {
            $where[]  = "p.type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['q'])) {
            $where[]  = "(p.title LIKE ? OR u.name LIKE ?)";
            $term     = '%' . $filters['q'] . '%';
            $params   = array_merge($params, [$term, $term]);
        }

        $sql = "SELECT p.*, u.name AS agent_name, pi.file_name AS thumb
                  FROM properties p
                  JOIN users u ON u.id = p.agent_id
                  LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.is_primary = 1
                 WHERE " . implode(' AND ', $where) . "
                 ORDER BY p.created_at DESC";
        return $this->paginate($sql, $params, $page);
    }

    // ------------------------------------------------------------------ //
    //  Slug                                                                //
    // ------------------------------------------------------------------ //

    public function generateSlug(string $title, string $city, ?int $excludeId = null): string
    {
        $base = $this->slugify($city . '-' . $title);
        $slug = $base;
        $i    = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function slugify(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\w\s-]/u', '', $text);
        $text = preg_replace('/[\s_]+/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }

    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql    = "SELECT id FROM properties WHERE slug = ?";
        $params = [$slug];
        if ($excludeId) {
            $sql    .= " AND id != ?";
            $params[] = $excludeId;
        }
        return $this->db->selectOne($sql, $params) !== null;
    }

    // ------------------------------------------------------------------ //
    //  Approval                                                            //
    // ------------------------------------------------------------------ //

    public function approve(int $id): bool
    {
        return $this->db->update('properties', ['approval_status' => 'approved'], 'id = ?', [$id]) > 0;
    }

    public function reject(int $id, string $reason = ''): bool
    {
        return $this->db->update('properties',
            ['approval_status' => 'rejected'],
            'id = ?', [$id]
        ) > 0;
    }

    // ------------------------------------------------------------------ //
    //  View counter                                                        //
    // ------------------------------------------------------------------ //

    public function incrementViews(int $id): void
    {
        $this->db->execute("UPDATE properties SET views = views + 1 WHERE id = ?", [$id]);
    }

    // ------------------------------------------------------------------ //
    //  Featured                                                            //
    // ------------------------------------------------------------------ //

    public function setFeatured(int $id, int $days = 30): void
    {
        $this->db->update('properties', [
            'is_featured'   => 1,
            'featured_until' => date('Y-m-d H:i:s', strtotime("+$days days")),
        ], 'id = ?', [$id]);
    }

    public function expireFeatured(): void
    {
        $this->db->execute(
            "UPDATE properties SET is_featured = 0, featured_until = NULL
              WHERE is_featured = 1 AND featured_until < NOW()"
        );
    }

    // ------------------------------------------------------------------ //
    //  Similar properties                                                  //
    // ------------------------------------------------------------------ //

    public function getSimilar(int $id, string $type, string $city, int $limit = 4): array
    {
        return $this->db->select(
            "SELECT p.*, pi.file_name AS thumb
               FROM properties p
               LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.is_primary = 1
              WHERE p.id != ? AND p.type = ? AND p.city = ?
                AND p.approval_status = 'approved' AND p.deleted_at IS NULL
              ORDER BY p.is_featured DESC, p.views DESC
              LIMIT ?",
            [$id, $type, $city, $limit]
        );
    }

    // ------------------------------------------------------------------ //
    //  Stats                                                               //
    // ------------------------------------------------------------------ //

    public function getTrending(int $limit = 6): array
    {
        return $this->db->select(
            "SELECT p.*, pi.file_name AS thumb
               FROM properties p
               LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.is_primary = 1
              WHERE p.approval_status = 'approved' AND p.deleted_at IS NULL
              ORDER BY p.views DESC LIMIT ?",
            [$limit]
        );
    }

    public function getFeatured(int $limit = 6): array
    {
        return $this->db->select(
            "SELECT p.*, pi.file_name AS thumb
               FROM properties p
               LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.is_primary = 1
              WHERE p.is_featured = 1 AND p.approval_status = 'approved'
                AND p.deleted_at IS NULL
              ORDER BY p.created_at DESC LIMIT ?",
            [$limit]
        );
    }
}
