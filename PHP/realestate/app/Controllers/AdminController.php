<?php

namespace App\Controllers;

/**
 * AdminController – Admin dashboard with platform-wide stats.
 */
class AdminController extends BaseController
{
    public function dashboard(): void
    {
        $this->requireAdmin();

        $stats = [
            'users'      => $this->db->selectOne("SELECT COUNT(*) as c FROM users WHERE deleted_at IS NULL")['c'] ?? 0,
            'properties' => $this->db->selectOne("SELECT COUNT(*) as c FROM properties WHERE deleted_at IS NULL")['c'] ?? 0,
            'pending'    => $this->db->selectOne("SELECT COUNT(*) as c FROM properties WHERE approval_status = 'pending'")['c'] ?? 0,
            'inquiries'  => $this->db->selectOne("SELECT COUNT(*) as c FROM inquiries WHERE status = 'new'")['c'] ?? 0,
            'bookings'   => $this->db->selectOne("SELECT COUNT(*) as c FROM bookings WHERE status = 'pending'")['c'] ?? 0,
            'revenue'    => $this->db->selectOne("SELECT COALESCE(SUM(amount),0) as c FROM transactions WHERE status = 'success'")['c'] ?? 0,
        ];

        $pendingProperties = $this->db->select(
            "SELECT p.*, u.name AS agent_name
               FROM properties p JOIN users u ON u.id = p.agent_id
              WHERE p.approval_status = 'pending' AND p.deleted_at IS NULL
              ORDER BY p.created_at DESC LIMIT 10"
        );

        $recentUsers = $this->db->select(
            "SELECT u.*, r.name AS role
               FROM users u JOIN roles r ON r.id = u.role_id
              WHERE u.deleted_at IS NULL
              ORDER BY u.created_at DESC LIMIT 8"
        );

        $this->view('admin.dashboard', [
            'title'             => 'Admin Dashboard',
            'stats'             => $stats,
            'pendingProperties' => $pendingProperties,
            'recentUsers'       => $recentUsers,
        ]);
    }
}
