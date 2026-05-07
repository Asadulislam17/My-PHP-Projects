<?php

namespace App\Controllers;

/**
 * BuyerController – Buyer dashboard and account pages.
 */
class BuyerController extends BaseController
{
    public function dashboard(): void
    {
        $this->requireRole('buyer', 'admin');

        $userId = (int) $this->authUser()['id'];

        $stats = [
            'wishlist'  => $this->db->selectOne("SELECT COUNT(*) as c FROM wishlist WHERE user_id = ?", [$userId])['c'] ?? 0,
            'inquiries' => $this->db->selectOne("SELECT COUNT(*) as c FROM inquiries WHERE sender_id = ?", [$userId])['c'] ?? 0,
            'bookings'  => $this->db->selectOne("SELECT COUNT(*) as c FROM bookings WHERE user_id = ?", [$userId])['c'] ?? 0,
        ];

        $recentlyViewed = $this->db->select(
            "SELECT p.*, pi.file_name AS thumb
               FROM recently_viewed rv
               JOIN properties p  ON p.id = rv.property_id
               LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.is_primary = 1
              WHERE rv.user_id = ?
              ORDER BY rv.viewed_at DESC LIMIT 6",
            [$userId]
        );

        $this->view('buyer.dashboard', [
            'title'          => 'My Dashboard',
            'stats'          => $stats,
            'recentlyViewed' => $recentlyViewed,
        ]);
    }
}
