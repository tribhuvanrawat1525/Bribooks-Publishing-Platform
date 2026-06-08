<?php

namespace App\Services;

use App\Events\BookVersionCreated;
use Exception;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;

class VersionService
{
    public function createVersion(int $bookId): array
    {
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

            $versionId = $this->createSnapshot(
                $bookId
            );

            $version = DB::table('book_versions')
                ->where('id', $versionId)
                ->first();

            DB::commit();

            return ApiResponse::success(
                'Version created successfully',
                [
                    'version_id' => $versionId,
                    'version_number' => $version->version_number
                ]
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return ApiResponse::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function listVersions(int $bookId): array {

        $versions = DB::table('book_versions')
            ->select(
                'id',
                'version_number',
                'created_by',
                'created_at'
            )
            ->where('book_id', $bookId)
            ->orderByDesc('version_number')
            ->get();

        return ApiResponse::success(
            'Versions fetched successfully',
            [
                'versions' => $versions
            ]
        );
    }

    public function viewVersion(int $bookId,int $versionId): array {

        $version = DB::table('book_versions')
            ->where('id', $versionId)
            ->where('book_id', $bookId)
            ->first();

        if (!$version) {

            return ApiResponse::error(
                'Version not found',
                404
            );
        }

        return ApiResponse::success(
            'Version fetched successfully',
            [
                'version' => json_decode(
                    $version->snapshot,
                    true
                )
            ]
        );
    }

    public function createSnapshot(int $bookId,?int $userId = null): int
    {
        $book = DB::table('books')
            ->where('id', $bookId)
            ->whereNull('deleted_at')
            ->first();

        if (!$book) {
            throw new \Exception('Book not found');
        }

        $chapters = DB::table('chapters')
            ->where('book_id', $bookId)
            ->whereNull('deleted_at')
            ->get();

        $chapterData = [];

        foreach ($chapters as $chapter) {

            $pages = DB::table('pages')
                ->where('chapter_id', $chapter->id)
                ->whereNull('deleted_at')
                ->get();

            $chapterData[] = [
                'chapter' => $chapter,
                'pages' => $pages
            ];
        }

        $latestVersion = DB::table('book_versions')
            ->where('book_id', $bookId)
            ->max('version_number');

        $versionNumber = $latestVersion
            ? $latestVersion + 1
            : 1;

        $snapshot = [
            'book' => $book,
            'chapters' => $chapterData
        ];

        $versionId = DB::table('book_versions')
            ->insertGetId([
                'book_id' => $bookId,
                'version_number' => $versionNumber,
                'snapshot' => json_encode($snapshot),
                'created_by' => $userId ?? auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

        DB::table('books')
            ->where('id', $bookId)
            ->update([
                'current_version_id' => $versionId
            ]);

        event(new BookVersionCreated($versionId,$bookId));

        return $versionId;
    }

    public function restoreVersion(int $bookId,int $versionId): array
    {
        DB::beginTransaction();

        try {

            $version = DB::table('book_versions')
                ->where('id', $versionId)
                ->where('book_id', $bookId)
                ->first();

            if (!$version) {

                return ApiResponse::error('Version not found',404);
            }

            $book = DB::table('books')
                ->where('id', $bookId)
                ->first();

            if (!$book) {

                return ApiResponse::error('Book not found',404);
            }

            //This check is to validate only author can restore the version
            if (auth()->user()->role !== 'author') {

                return ApiResponse::error(
                    'Only author can restore versions',
                    403
                );
            }

            if ($book->author_id != auth()->id()) {

                return ApiResponse::error(
                    'Unauthorized',
                    403
                );
            }

            //ONly draft adn rejected books will only able to restore the version
            if (!in_array($book->status,['draft','rejected'])) {

                return ApiResponse::error(
                    'Version restore is not allowed in current status',
                    422
                );
            }

            $snapshot = json_decode(
                $version->snapshot,
                true
            );

            //Restore books
            DB::table('books')->where('id', $bookId)
                ->update([

                    'title' => $snapshot['book']['title'],

                    'description' => $snapshot['book']['description'],

                    'updated_at' => now()
                ]);

            //Soft delete existing pages
            $chapterIds = DB::table('chapters')->where('book_id', $bookId)->pluck('id');

            DB::table('pages')->whereIn('chapter_id',$chapterIds)->update(['deleted_at' => now()]);

            //Soft delete existing pages
            DB::table('chapters')->where('book_id', $bookId)->update(['deleted_at' => now()]);

            //Recreate Chapters & Pages
            foreach ($snapshot['chapters']as $chapterData) {

                $chapter = $chapterData['chapter'];

                $newChapterId = DB::table('chapters')
                    ->insertGetId([

                        'book_id' => $bookId,

                        'title' => $chapter['title'],

                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                foreach ($chapterData['pages']as $page) {

                    DB::table('pages')
                        ->insert([

                            'chapter_id' => $newChapterId,

                            'title' => $page['title'],

                            'content' => $page['content'],

                            'page_number' => $page['page_number'],

                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                }
            }

            $this->createSnapshot($bookId);

            DB::commit();

            return ApiResponse::success(
                'Version restored successfully'
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