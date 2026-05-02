<?php

use App\Http\Controllers\Api\IssueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes  —  prefix: /api
|--------------------------------------------------------------------------
| All responses are JSON. No session/cookie auth required for this demo;
| add Sanctum token middleware here when auth is needed.
*/

Route::apiResource('issues', IssueController::class)->names('api.issues');// GET /api/issues
// POST /api/issues
// GET /api/issues/{issue}
// PATCH /api/issues/{issue}
// DELETE /api/issues/{issue}

