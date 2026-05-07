<?php

/**
 * Web Routes
 * ──────────
 * $router is injected by App::boot()
 */

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\BuyerController;
use App\Controllers\AgentController;
use App\Controllers\AdminController;

// ── Home ─────────────────────────────────────────────────────────────── //
$router->get('/',     [HomeController::class, 'index']);
$router->get('/home', [HomeController::class, 'index']);

// ── Auth ─────────────────────────────────────────────────────────────── //
$router->get('/auth/register',  [AuthController::class, 'registerForm']);
$router->post('/auth/register', [AuthController::class, 'register']);

$router->get('/auth/login',  [AuthController::class, 'loginForm']);
$router->post('/auth/login', [AuthController::class, 'login']);

$router->get('/auth/logout', [AuthController::class, 'logout']);

$router->get('/auth/verify-otp',  [AuthController::class, 'verifyOtpForm']);
$router->post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
$router->post('/auth/resend-otp', [AuthController::class, 'resendOtp']);

$router->get('/auth/forgot-password',  [AuthController::class, 'forgotForm']);
$router->post('/auth/forgot-password', [AuthController::class, 'forgot']);
$router->get('/auth/reset-password',   [AuthController::class, 'resetForm']);
$router->post('/auth/reset-password',  [AuthController::class, 'reset']);

// ── Buyer Dashboard ───────────────────────────────────────────────────── //
$router->get('/buyer/dashboard', [BuyerController::class, 'dashboard']);

// ── Agent Dashboard ───────────────────────────────────────────────────── //
$router->get('/agent/dashboard', [AgentController::class, 'dashboard']);

// ── Admin Dashboard ───────────────────────────────────────────────────── //
$router->get('/admin/dashboard', [AdminController::class, 'dashboard']);

// ── Properties (Phase 3) ─────────────────────────────────────────────── //
use App\Controllers\PropertyController;
use App\Controllers\AgentPropertyController;
use App\Controllers\AdminPropertyController;

// Public
$router->get('/properties',        [PropertyController::class, 'index']);
$router->get('/properties/search', [PropertyController::class, 'search']);
$router->get('/property/{slug}',   [PropertyController::class, 'show']);

// Agent CRUD
$router->get('/agent/properties',                    [AgentPropertyController::class, 'index']);
$router->get('/agent/properties/create',             [AgentPropertyController::class, 'create']);
$router->post('/agent/properties/create',            [AgentPropertyController::class, 'store']);
$router->get('/agent/properties/{id}/edit',          [AgentPropertyController::class, 'edit']);
$router->post('/agent/properties/{id}/edit',         [AgentPropertyController::class, 'update']);
$router->post('/agent/properties/{id}/delete',       [AgentPropertyController::class, 'destroy']);
$router->post('/agent/properties/image/delete',      [AgentPropertyController::class, 'deleteImage']);
$router->post('/agent/properties/image/primary',     [AgentPropertyController::class, 'setPrimaryImage']);

// Admin
$router->get('/admin/properties',                    [AdminPropertyController::class, 'index']);
$router->get('/admin/properties/{id}',               [AdminPropertyController::class, 'show']);
$router->post('/admin/properties/{id}/approve',      [AdminPropertyController::class, 'approve']);
$router->post('/admin/properties/{id}/reject',       [AdminPropertyController::class, 'reject']);
$router->post('/admin/properties/{id}/delete',       [AdminPropertyController::class, 'destroy']);
$router->post('/admin/properties/{id}/feature',      [AdminPropertyController::class, 'feature']);
