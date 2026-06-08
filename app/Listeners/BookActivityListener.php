<?php

namespace App\Listeners;

use App\Events\BookCreated;
use App\Events\BookPublished;
use App\Events\BookSubmitted;
use App\Events\BookVersionCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;


class BookActivityListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BookCreated|BookPublished|BookSubmitted|BookVersionCreated $event)
    {
        DB::table('activity_logs')
            ->insert([

                'user_id' => auth()->id(),

                'book_id' =>
                    $event->bookId ?? null,

                'event' =>
                    class_basename($event),

                'description' =>
                    class_basename($event)
                    . ' triggered',

                'created_at' => now(),

                'updated_at' => now()
            ]);
    }
}
