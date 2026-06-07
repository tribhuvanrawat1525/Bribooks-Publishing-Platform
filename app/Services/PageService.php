<?php

namespace App\Services;

use Exception;
use App\Helpers\ApiResponse;
use App\Helpers\BookHelper;
use Illuminate\Support\Facades\DB;

class PageService
{
    private VersionService $versionService;

    public function __construct()
    {
        $this->versionService = new VersionService();
    }

    public function create(int $chapterId,array $data): array {

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

            $lastPageNumber = DB::table('pages')
                ->where('chapter_id', $chapterId)
                ->max('page_number');

            $pageNumber = $lastPageNumber
                ? $lastPageNumber + 1
                : 1;

            $pageId = DB::table('pages')
                ->insertGetId([

                    'chapter_id' => $chapterId,

                    'title' => $data['title'] ?? null,

                    'content' => $data['content'],

                    'page_number' => $pageNumber ?? 1,

                    'created_at' => now(),
                    'updated_at' => now()
                ]);

            DB::commit();

            $this->versionService
                ->createSnapshot($book->id);

            return ApiResponse::success(
                'Page created successfully',
                [
                    'page_id' => $pageId
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

    public function list(int $chapterId): array {

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

        $pages = DB::table('pages')
            ->where('chapter_id', $chapterId)
            ->whereNull('deleted_at')
            ->orderBy('page_number')
            ->get();

        return ApiResponse::success(
            'Pages fetched successfully',
            [
                'pages' => $pages
            ]
        );
    }

    public function update(int $pageId,array $data): array {

        DB::beginTransaction();

        try {

            $page = DB::table('pages')
                ->where('id', $pageId)
                ->whereNull('deleted_at')
                ->first();

            if (!$page) {

                return ApiResponse::error(
                    'Page not found',
                    404
                );
            }

            $chapter = DB::table('chapters')
                ->where('id', $page->chapter_id)
                ->first();

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

            DB::table('pages')
                ->where('id', $pageId)
                ->update([

                    'title' => $data['title'] ?? null,

                    'content' => $data['content'],

                    'page_number' => $data['page_number'] ?? 1,

                    'updated_at' => now()
                ]);

            DB::commit();

            $this->versionService
                ->createSnapshot($book->id);

            return ApiResponse::success(
                'Page updated successfully'
            );

        } catch (Exception $e) {

            DB::rollBack();

            return ApiResponse::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function delete(int $pageId): array {

        DB::beginTransaction();

        try {

            $page = DB::table('pages')
                ->where('id', $pageId)
                ->whereNull('deleted_at')
                ->first();

            if (!$page) {

                return ApiResponse::error(
                    'Page not found',
                    404
                );
            }

            $chapter = DB::table('chapters')
                ->where('id', $page->chapter_id)
                ->first();

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

            DB::table('pages')
                ->where('id', $pageId)
                ->update([
                    'deleted_at' => now()
                ]);

            DB::commit();

            $this->versionService
                ->createSnapshot($book->id);

            return ApiResponse::success(
                'Page deleted successfully'
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