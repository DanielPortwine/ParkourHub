<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSpotComment;
use App\Http\Requests\UpdateSpotComment;
use App\Notifications\SpotCommented;
use App\Models\Report;
use App\Models\SpotComment;
use App\SpotCommentLike;
use App\Models\User;
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
        if (!empty($request['delete'])) {
            return $this->delete($id, $request['redirect']);
        }

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

        return back()->with([
            'status' => 'Successfully updated comment',
            'redirect' => $request['redirect'],
        ]);

    }

    public function delete($id, $redirect = null)
    {
        $comment = SpotComment::where('id', $id)->first();
        if ($comment->user_id === Auth::id()) {
            $comment->delete();
        }

        if (!empty($redirect)) {
            return redirect($redirect)->with('status', 'Successfully deleted comment');
        }

        return back()->with('status', 'Successfully deleted comment');
    }

    public function recover(Request $request, $id)
    {
        $comment = SpotComment::onlyTrashed()->where('id', $id)->first();

        if (empty($comment) || $comment->user_id !== Auth::id()) {
            return back();
        }

        $comment->restore();

        return back()->with('status', 'Successfully recovered comment.');
    }

    public function remove(Request $request, $id)
    {
        $comment = SpotComment::withTrashed()->where('id', $id)->first();

        if ($comment->user_id !== Auth::id() && !Auth::user()->hasPermissionTo('remove content')) {
            return back();
        }

        if (!empty($comment->image)) {
            Storage::disk('public')->delete(str_replace('storage/', '', $comment->image));
        }
        if (!empty($comment->video)) {
            Storage::disk('public')->delete(str_replace('storage/', '', $comment->video));
        }

        $comment->forceDelete();

        return back()->with('status', 'Successfully removed comment forever.');
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
