<?php

use App\Http\Controllers\IssueController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('issues.index')); // fn() is a shorthand for an anonymous function

Route::resource('issues', IssueController::class); // resource() is a shorthand for a set of routes for a resource
// GET /issues
// GET /issues/create
// POST /issues
// GET /issues/{issue}
// GET /issues/{issue}/edit
// PATCH /issues/{issue}    
// DELETE /issues/{issue}

