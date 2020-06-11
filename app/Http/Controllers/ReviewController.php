<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateReview;
use App\Http\Requests\UpdateReview;
use App\Notifications\SpotReviewed;
use App\Report;
use App\Review;
use App\Spot;
use App\User;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function create(CreateReview $request)
    {
        $review = Review::updateOrCreate(
            ['spot_id' => $request['spot'], 'user_id' => Auth::id()],
            [
                'rating' => empty($request['rating']) ? '0' : $request['rating'],
                'title' => !empty($request['title']) ? $request['title'] : null,
                'review' => !empty($request['review']) ? $request['review'] : null,
            ]
        );

        $spot = Spot::where('id', $request['spot'])->first();
        $spot->rating = round($spot->reviews->sum('rating') / count($spot->reviews));
        $spot->save();

        // notify the spot creator that someone created a review
        $creator = User::where('id', $review->spot->user_id)->first();
        if ($creator->id != Auth::id() && in_array(setting('notifications_review', null, $creator->id), ['on-site', 'email', 'email-site'])) {
            $creator->notify(new SpotReviewed($review));
        }

        return back()->with('status', 'Successfully submitted a review');
    }

    public function edit($id)
    {
        $review = Review::where('id', $id)->first();

        return view('reviews.edit', ['review' => $review]);
    }

    public function update(UpdateReview $request, $id)
    {
        $review = Review::where('id', $id)->first();
        $review->rating = empty($request['rating']) ? '0' : $request['rating'];
        $review->title = !empty($request['title']) ? $request['title'] : null;
        $review->review = !empty($request['review']) ? $request['review'] : null;
        $review->save();

        $spot = Spot::where('id', $request['spot'])->first();
        $spot->rating = round($spot->reviews->sum('rating') / count($spot->reviews));
        $spot->save();

        return back()->with('status', 'Successfully updated review');
    }

    public function delete($id)
    {
        $review = Review::where('id', $id)->first();
        $spot = Spot::where('id', $review->spot_id)->first();
        if ($review->user_id === Auth::id()) {
            $review->delete();
        }

        $spot->rating = round($spot->reviews->sum('rating') / count($spot->reviews));
        $spot->save();

        return redirect()->route('home');
    }

    public function report($id)
    {
        $report = new Report;
        $report->reportable_id = $id;
        $report->reportable_type = 'App\Review';
        $report->user_id = Auth::id();
        $report->save();

        return back()->with('status', 'Successfully reported Review.');
    }

    public function deleteReported($id)
    {
        $review = Review::where('id', $id)->first();
        $spot = $review->spot_id;
        $review->forceDelete();

        return redirect()->route('spot_view', $spot)->with('status', 'Successfully deleted Review.');
    }
}
