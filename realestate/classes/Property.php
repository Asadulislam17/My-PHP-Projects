<?php

require_once __DIR__ . '/../config/Database.php';

class Property {

    private Database $db;
    private static ?Property $instance = null;

    private function __construct() {
        $this->db = Database::getInstance();
    }

    public static function getInstance(): Property {
        if (self::$instance === null) {
            self::$instance = new Property();
        }
        return self::$instance;
    }


    // =========================================
    // ✅ CREATE PROPERTY
    // =========================================
    public function create(array $data, int $userId): array {

        $errors = $this->validateProperty($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $slug = $this->generateSlug($data['title']);

        try {
            $this->db->beginTransaction();

            $this->db->execute(
                "INSERT INTO properties 
                    (user_id, type_id, area_id, title, slug, description,
                     price, price_type, size_sqft, bedrooms, bathrooms,
                     floor_no, total_floors, address, latitude, longitude,
                     facing, year_built, parking, status)
                 VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')",
                [
                    $userId,
                    $data['type_id'],
                    $data['area_id'],
                    trim($data['title']),
                    $slug,
                    trim($data['description'] ?? ''),
                    $data['price'],
                    $data['price_type'],
                    $data['size_sqft'] ?? null,
                    $data['bedrooms']  ?? 0,
                    $data['bathrooms'] ?? 0,
                    $data['floor_no']      ?? null,
                    $data['total_floors']  ?? null,
                    trim($data['address']  ?? ''),
                    $data['latitude']  ?? null,
                    $data['longitude'] ?? null,
                    $data['facing']    ?? null,
                    $data['year_built'] ?? null,
                    isset($data['parking']) ? 1 : 0,
                ]
            );

            $propertyId = $this->db->lastInsertId();

            // Amenities save
            if (!empty($data['amenities']) && is_array($data['amenities'])) {
                $this->saveAmenities($propertyId, $data['amenities']);
            }

            // Images save
            if (!empty($data['images'])) {
                $this->saveImages($propertyId, $data['images']);
            }

            $this->db->commit();

            return [
                'success'     => true,
                'message'     => 'Property submit হয়েছে। Admin approval এর জন্য অপেক্ষা করুন।',
                'property_id' => $propertyId,
                'slug'        => $slug
            ];

        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'errors' => ['general' => 'Property add হয়নি। আবার try করুন।']];
        }
    }


    // =========================================
    // ✅ UPDATE PROPERTY
    // =========================================
    public function update(int $id, array $data, int $userId): array {

        $property = $this->getById($id);
        if (!$property) {
            return ['success' => false, 'message' => 'Property পাওয়া যায়নি।'];
        }

        // Owner or Admin check
        if ($property['user_id'] !== $userId && !$this->isAdmin($userId)) {
            return ['success' => false, 'message' => 'আপনার এই property edit করার permission নেই।'];
        }

        $errors = $this->validateProperty($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            $this->db->beginTransaction();

            $this->db->execute(
                "UPDATE properties SET
                    type_id = ?, area_id = ?, title = ?, description = ?,
                    price = ?, price_type = ?, size_sqft = ?,
                    bedrooms = ?, bathrooms = ?,
                    floor_no = ?, total_floors = ?,
                    address = ?, latitude = ?, longitude = ?,
                    facing = ?, year_built = ?, parking = ?,
                    status = 'pending',
                    updated_at = NOW()
                 WHERE id = ?",
                [
                    $data['type_id'],
                    $data['area_id'],
                    trim($data['title']),
                    trim($data['description'] ?? ''),
                    $data['price'],
                    $data['price_type'],
                    $data['size_sqft']    ?? null,
                    $data['bedrooms']     ?? 0,
                    $data['bathrooms']    ?? 0,
                    $data['floor_no']     ?? null,
                    $data['total_floors'] ?? null,
                    trim($data['address'] ?? ''),
                    $data['latitude']  ?? null,
                    $data['longitude'] ?? null,
                    $data['facing']    ?? null,
                    $data['year_built'] ?? null,
                    isset($data['parking']) ? 1 : 0,
                    $id
                ]
            );

            // Amenities update
            $this->db->execute("DELETE FROM property_amenities WHERE property_id = ?", [$id]);
            if (!empty($data['amenities'])) {
                $this->saveAmenities($id, $data['amenities']);
            }

            $this->db->commit();

            return ['success' => true, 'message' => 'Property update হয়েছে।'];

        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Update হয়নি। আবার try করুন।'];
        }
    }


    // =========================================
    // ✅ DELETE PROPERTY
    // =========================================
    public function delete(int $id, int $userId): array {

        $property = $this->getById($id);
        if (!$property) {
            return ['success' => false, 'message' => 'Property পাওয়া যায়নি।'];
        }

        if ($property['user_id'] !== $userId && !$this->isAdmin($userId)) {
            return ['success' => false, 'message' => 'Permission নেই।'];
        }

        // Images delete from disk
        $images = $this->db->query(
            "SELECT image_path, thumbnail FROM property_images WHERE property_id = ?",
            [$id]
        );
        foreach ($images as $img) {
            $this->deleteFile(UPLOAD_PATH . 'properties/' . $img['image_path']);
            if ($img['thumbnail']) {
                $this->deleteFile(UPLOAD_PATH . 'properties/thumbs/' . $img['thumbnail']);
            }
        }

        $this->db->execute("DELETE FROM properties WHERE id = ?", [$id]);

        return ['success' => true, 'message' => 'Property delete হয়েছে।'];
    }


    // =========================================
    // ✅ GET ALL (with Filters + Pagination)
    // =========================================
    public function getAll(array $filters = [], int $page = 1, int $perPage = 12): array {

        $where  = ["p.status = 'approved'"];
        $params = [];

        // Filter: Price Type
        if (!empty($filters['price_type'])) {
            $where[]  = "p.price_type = ?";
            $params[] = $filters['price_type'];
        }

        // Filter: Property Type
        if (!empty($filters['type'])) {
            $where[]  = "pt.slug = ?";
            $params[] = $filters['type'];
        }

        // Filter: Area
        if (!empty($filters['area_id'])) {
            $where[]  = "p.area_id = ?";
            $params[] = $filters['area_id'];
        }

        // Filter: Price Range
        if (!empty($filters['price_min'])) {
            $where[]  = "p.price >= ?";
            $params[] = $filters['price_min'];
        }
        if (!empty($filters['price_max'])) {
            $where[]  = "p.price <= ?";
            $params[] = $filters['price_max'];
        }

        // Filter: Bedrooms
        if (!empty($filters['bedrooms'])) {
            $where[]  = "p.bedrooms >= ?";
            $params[] = $filters['bedrooms'];
        }

        // Filter: Keyword Search
        if (!empty($filters['keyword'])) {
            $where[]  = "(p.title LIKE ? OR p.address LIKE ? OR p.description LIKE ?)";
            $kw       = '%' . $filters['keyword'] . '%';
            $params   = array_merge($params, [$kw, $kw, $kw]);
        }

        // Filter: Featured only
        if (!empty($filters['featured'])) {
            $where[] = "p.is_featured = 1";
        }

        $whereSQL = implode(' AND ', $where);

        // Sorting
        $sort = match($filters['sort'] ?? 'newest') {
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'popular'    => 'p.views_count DESC',
            default      => 'p.created_at DESC'
        };

        // Total Count
        $total = $this->db->queryOne(
            "SELECT COUNT(*) AS cnt
             FROM properties p
             JOIN property_types pt ON pt.id = p.type_id
             WHERE $whereSQL",
            $params
        );
        $totalCount = $total['cnt'] ?? 0;

        // Offset
        $offset = ($page - 1) * $perPage;

        // Main Query
        $rows = $this->db->query(
            "SELECT 
                p.*,
                pt.name  AS type_name,
                pt.slug  AS type_slug,
                a.name   AS area_name,
                d.name   AS district_name,
                u.name   AS agent_name,
                u.phone  AS agent_phone,
                u.avatar AS agent_avatar,
                (SELECT image_path FROM property_images 
                 WHERE property_id = p.id AND is_cover = 1 LIMIT 1) AS cover_image
             FROM properties p
             JOIN property_types pt ON pt.id = p.type_id
             JOIN areas a           ON a.id  = p.area_id
             JOIN districts d       ON d.id  = a.district_id
             JOIN users u           ON u.id  = p.user_id
             WHERE $whereSQL
             ORDER BY $sort
             LIMIT $perPage OFFSET $offset",
            $params
        );

        return [
            'data'        => $rows,
            'total'       => $totalCount,
            'per_page'    => $perPage,
            'current_page'=> $page,
            'last_page'   => (int) ceil($totalCount / $perPage),
        ];
    }


    // =========================================
    // ✅ GET SINGLE PROPERTY
    // =========================================
    public function getById(int $id): ?array {

        $property = $this->db->queryOne(
            "SELECT 
                p.*,
                pt.name  AS type_name,
                pt.slug  AS type_slug,
                a.name   AS area_name,
                d.name   AS district_name,
                dv.name  AS division_name,
                u.name   AS agent_name,
                u.phone  AS agent_phone,
                u.email  AS agent_email,
                u.avatar AS agent_avatar
             FROM properties p
             JOIN property_types pt ON pt.id  = p.type_id
             JOIN areas a           ON a.id   = p.area_id
             JOIN districts d       ON d.id   = a.district_id
             JOIN divisions dv      ON dv.id  = d.division_id
             JOIN users u           ON u.id   = p.user_id
             WHERE p.id = ?",
            [$id]
        );

        if (!$property) return null;

        // Images
        $property['images'] = $this->db->query(
            "SELECT * FROM property_images WHERE property_id = ? ORDER BY is_cover DESC, sort_order ASC",
            [$id]
        );

        // Amenities
        $property['amenities'] = $this->db->query(
            "SELECT am.* FROM amenities am
             JOIN property_amenities pa ON pa.amenity_id = am.id
             WHERE pa.property_id = ?",
            [$id]
        );

        // View count increment
        $this->incrementView($id);

        return $property;
    }

    // GET BY SLUG
    public function getBySlug(string $slug): ?array {
        $property = $this->db->queryOne(
            "SELECT id FROM properties WHERE slug = ?",
            [$slug]
        );
        return $property ? $this->getById($property['id']) : null;
    }


    // =========================================
    // ✅ ADMIN: APPROVE / REJECT
    // =========================================
    public function approve(int $id): array {
        $this->db->execute(
            "UPDATE properties SET status = 'approved', updated_at = NOW() WHERE id = ?",
            [$id]
        );
        return ['success' => true, 'message' => 'Property approve হয়েছে।'];
    }

    public function reject(int $id, string $reason = ''): array {
        $this->db->execute(
            "UPDATE properties SET status = 'rejected', updated_at = NOW() WHERE id = ?",
            [$id]
        );
        return ['success' => true, 'message' => 'Property reject হয়েছে।'];
    }

    public function toggleFeatured(int $id): array {
        $this->db->execute(
            "UPDATE properties SET is_featured = NOT is_featured WHERE id = ?",
            [$id]
        );
        return ['success' => true, 'message' => 'Featured status পরিবর্তন হয়েছে।'];
    }


    // =========================================
    // ✅ IMAGE UPLOAD
    // =========================================
    public function uploadImages(int $propertyId, array $files): array {

        $uploadDir  = UPLOAD_PATH . 'properties/';
        $thumbDir   = UPLOAD_PATH . 'properties/thumbs/';
        $uploaded   = [];
        $allowed    = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize    = 5 * 1024 * 1024; // 5MB

        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        if (!is_dir($thumbDir))  mkdir($thumbDir,  0755, true);

        // Normalize $_FILES array
        $fileList = $this->normalizeFiles($files);

        foreach ($fileList as $index => $file) {

            if ($file['error'] !== UPLOAD_ERR_OK) continue;
            if ($file['size']  > $maxSize)        continue;
            if (!in_array($file['type'], $allowed)) continue;

            // Unique filename
            $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('prop_') . '_' . time() . '.webp';
            $thumbname= 'thumb_' . $filename;

            $destPath  = $uploadDir . $filename;
            $thumbPath = $thumbDir  . $thumbname;

            // Convert & save as WebP
            $this->convertToWebP($file['tmp_name'], $destPath, 85);

            // Generate thumbnail (400x300)
            $this->generateThumbnail($file['tmp_name'], $thumbPath, 400, 300);

            // DB insert
            $isCover = ($index === 0) ? 1 : 0;
            $this->db->execute(
                "INSERT INTO property_images 
                    (property_id, image_path, thumbnail, is_cover, sort_order)
                 VALUES (?, ?, ?, ?, ?)",
                [$propertyId, $filename, $thumbname, $isCover, $index]
            );

            $uploaded[] = $filename;
        }

        return ['success' => true, 'uploaded' => $uploaded];
    }

    public function deleteImage(int $imageId, int $userId): array {

        $image = $this->db->queryOne(
            "SELECT pi.*, p.user_id FROM property_images pi
             JOIN properties p ON p.id = pi.property_id
             WHERE pi.id = ?",
            [$imageId]
        );

        if (!$image) {
            return ['success' => false, 'message' => 'Image পাওয়া যায়নি।'];
        }

        if ($image['user_id'] !== $userId && !$this->isAdmin($userId)) {
            return ['success' => false, 'message' => 'Permission নেই।'];
        }

        $this->deleteFile(UPLOAD_PATH . 'properties/' . $image['image_path']);
        $this->deleteFile(UPLOAD_PATH . 'properties/thumbs/' . $image['thumbnail']);

        $this->db->execute("DELETE FROM property_images WHERE id = ?", [$imageId]);

        return ['success' => true, 'message' => 'Image delete হয়েছে।'];
    }


    // =========================================
    // ✅ WISHLIST
    // =========================================
    public function toggleWishlist(int $propertyId, int $userId): array {

        $exists = $this->db->queryOne(
            "SELECT id FROM wishlist WHERE property_id = ? AND user_id = ?",
            [$propertyId, $userId]
        );

        if ($exists) {
            $this->db->execute(
                "DELETE FROM wishlist WHERE property_id = ? AND user_id = ?",
                [$propertyId, $userId]
            );
            return ['success' => true, 'wishlisted' => false, 'message' => 'Wishlist থেকে সরানো হয়েছে।'];
        } else {
            $this->db->execute(
                "INSERT INTO wishlist (property_id, user_id) VALUES (?, ?)",
                [$propertyId, $userId]
            );
            return ['success' => true, 'wishlisted' => true, 'message' => 'Wishlist এ যোগ হয়েছে।'];
        }
    }

    public function getWishlist(int $userId): array {
        return $this->db->query(
            "SELECT p.*, pi.image_path AS cover_image,
                    pt.name AS type_name, a.name AS area_name
             FROM wishlist w
             JOIN properties p      ON p.id  = w.property_id
             JOIN property_types pt ON pt.id = p.type_id
             JOIN areas a           ON a.id  = p.area_id
             LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.is_cover = 1
             WHERE w.user_id = ? AND p.status = 'approved'
             ORDER BY w.created_at DESC",
            [$userId]
        );
    }


    // =========================================
    // ✅ RECENTLY VIEWED
    // =========================================
    public function trackView(int $propertyId, int $userId): void {
        $this->db->execute(
            "INSERT INTO recently_viewed (user_id, property_id)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE viewed_at = NOW()",
            [$userId, $propertyId]
        );
    }

    public function getRecentlyViewed(int $userId, int $limit = 6): array {
        return $this->db->query(
            "SELECT p.*, pi.image_path AS cover_image,
                    pt.name AS type_name, a.name AS area_name
             FROM recently_viewed rv
             JOIN properties p      ON p.id  = rv.property_id
             JOIN property_types pt ON pt.id = p.type_id
             JOIN areas a           ON a.id  = p.area_id
             LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.is_cover = 1
             WHERE rv.user_id = ? AND p.status = 'approved'
             ORDER BY rv.viewed_at DESC
             LIMIT ?",
            [$userId, $limit]
        );
    }


    // =========================================
    // 🔒 PRIVATE HELPERS
    // =========================================

    private function generateSlug(string $title): string {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/\s+/', '-', $slug);
        $slug = trim($slug, '-');

        // Unique check
        $original = $slug;
        $counter  = 1;
        while ($this->db->queryOne("SELECT id FROM properties WHERE slug = ?", [$slug])) {
            $slug = $original . '-' . $counter++;
        }
        return $slug;
    }

    private function saveAmenities(int $propertyId, array $amenityIds): void {
        foreach ($amenityIds as $amenityId) {
            $this->db->execute(
                "INSERT IGNORE INTO property_amenities (property_id, amenity_id) VALUES (?, ?)",
                [$propertyId, (int)$amenityId]
            );
        }
    }

    private function saveImages(int $propertyId, array $images): void {
        foreach ($images as $index => $filename) {
            $this->db->execute(
                "INSERT INTO property_images (property_id, image_path, is_cover, sort_order) VALUES (?, ?, ?, ?)",
                [$propertyId, $filename, $index === 0 ? 1 : 0, $index]
            );
        }
    }

    private function convertToWebP(string $source, string $dest, int $quality = 85): bool {
        $info = getimagesize($source);
        if (!$info) return false;

        $image = match($info['mime']) {
            'image/jpeg' => imagecreatefromjpeg($source),
            'image/png'  => imagecreatefrompng($source),
            'image/webp' => imagecreatefromwebp($source),
            default      => false
        };

        if (!$image) return false;
        $result = imagewebp($image, $dest, $quality);
        imagedestroy($image);
        return $result;
    }

    private function generateThumbnail(string $source, string $dest, int $w, int $h): bool {
        $info = getimagesize($source);
        if (!$info) return false;

        $srcW  = $info[0];
        $srcH  = $info[1];
        $ratio = min($w / $srcW, $h / $srcH);
        $newW  = (int)($srcW * $ratio);
        $newH  = (int)($srcH * $ratio);

        $image = match($info['mime']) {
            'image/jpeg' => imagecreatefromjpeg($source),
            'image/png'  => imagecreatefrompng($source),
            'image/webp' => imagecreatefromwebp($source),
            default      => false
        };

        if (!$image) return false;

        $thumb = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($thumb, $image, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);
        $result = imagewebp($thumb, $dest, 80);

        imagedestroy($image);
        imagedestroy($thumb);
        return $result;
    }

    private function normalizeFiles(array $files): array {
        $result = [];
        if (is_array($files['name'])) {
            foreach ($files['name'] as $i => $name) {
                $result[] = [
                    'name'     => $name,
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                ];
            }
        } else {
            $result[] = $files;
        }
        return $result;
    }

    private function incrementView(int $propertyId): void {
        $this->db->execute(
            "UPDATE properties SET views_count = views_count + 1 WHERE id = ?",
            [$propertyId]
        );
    }

    private function isAdmin(int $userId): bool {
        $user = $this->db->queryOne(
            "SELECT r.name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?",
            [$userId]
        );
        return $user && $user['name'] === 'admin';
    }

    private function deleteFile(string $path): void {
        if (file_exists($path)) unlink($path);
    }

    private function validateProperty(array $data): array {
        $errors = [];

        if (empty(trim($data['title'] ?? ''))) {
            $errors['title'] = 'Title দিন।';
        }
        if (empty($data['type_id'])) {
            $errors['type_id'] = 'Property type বেছে নিন।';
        }
        if (empty($data['area_id'])) {
            $errors['area_id'] = 'এলাকা বেছে নিন।';
        }
        if (empty($data['price']) || !is_numeric($data['price']) || $data['price'] <= 0) {
            $errors['price'] = 'সঠিক মূল্য দিন।';
        }
        if (!in_array($data['price_type'] ?? '', ['sale', 'rent'])) {
            $errors['price_type'] = 'বিক্রয় বা ভাড়া বেছে নিন।';
        }

        return $errors;
    }

    private function __clone() {}
}