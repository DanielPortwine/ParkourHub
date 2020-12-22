<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSpotComment;
use App\Http\Requests\UpdateSpotComment;
use App\Notifications\SpotCommented;
use App\Report;
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
        $comment->visibility = $request['visibility'] ?: 'private';
        if (!empty($request['youtube'])){
            $youtube = explode('t=', str_replace(['https://youtu.be/', 'https://www.youtube.com/watch?v=', '&', '?'], '', $request['youtube']));
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
        if ($creator->id != Auth::id() && in_array(setting('notifications_comment', 'on-site', $creator->id), ['on-site', 'email', 'email-site'])) {
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
        $comment->visibility = $request['visibility'] ?: 'private';
        if (!empty($request['youtube'])){
            $youtube = explode('t=', str_replace(['https://youtu.be/', 'https://www.youtube.com/watch?v=', '&', '?'], '', $request['youtube']));
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
                $comment->youtube_start = null;
                $comment->image = null;
            } else if (in_array($extension, ['jpg','jpeg','png'])) {
                $comment->image = Storage::url($file->store('images/spot_comments', 'public'));
                $comment->youtube = null;
                $comment->youtube_start = null;
                $comment->video = null;
            }
        }
        $comment->save();

        return back()->with('status', 'Successfully updated comment');

    }

    public function delete($id)
    {
        $comment = SpotComment::where('id', $id)->first();
        if ($comment->user_id === Auth::id()) {
            $comment->delete();
        }

        return back()->with('status', 'Successfully deleted comment');
    }

    public function report(SpotComment $spotComment)
    {
        $spotComment->report();

        return back()->with('status', 'Successfully reported spot comment');
    }

    public function discardReports(SpotComment $spotComment)
    {
        $spotComment->discardReports();

        return back()->with('status', 'Successfully discarded reports against this content');
    }
}
