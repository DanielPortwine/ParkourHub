<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateReview;
use App\Http\Requests\UpdateReview;
use App\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function create(CreateReview $request)
    {
        $review = new Review;
        $review->spot_id = $request['spot'];
        $review->user_id = Auth::id();
        $review->rating = empty($request['rating']) ? '0' : $request['rating'];
        $review->title = !empty($request['title']) ? $request['title'] : null;
        $review->review = !empty($request['review']) ? $request['review'] : null;
        $review->save();

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

        return back()->with('status', 'Successfully updated review');
    }

    public function delete($id)
    {
        $review = Review::where('id', $id)->first();
        if ($review->user_id === Auth::id()) {
            $review->delete();
        }

        return redirect()->route('home');
    }
}
