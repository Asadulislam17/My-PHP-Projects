<?php

namespace App\Controllers;

/**
 * AgentController – Agent dashboard.
 */
class AgentController extends BaseController
{
    public function dashboard(): void
    {
        $this->requireRole('agent', 'admin');
        $userId = (int) $this->authUser()['id'];

        $stats = [
            'properties' => $this->db->selectOne(
                "SELECT COUNT(*) as c FROM properties WHERE agent_id = ? AND deleted_at IS NULL", [$userId])['c'] ?? 0,
            'inquiries'  => $this->db->selectOne(
                "SELECT COUNT(*) as c FROM inquiries i JOIN properties p ON p.id = i.property_id WHERE p.agent_id = ? AND i.status = 'new'", [$userId])['c'] ?? 0,
            'bookings'   => $this->db->selectOne(
                "SELECT COUNT(*) as c FROM bookings b JOIN properties p ON p.id = b.property_id WHERE p.agent_id = ? AND b.status = 'pending'", [$userId])['c'] ?? 0,
            'views'      => $this->db->selectOne(
                "SELECT COALESCE(SUM(p.views),0) as c FROM properties p WHERE p.agent_id = ?", [$userId])['c'] ?? 0,
        ];

        $recentProperties = $this->db->select(
            "SELECT p.*, pi.file_name AS thumb
               FROM properties p
               LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.is_primary = 1
              WHERE p.agent_id = ? AND p.deleted_at IS NULL
              ORDER BY p.created_at DESC LIMIT 5",
            [$userId]
        );

        $this->view('agent.dashboard', [
            'title'            => 'Agent Dashboard',
            'stats'            => $stats,
            'recentProperties' => $recentProperties,
        ]);
    }
}
