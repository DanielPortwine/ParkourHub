<?php

use App\Movement;
use App\MovementCategory;
use App\MovementField;
use App\MovementType;
use Illuminate\Database\Seeder;

class MovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $movement = factory(Movement::class)->create([
            'category_id' => 1,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 1,
            'name' => 'Standing Precision to Wall',
            'description' => 'A standard jump from some place to a wall',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(2); // sticks
        $movement->fields()->attach(5); // distance
        $movement->fields()->attach(6); // height

        $movement = factory(Movement::class)->create([
            'category_id' => 1,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 1,
            'name' => 'Standing Precision to Rail',
            'description' => 'A standard jump from some place to a rail',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(2); // sticks
        $movement->fields()->attach(5); // distance
        $movement->fields()->attach(6); // height

        $movement = factory(Movement::class)->create([
            'category_id' => 1,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 1,
            'name' => 'Running Precision to Wall',
            'description' => 'A running jump from some place to a wall',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(2); // sticks
        $movement->fields()->attach(5); // distance
        $movement->fields()->attach(6); // height

        $movement = factory(Movement::class)->create([
            'category_id' => 1,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 1,
            'name' => 'Running Precision to Rail',
            'description' => 'A running jump from some place to a rail',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(2); // sticks
        $movement->fields()->attach(5); // distance
        $movement->fields()->attach(6); // height

        $movement = factory(Movement::class)->create([
            'category_id' => 2,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 1,
            'name' => 'Kong Vault/Cat Pass',
            'description' => 'A vault involving both hands being placed on the obstacle and the feet passing between them',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(6); // height

        $movement = factory(Movement::class)->create([
            'category_id' => 2,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 1,
            'name' => 'Dash Vault',
            'description' => 'A vault where you pass over the obstacle feet first and then place your hands on the obstacle behind your back to push you up and over',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(6); // height

        $movement = factory(Movement::class)->create([
            'category_id' => 2,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 1,
            'name' => 'Lazy Vault',
            'description' => 'A vault to pass over an obstacle sideways or diagonally while running',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(6); // height

        $movement = factory(Movement::class)->create([
            'category_id' => 2,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 1,
            'name' => 'Reverse Vault',
            'description' => 'A vault where you rotate 360 degrees as you pass over an obstacle',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(6); // height

        $movement = factory(Movement::class)->create([
            'category_id' => 3,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 1,
            'name' => 'Standing Backflip',
            'description' => 'A flip that rotates backwards',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps

        $movement = factory(Movement::class)->create([
            'category_id' => 3,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 1,
            'name' => 'Handspring',
            'description' => 'A trick involving diving forwards onto your hands and then springing over on to your feet',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps

        $movement = factory(Movement::class)->create([
            'category_id' => 3,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 1,
            'name' => 'Dive Cartwheel',
            'description' => 'A sideways dive onto your hands then continuing the rotation back onto your feet',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps

        $movement = factory(Movement::class)->create([
            'category_id' => 3,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 1,
            'name' => 'Dive Roll',
            'description' => 'A forwards dive on to your hands then rolling over your shoulder',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps

        $movement = factory(Movement::class)->create([
            'category_id' => 4,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 1,
            'name' => 'Climb Up',
            'description' => 'Getting on to an obstacle by pulling yourself up',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps

        $movement = factory(Movement::class)->create([
            'category_id' => 6,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 1,
            'name' => 'Lache',
            'description' => 'Throwing yourself from a bar',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(5); // distance

        $movement = factory(Movement::class)->create([
            'category_id' => 6,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 1,
            'name' => 'Lache Precision',
            'description' => 'Throwing yourself from a bar and landing on an object',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(2); // sticks
        $movement->fields()->attach(5); // distance

        $movement = factory(Movement::class)->create([
            'category_id' => 7,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 2,
            'name' => 'Pushup',
            'description' => 'Pushing your body from flat on the floor to fully extended arms',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(3); // weight

        $movement = factory(Movement::class)->create([
            'category_id' => 7,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 2,
            'name' => 'Pullup',
            'description' => 'Pulling yourself up from arms fully extended to your chin above your hands',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(3); // weight

        $movement = factory(Movement::class)->create([
            'category_id' => 7,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 2,
            'name' => 'Handstand Pushup',
            'description' => 'Pushing your body from bent arms to fully extended whilst in a handstand position',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(3); // weight

        $movement = factory(Movement::class)->create([
            'category_id' => 7,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 2,
            'name' => 'Bicep Curl',
            'description' => 'Contracting your arm from fully extended to hands touching your shoulders',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(3); // weight

        $movement = factory(Movement::class)->create([
            'category_id' => 7,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 2,
            'name' => 'Tricep Extension',
            'description' => 'Brining your hands from behind your head to fully extended above your head',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(3); // weight

        $movement = factory(Movement::class)->create([
            'category_id' => 7,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 2,
            'name' => 'Bent Over Row',
            'description' => 'A rowing motion with your arms whilst leaning forwards',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(3); // weight

        $movement = factory(Movement::class)->create([
            'category_id' => 8,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 2,
            'name' => 'Jack Knife',
            'description' => 'From lying on your back to both hands and feet pointing up while balancing on your bum',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(3); // weight

        $movement = factory(Movement::class)->create([
            'category_id' => 8,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 2,
            'name' => 'Russian Twist',
            'description' => 'Balancing on your bum whilst twisting your upper body from side to side',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(3); // weight

        $movement = factory(Movement::class)->create([
            'category_id' => 8,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 2,
            'name' => 'Bicycle Kick',
            'description' => 'Balancing on your bum whilst alternating touching opposite elbow to knee',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps

        $movement = factory(Movement::class)->create([
            'category_id' => 9,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 2,
            'name' => 'Pistol Squat',
            'description' => 'Lowering your body then standing back up again on one leg',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(3); // weight

        $movement = factory(Movement::class)->create([
            'category_id' => 9,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 2,
            'name' => 'Squat',
            'description' => 'Lowering your body then standing back up again on both legs',
            'official' => true,
        ]);
        $movement->fields()->attach(1); // reps
        $movement->fields()->attach(3); // weight

        $movement = factory(Movement::class)->create([
            'category_id' => 10,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 2,
            'name' => 'Run',
            'description' => 'A fast run over a medium distance',
            'official' => true,
        ]);
        $movement->fields()->attach(4); // duration
        $movement->fields()->attach(5); // distance

        $movement = factory(Movement::class)->create([
            'category_id' => 10,
            'user_id' => 1,
            'creator_id' => 1,
            'type_id' => 2,
            'name' => 'Jog',
            'description' => 'A slow run over a medium to long distance',
            'official' => true,
        ]);
        $movement->fields()->attach(4); // duration
        $movement->fields()->attach(5); // distance
    }
}
