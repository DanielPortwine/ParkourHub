<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSpotComment;
use App\Http\Requests\UpdateSpotComment;
use App\Notifications\SpotCommented;
use App\SpotComment;
use App\SpotCommentLike;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SpotCommentController extends Controller
{
    public function create(CreateSpotComment $request)
    {
        $comment = new SpotComment;
        $comment->spot_id = $request['spot'];
        $comment->user_id = Auth::id();
        $comment->comment = $request['comment'];
        if (!empty($request['youtube'])){
            $youtube = explode('t=', str_replace('https://youtu.be/?', '', str_replace('&', '', str_replace('https://www.youtube.com/watch?v=', '', $request['youtube']))));
            $comment->youtube = $youtube[0];
            $comment->youtube_start = $youtube[1] ?? null;
        } else if (!empty($request['video_image'])) {
            $file = $request->file('video_image');
            $extension = $file->extension();
            if (in_array($extension, ['mp4','mov','mpg','mpeg'])) {
                $comment->video = Storage::url($file->store('videos/spot_comments', 'public'));
                $comment->video_type = $extension;
            } else if (in_array($extension, ['jpg','jpeg','png'])) {
                $comment->image = Storage::url($file->store('images/spot_comments', 'public'));
            }
        }
        $comment->save();

        // notify the spot creator that someone created a comment
        $creator = User::where('id', $comment->spot->user_id)->first();
        if ($creator->id != Auth::id() && in_array(setting('comment', null, $creator->id), ['on-site', 'email', 'email-site'])) {
            $creator->notify(new SpotCommented($comment));
        }

        return back()->with('status', 'Successfully commented on spot');
    }

    public function edit($id)
    {
        $comment = SpotComment::where('id', $id)->first();

        return view('spots.comments.edit', ['comment' => $comment]);
    }

    public function update(UpdateSpotComment $request, $id)
    {
        $comment = SpotComment::where('id', $id)->first();
        $comment->comment = $request['comment'];
        if (!empty($request['youtube'])){
            $youtube = explode('t=', str_replace('https://youtu.be/?', '', str_replace('&', '', str_replace('https://www.youtube.com/watch?v=', '', $request['youtube']))));
            $comment->youtube = $youtube[0];
            $comment->youtube_start = $youtube[1] ?? null;
            $comment->video = null;
            $comment->image = null;
        } else if (!empty($request['video_image'])) {
            $file = $request->file('video_image');
            $extension = $file->extension();
            if (in_array($extension, ['mp4','mov','mpg','mpeg'])) {
                $comment->video = Storage::url($file->store('videos/spot_comments', 'public'));
                $comment->video_type = $extension;
                $comment->youtube = null;
                $comment->image = null;
            } else if (in_array($extension, ['jpg','jpeg','png'])) {
                $comment->image = Storage::url($file->store('images/spot_comments', 'public'));
                $comment->youtube = null;
                $comment->video = null;
            }
        }
        $comment->save();

        return back()->with('status', 'Successfully updated comment');

    }

    public function delete($id)
    {
        $comment = SpotComment::where('id', $id)->first();
        $spot = $comment->spot->id;
        if ($comment->user_id === Auth::id()) {
            $comment->delete();
        }

        return redirect()->route('spot_view', $spot);
    }

    public function like(Request $request, $id)
    {
        if (!$request->ajax()) {
            return back();
        }

        $comment = SpotComment::where('id', $id)->first();
        if (empty(SpotCommentLike::where('spot_comment_id', $id)->where('user_id', Auth::id())->first())) {
            $like = new SpotCommentLike;
            $like->spot_comment_id = $comment->id;
            $like->user_id = Auth::id();
            $like->save();
        }

        echo count(SpotCommentLike::where('spot_comment_id', $id)->get());
    }

    public function unlike(Request $request, $id)
    {
        if (!$request->ajax()) {
            return back();
        }

        SpotCommentLike::where('spot_comment_id', $id)->where('user_id', Auth::id())->delete();

        echo count(SpotCommentLike::where('spot_comment_id', $id)->get());
    }
}
