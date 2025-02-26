<?php

namespace Elysiumrealms\Imageable\Console\Commands;

use Elysiumrealms\Imageable\Models\Imageable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneCommand extends Command
{
    /**
     * The signature of the console command.
     *
     * @var string
     */
    protected $signature = 'imageable:prune
        {--days= : The number of days to keep the images}
    ';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Purge the deleted images';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Pruning the images');

        $query = Imageable::withTrashed()
            ->where(
                'deleted_at',
                '<',
                now()->subDays($this->option('days') ?? 0)
            );

        $bar = $this->output->createProgressBar(
            $count = $query->count()
        );

        $bar->start();

        $query->chunk(100, function ($images) use ($bar) {
            foreach ($images as $image) {
                $image->forceDelete();
            }
            collect($images)
                ->groupBy('disk')
                ->each(function ($images, $disk) {
                    Storage::disk($disk)->delete(
                        collect($images)->pluck('path')
                            ->toArray()
                    );
                });
            $bar->advance($images->count());
        });

        $bar->finish();

        $this->info("Purged {$count} images");
    }
}
