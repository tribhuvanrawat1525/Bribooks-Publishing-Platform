<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Constants\BookStatus;
use App\Helpers\BookHelper;
use Illuminate\Support\Facades\DB;
use App\Services\VersionService;
use Exception;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpWord\IOFactory;

class BookService
{
    private VersionService $versionService;

    public function __construct()
    {
        $this->versionService = new VersionService();
    }


    public function create(array $data): array
    {
        try {

            if (auth()->user()->role !== 'author') {

                return ApiResponse::error(
                    'Only authors can create books',
                    403
                );
            }

            $bookId = DB::table('books')->insertGetId([
                'author_id'  => auth()->id(),
                'title'      => $data['title'],
                'description'=> $data['description'] ?? null,
                'status'     => BookStatus::DRAFT,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return ApiResponse::success(
                'Book created successfully',
                [
                    'book_id' => $bookId
                ]
            );

        } catch (\Exception $e) {

            return ApiResponse::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function list(): array
    {
        $user = auth()->user();

        $query = DB::table('books')
            ->whereNull('deleted_at');

        if ($user->role === 'author') {

            $query->where(
                'author_id',
                $user->id
            );

        } elseif ($user->role === 'reviewer') {

            $query->where(
                'status',
                'submitted'
            );
        }

        $books = $query
            ->orderByDesc('id')
            ->get();

        return ApiResponse::success(
            'Books fetched successfully',
            [
                'books' => $books
            ]
        );
    }

    public function details(int $bookId): array
    {
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

        $user = auth()->user();

        if ($user->role === 'author' && $book->author_id != $user->id) {
            return ApiResponse::error(
                'Unauthorized',
                403
            );
        }

        return ApiResponse::success(
            'Book fetched successfully',
            [
                'book' => $book
            ]
        );
    }

    public function update(int $bookId,array $data): array 
    {

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

        if ($book->status === 'published') {

            return ApiResponse::error(
                'Published book cannot be edited',
                403
            );
        }

        if (!BookHelper::canEdit($book->status)) {

            return ApiResponse::error(
                'Book cannot be modified in current status',
                422
            );
        }

        DB::table('books')
            ->where('id', $bookId)
            ->update([
                'title' => $data['title'],
                'description' => $data['description'],
                'updated_at' => now()
            ]);

        return ApiResponse::success(
            'Book updated successfully'
        );
    }

    public function delete(int $bookId): array
    {
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

        DB::table('books')
            ->where('id', $bookId)
            ->update([
                'deleted_at' => now()
            ]);

        return ApiResponse::success(
            'Book deleted successfully'
        );
    }

    public function upload(int $bookId,UploadedFile $file): array
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

            if ($book->status === 'published') {

                return ApiResponse::error(
                    'Published book cannot be modified',
                    403
                );
            }

            $path = $file->store(
                'book_uploads',
                'public'
            );

            DB::table('book_uploads')
                ->insert([

                    'book_id' => $bookId,

                    'file_name' => $file->getClientOriginalName(),

                    'file_path' => $path,

                    'file_type' => $file->getClientOriginalExtension(),

                    'created_at' => now(),

                    'updated_at' => now()
                ]);

            $fullPath = storage_path('app/public/' . $path);

            $phpWord = IOFactory::load($fullPath);

            $content = '';

            foreach ($phpWord->getSections()as $section) {

                foreach ($section->getElements()as $element) {

                    if (method_exists($element,'getText')) {
                        $content .=
                            $element->getText()
                            . "\n";
                    }
                }
            }

            if (empty(trim($content))) {

                return ApiResponse::error(
                    'Unable to extract content from document',
                    422
                );
            }

            //keep only the latest imported chapter
            DB::table('chapters')
                ->where('book_id', $bookId)
                ->where('title', 'Imported Chapter')
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => now()
                ]);

            $chapterId = DB::table('chapters')
                ->insertGetId([

                    'book_id' => $bookId,

                    'title'  => pathinfo($file->getClientOriginalName(),PATHINFO_FILENAME),

                    'sort_order' => 1,

                    'created_at' => now(),

                    'updated_at' => now()
                ]);

            $pageLength = 3000;

            $chunks = str_split($content,$pageLength);

            foreach ($chunks as $index => $chunk) {

                DB::table('pages')
                    ->insert([

                        'chapter_id' => $chapterId,

                        'title' =>'Page '. ($index + 1),

                        'content' =>'<p>'. nl2br(e($chunk)). '</p>',

                        'page_number' =>($index + 1),

                        'created_at' => now(),

                        'updated_at' => now()
                    ]);
            }

            $this->versionService
                ->createSnapshot($bookId);

            DB::commit();

            return ApiResponse::success(
                'Document uploaded successfully',
                [
                    'pages_created' =>
                        count($chunks)
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
}