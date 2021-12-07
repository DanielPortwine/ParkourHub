<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateComment;
use App\Http\Requests\UpdateComment;
use App\Models\Spot;
use App\Notifications\NewComment;
use App\Models\Report;
use App\Models\Comment;
use App\Scopes\VisibilityScope;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    public function store(CreateComment $request)
    {
        $model = app('App\Models\\' . $request['commentable_type'])->findOrFail($request['commentable_id']);
        $comment = new Comment;
        $comment->user_id = Auth::id();
        $comment->commentable()->associate($model);
        $comment->parent_comment_id = $request['parent_comment_id'];
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
                $comment->video = Storage::url($file->store('videos/comments', 'public'));
                $comment->video_type = $extension;
            } else if (in_array($extension, ['jpg','jpeg','png'])) {
                $comment->image = Storage::url($file->store('images/comments', 'public'));
            }
        }
        $comment->save();

        // notify the creator that someone created a comment
        $creator = User::where('id', $comment->commentable()->first()->user->id)->first();
        if ($creator->id != Auth::id() && in_array(setting('notifications_comment', 'on-site', $creator->id), ['on-site', 'email', 'email-site'])) {
            $creator->notify(new NewComment($comment));
        }

        return back()->with('status', 'Successfully commented on content');
    }

    public function edit($id)
    {
        $comment = Comment::withoutGlobalScope(VisibilityScope::class)->where('id', $id)->first();
        if ($comment->user_id !== Auth::id()) {
            return redirect()->route(strtolower(str_replace('App\Models\\', '', $comment->commentable_type)) . '_view', $comment->commentable_id);
        }

        return view('comments.edit', ['comment' => $comment]);
    }

    public function update(UpdateComment $request, $id)
    {
        if (!empty($request['delete'])) {
            return $this->delete($id, $request['redirect']);
        }

        $comment = Comment::withoutGlobalScope(VisibilityScope::class)->where('id', $id)->first();
        if ($comment->user_id !== Auth::id()) {
            return redirect()->route(strtolower(str_replace('App\Models\\', '', $comment->commentable_type)) . '_view', $comment->commentable_id);
        }

        $comment->comment = $request['comment'];
        $comment->visibility = $request['visibility'] ?: 'private';
        if (!empty($request['youtube']) || !empty($request['video_image'])) {
            Storage::disk('public')->delete(str_replace('storage/', '', $comment->video));
            Storage::disk('public')->delete(str_replace('storage/', '', $comment->image));
        }
        if (!empty($request['video_image'])) {
            $file = $request->file('video_image');
            $extension = $file->extension();
            if (in_array($extension, ['mp4','mov','mpg','mpeg'])) {
                $comment->video = Storage::url($file->store('videos/comments', 'public'));
                $comment->video_type = $extension;
                $comment->youtube = null;
                $comment->youtube_start = null;
                $comment->image = null;
            } else if (in_array($extension, ['jpg','jpeg','png'])) {
                $comment->image = Storage::url($file->store('images/comments', 'public'));
                $comment->youtube = null;
                $comment->youtube_start = null;
                $comment->video = null;
                $comment->video_type = null;
            }
        } else if (!empty($request['youtube'])) {
            $youtube = explode('t=', str_replace(['https://youtu.be/', 'https://www.youtube.com/watch?v=', '&', '?'], '', $request['youtube']));
            $comment->youtube = $youtube[0];
            $comment->youtube_start = $youtube[1] ?? null;
            $comment->video = null;
            $comment->video_type = null;
            $comment->image = null;
        }
        $comment->save();

        return back()->with([
            'status' => 'Successfully updated comment',
            'redirect' => $request['redirect'],
        ]);

    }

    public function delete($id, $redirect = null)
    {
        $comment = Comment::withoutGlobalScope(VisibilityScope::class)->where('id', $id)->first();
        if ($comment->user_id === Auth::id()) {
            $comment->delete();
        } else {
            return redirect()->route(strtolower(str_replace('App\Models\\', '', $comment->commentable_type)) . '_view', $comment->commentable_id);
        }

        if (!empty($redirect)) {
            return redirect($redirect)->with('status', 'Successfully deleted comment');
        }

        return back()->with('status', 'Successfully deleted comment');
    }

    public function recover(Request $request, $id)
    {
        $comment = Comment::onlyTrashed()->where('id', $id)->first();

        if (empty($comment) || $comment->user_id !== Auth::id()) {
            return back();
        }

        $comment->restore();

        return back()->with('status', 'Successfully recovered comment.');
    }

    public function remove(Request $request, $id)
    {
        $comment = Comment::withoutGlobalScope(VisibilityScope::class)->withTrashed()->where('id', $id)->first();

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

    public function report($id)
    {
        $spotComment = Comment::where('id', $id)->first();

        $spotComment->report();

        return back()->with('status', 'Successfully reported comment');
    }

    public function discardReports(Comment $comment)
    {
        if (!Auth::user()->hasPermissionTo('manage reports') || $comment->user_id === Auth::id()) {
            return back();
        }

        $comment->discardReports();

        return back()->with('status', 'Successfully discarded reports against this comment');
    }
}
