<?php

namespace Elysiumrealms\Imageable\Console\Commands;

use Elysiumrealms\Imageable\Models\Imageable;
use Illuminate\Console\Command;

class PruneCommand extends Command
{
    /**
     * The signature of the console command.
     *
     * @var string
     */
    protected $signature = 'imageable:prune
        {--days : The number of days to prune the images}
        {--uploaded : Prune all the uploaded images}
    ';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Purne the images';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Pruning the images');

        $query = Imageable::query()
            ->when(!$this->option('uploaded'), fn($query)
            => $query->whereNull('owner_id'))
            ->when($this->option('days'), fn($query)
            => $query->where('created_at', '<', now()
                ->subDays($this->option('days'))));

        $bar = $this->output->createProgressBar(
            $count = $query->count()
        );

        $bar->start();

        $query->chunk(100, function ($images) use ($bar) {
            foreach ($images as $image) {
                $image->delete();
                $bar->advance();
            }
        });

        $bar->finish();

        $this->info("Pruned {$count} images");
    }
}
