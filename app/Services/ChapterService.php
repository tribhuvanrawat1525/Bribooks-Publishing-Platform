<?php

namespace App\Services;

use Exception;
use App\Helpers\ApiResponse;
use App\Helpers\BookHelper;
use Illuminate\Support\Facades\DB;

class ChapterService
{
    private VersionService $versionService;

    public function __construct()
    {
        $this->versionService = new VersionService();
    }

    public function create(int $bookId,array $data): array {

        DB::beginTransaction();

        try {

            $book = DB::table('books')
                ->where('id', $bookId)
                ->whereNull('deleted_at')
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

            if (!BookHelper::canEdit($book->status)) {

                return ApiResponse::error(
                    'Book cannot be modified in current status',
                    422
                );
            }


            $chapterId = DB::table('chapters')
                ->insertGetId([

                    'book_id' => $bookId,
                    'title' => $data['title'],
                    'sort_order' => $data['sort_order'] ?? 1,

                    'created_at' => now(),
                    'updated_at' => now()
                ]);

            DB::commit();

            $this->versionService
                ->createSnapshot($bookId);

            return ApiResponse::success(
                'Chapter created successfully',
                [
                    'chapter_id' => $chapterId
                ]
            );

        } catch (Exception $e) {

            DB::rollBack();

            return ApiResponse::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function list(int $bookId): array {

        $book = DB::table('books')
            ->where('id', $bookId)
            ->whereNull('deleted_at')
            ->first();

        if (!$book) {

            return ApiResponse::error(
                'Book not found',
                404
            );
        }

        $chapters = DB::table('chapters')
            ->where('book_id', $bookId)
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->get();

        return ApiResponse::success(
            'Chapters fetched successfully',
            [
                'chapters' => $chapters
            ]
        );
    }

    public function update(int $chapterId,array $data): array {

        DB::beginTransaction();

        try {

            $chapter = DB::table('chapters')
                ->where('id', $chapterId)
                ->whereNull('deleted_at')
                ->first();

            if (!$chapter) {

                return ApiResponse::error(
                    'Chapter not found',
                    404
                );
            }

            $book = DB::table('books')
                ->where('id', $chapter->book_id)
                ->first();

            if ($book->author_id != auth()->id()) {

                return ApiResponse::error(
                    'Unauthorized',
                    403
                );
            }

            if (!BookHelper::canEdit($book->status)) {

                return ApiResponse::error(
                    'Book cannot be modified in current status',
                    422
                );
            }

            DB::table('chapters')
                ->where('id', $chapterId)
                ->update([

                    'title' => $data['title'],

                    'sort_order' => $data['sort_order'] ?? 1,

                    'updated_at' => now()
                ]);

            DB::commit();

            $this->versionService
                ->createSnapshot($book->id);

            return ApiResponse::success(
                'Chapter updated successfully'
            );

        } catch (Exception $e) {

            DB::rollBack();

            return ApiResponse::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function delete(int $chapterId): array {

        DB::beginTransaction();

        try {

            $chapter = DB::table('chapters')
                ->where('id', $chapterId)
                ->whereNull('deleted_at')
                ->first();

            if (!$chapter) {

                return ApiResponse::error(
                    'Chapter not found',
                    404
                );
            }

            $book = DB::table('books')
                ->where('id', $chapter->book_id)
                ->first();

            if ($book->author_id != auth()->id()) {

                return ApiResponse::error(
                    'Unauthorized',
                    403
                );
            }

            if (!BookHelper::canEdit($book->status)) {

                return ApiResponse::error(
                    'Book cannot be modified in current status',
                    422
                );
            }

            DB::table('chapters')
                ->where('id', $chapterId)
                ->update([
                    'deleted_at' => now()
                ]);

            DB::commit();

            $this->versionService
                ->createSnapshot($book->id);

            return ApiResponse::success(
                'Chapter deleted successfully'
            );

        } catch (Exception $e) {

            DB::rollBack();

            return ApiResponse::error(
                $e->getMessage(),
                500
            );
        }
    }
}