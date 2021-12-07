<?php

namespace App\Console\Commands;

use App\Models\Challenge;
use App\Models\Equipment;
use App\Models\Movement;
use App\Models\Review;
use App\Models\Spot;
use App\Models\Comment;
use App\Models\Workout;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RemoveDeletedContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'content:remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function performRemoval($content, $name)
    {
        $count = count($content);
        $this->line('Removing ' . $count . ' ' . $name . '...');
        foreach($content as $item) {
            $item->forceDelete();
        }
        $this->info('Removed ' . $count . ' ' . $name . '.');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $spots = Spot::onlyTrashed()->where('deleted_at', '<', Carbon::now()->subDays(30))->get();
        $this->performRemoval($spots, 'spots');

        $reviews = Review::onlyTrashed()->where('deleted_at', '<', Carbon::now()->subDays(30))->get();
        $this->performRemoval($reviews, 'reviews');

        $comments = Comment::onlyTrashed()->where('deleted_at', '<', Carbon::now()->subDays(30))->get();
        $this->performRemoval($comments, 'comments');

        $challenges = Challenge::onlyTrashed()->where('deleted_at', '<', Carbon::now()->subDays(30))->get();
        $this->performRemoval($challenges, 'challenges');

        $movements = Movement::onlyTrashed()->where('deleted_at', '<', Carbon::now()->subDays(30))->get();
        $this->performRemoval($movements, 'movements');

        $equipment = Equipment::onlyTrashed()->where('deleted_at', '<', Carbon::now()->subDays(30))->get();
        $this->performRemoval($equipment, 'equipment');

        $workouts = Workout::onlyTrashed()->where('deleted_at', '<', Carbon::now()->subDays(30))->get();
        $this->performRemoval($workouts, 'workouts');

        $this->info('Removed all deleted content.');

        return 0;
    }
}
