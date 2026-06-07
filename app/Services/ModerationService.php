<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ModerationService
{
    public function checkBook(int $bookId): array
    {
        $words = DB::table('restricted_words')
            ->get();

        if ($words->isEmpty()) {

            return [
                'status' => true
            ];
        }

        $book = DB::table('books')
            ->where('id', $bookId)
            ->first();

        $content = [];

        $content[] = $book->title;
        $content[] = $book->description;

        $chapters = DB::table('chapters')
            ->where('book_id', $bookId)
            ->whereNull('deleted_at')
            ->get();

        foreach ($chapters as $chapter) {

            $content[] = $chapter->title;

            $pages = DB::table('pages')
                ->where('chapter_id', $chapter->id)
                ->whereNull('deleted_at')
                ->get();

            foreach ($pages as $page) {

                $content[] = $page->title;
                $content[] = $page->content;
            }
        }

        $contentText = strtolower(
            implode(' ', $content)
        );

        foreach ($words as $word) {

            if (preg_match('/\b'. preg_quote(strtolower($word->word),'/'). '\b/',$contentText)) {

                return [
                    'status' => false,
                    'word' => $word->word,
                    'type' => $word->type
                ];
            }
        }

        return [
            'status' => true
        ];
    }
}