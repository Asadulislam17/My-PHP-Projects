<?php

/**
 * API Routes  –  /api/v1/*
 * ─────────────────────────
 * All API responses are JSON.
 * Versioning is via URL prefix.
 */

// $router->group(['prefix' => '/api/v1', 'middleware' => ['ApiMiddleware']], function ($router) {
//
//     // Auth
//     $router->post('/auth/register', [Api\AuthController::class, 'register']);
//     $router->post('/auth/login',    [Api\AuthController::class, 'login']);
//     $router->post('/auth/logout',   [Api\AuthController::class, 'logout']);
//
//     // Properties  (Phase 9)
//     $router->get('/properties',       [Api\PropertyController::class, 'index']);
//     $router->get('/properties/{id}',  [Api\PropertyController::class, 'show']);
//     $router->post('/properties',      [Api\PropertyController::class, 'store']);
//     $router->put('/properties/{id}',  [Api\PropertyController::class, 'update']);
//     $router->delete('/properties/{id}', [Api\PropertyController::class, 'destroy']);
//
//     // Search
//     $router->get('/search', [Api\SearchController::class, 'index']);
// });
