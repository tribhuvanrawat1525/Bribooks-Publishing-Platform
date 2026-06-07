<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function stats(): array
    {
        if (auth()->user()->role !== 'admin') {

            return ApiResponse::error(
                'Only admin can access dashboard',
                403
            );
        }

        $data = [

            'total_books' => DB::table('books')
                ->whereNull('deleted_at')
                ->count(),

            'draft_books' => DB::table('books')
                ->where('status', 'draft')
                ->whereNull('deleted_at')
                ->count(),

            'submitted_books' => DB::table('books')
                ->where('status', 'submitted')
                ->whereNull('deleted_at')
                ->count(),

            'under_review_books' => DB::table('books')
                ->where('status', 'under_review')
                ->whereNull('deleted_at')
                ->count(),

            'approved_books' => DB::table('books')
                ->where('status', 'approved')
                ->whereNull('deleted_at')
                ->count(),

            'published_books' => DB::table('books')
                ->where('status', 'published')
                ->whereNull('deleted_at')
                ->count(),

            'rejected_books' => DB::table('books')
                ->where('status', 'rejected')
                ->whereNull('deleted_at')
                ->count(),

            'total_authors' => DB::table('users')
                ->where('role', 'author')
                ->count(),

            'total_reviewers' => DB::table('users')
                ->where('role', 'reviewer')
                ->count(),

            'total_chapters' => DB::table('chapters')
                ->whereNull('deleted_at')
                ->count(),

            'total_pages' => DB::table('pages')
                ->whereNull('deleted_at')
                ->count(),

            'total_versions' => DB::table('book_versions')
                ->count(),
        ];

        return ApiResponse::success(
            'Dashboard data fetched successfully',
            $data
        );
    }
}