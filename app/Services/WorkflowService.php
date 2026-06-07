<?php

namespace App\Services;

use Exception;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Helpers\RoleHelper;


class WorkflowService
{
    private VersionService $versionService;
    private ModerationService $moderationService;

    public function __construct()
    {
        $this->versionService = new VersionService();
        $this->moderationService = new ModerationService();
    }

    public function submitBook(int $bookId): array
    {
        DB::beginTransaction();

        try {

            $book = DB::table('books')
                ->where('id', $bookId)
                ->first();

            if (!$book) {

                return ApiResponse::error(
                    'Book not found',
                    404
                );
            }

            if ($book->author_id != auth()->id()) {

                return ApiResponse::error(
                    'Unauthorized',
                    403
                );
            }

            if ($book->status !== 'draft') {

                return ApiResponse::error(
                    'Only draft books can be submitted',
                    422
                );
            }

            $chapterCount = DB::table('chapters')
                ->where('book_id', $bookId)
                ->whereNull('deleted_at')
                ->count();

            if ($chapterCount == 0) {

                return ApiResponse::error(
                    'Book must contain at least one chapter',
                    422
                );
            }

            $pageCount = DB::table('pages')
                ->join(
                    'chapters',
                    'pages.chapter_id',
                    '=',
                    'chapters.id'
                )
                ->where('chapters.book_id', $bookId)
                ->whereNull('pages.deleted_at')
                ->whereNull('chapters.deleted_at')
                ->count();

            if ($pageCount == 0) {

                return ApiResponse::error(
                    'Book must contain at least one page',
                    422
                );
            }

            //Moderation
            $moderationResult = $this
                ->moderationService
                ->checkBook($bookId);

            if (!$moderationResult['status']) {

                return ApiResponse::error(
                    ucfirst($moderationResult['type'])
                    . ' word detected: '
                    . $moderationResult['word'],
                    422
                );
            }


            DB::table('books')
                ->where('id', $bookId)
                ->update([
                    'status' => 'submitted',
                    'updated_at' => now()
                ]);

            DB::commit();

            $this->versionService
                ->createSnapshot($bookId);

            return ApiResponse::success(
                'Book submitted successfully'
            );

        } catch (Exception $e) {

            DB::rollBack();

            return ApiResponse::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function startReview(int $bookId): array
    {
        try {

            if (!RoleHelper::isReviewer()) {

                return ApiResponse::error(
                    'Only reviewer can perform this action',
                    403
                );
            }

            $book = DB::table('books')
                ->where('id', $bookId)
                ->first();

            if (!$book) {

                return ApiResponse::error(
                    'Book not found',
                    404
                );
            }

            if ($book->status !== 'submitted') {

                return ApiResponse::error(
                    'Only submitted books can be reviewed',
                    422
                );
            }

            DB::table('books')
                ->where('id', $bookId)
                ->update([
                    'status' => 'under_review',
                    'updated_at' => now()
                ]);

            $this->versionService
                ->createSnapshot($bookId);

            return ApiResponse::success(
                'Book moved to review successfully'
            );

        } catch (Exception $e) {

            return ApiResponse::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function approveBook(int $bookId): array
    {
        try {

            if (!RoleHelper::isReviewer()) {

                return ApiResponse::error(
                    'Only reviewer can perform this action',
                    403
                );
            }

            $book = DB::table('books')
                ->where('id', $bookId)
                ->first();

            if (!$book) {

                return ApiResponse::error(
                    'Book not found',
                    404
                );
            }

            if ($book->status !== 'under_review') {

                return ApiResponse::error(
                    'Only books under review can be approved',
                    422
                );
            }

            DB::table('books')
                ->where('id', $bookId)
                ->update([
                    'status' => 'approved',
                    'updated_at' => now()
                ]);

            $this->versionService
                ->createSnapshot($bookId);

            return ApiResponse::success(
                'Book approved successfully'
            );

        } catch (Exception $e) {

            return ApiResponse::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function rejectBook(int $bookId): array
    {
        try {

            if (!RoleHelper::isReviewer()) {

                return ApiResponse::error(
                    'Only reviewer can perform this action',
                    403
                );
            }

            $book = DB::table('books')
                ->where('id', $bookId)
                ->first();

            if (!$book) {

                return ApiResponse::error(
                    'Book not found',
                    404
                );
            }

            if ($book->status !== 'under_review') {

                return ApiResponse::error(
                    'Only books under review can be rejected',
                    422
                );
            }

            DB::table('books')
                ->where('id', $bookId)
                ->update([
                    'status' => 'rejected',
                    'updated_at' => now()
                ]);

            $this->versionService
                ->createSnapshot($bookId);

            return ApiResponse::success(
                'Book rejected successfully'
            );

        } catch (Exception $e) {

            return ApiResponse::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function publishBook(int $bookId): array
    {
        try {

            if (!RoleHelper::isAdmin()) {

                return ApiResponse::error(
                    'Only admin can perform this action',
                    403
                );
            }

            $book = DB::table('books')
                ->where('id', $bookId)
                ->first();

            if (!$book) {

                return ApiResponse::error(
                    'Book not found',
                    404
                );
            }

            if ($book->status !== 'approved') {

                return ApiResponse::error(
                    'Only approved books can be published',
                    422
                );
            }

            DB::table('books')
                ->where('id', $bookId)
                ->update([
                    'status' => 'published',
                    'updated_at' => now()
                ]);

            $this->versionService
                ->createSnapshot($bookId);

            return ApiResponse::success(
                'Book published successfully'
            );

        } catch (Exception $e) {

            return ApiResponse::error(
                $e->getMessage(),
                500
            );
        }
    }
}