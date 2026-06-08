<?php

namespace App\Jobs;

use App\Services\VersionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\IOFactory;

class ProcessBookUploadJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $bookId,public string $filePath,public string $fileName,public int $userId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fullPath = storage_path(
            'app/public/' . $this->filePath
        );

        $phpWord = IOFactory::load($fullPath);

        $content = '';

        foreach ($phpWord->getSections() as $section) {

            foreach ($section->getElements() as $element) {

                if (method_exists($element, 'getText')) {

                    $content .=
                        $element->getText() . "\n";
                }
            }
        }

        if (empty(trim($content))) {
            return;
        }

        DB::table('chapters')
            ->where('book_id', $this->bookId)
            ->where('title', 'Imported Chapter')
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => now()
            ]);

        $chapterId = DB::table('chapters')
            ->insertGetId([

                'book_id' => $this->bookId,

                'title' => pathinfo(
                    $this->fileName,
                    PATHINFO_FILENAME
                ),

                'sort_order' => 1,

                'created_at' => now(),

                'updated_at' => now()
            ]);

        $pageLength = 3000;

        $chunks = str_split(
            $content,
            $pageLength
        );

        foreach ($chunks as $index => $chunk) {

            DB::table('pages')
                ->insert([

                    'chapter_id' => $chapterId,

                    'title' => 'Page ' . ($index + 1),

                    'content' => '<p>'
                        . nl2br(e($chunk))
                        . '</p>',

                    'page_number' => ($index + 1),

                    'created_at' => now(),

                    'updated_at' => now()
                ]);
        }

        (new VersionService())
            ->createSnapshot(
                $this->bookId,
                $this->userId
            );
    }
}
