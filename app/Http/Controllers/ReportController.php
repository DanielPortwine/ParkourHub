<?php

namespace App\Http\Controllers;

use App\Challenge;
use App\ChallengeEntry;
use App\Report;
use App\Review;
use App\Spot;
use App\SpotComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request, $type = 'spot')
    {
        if (Auth::id() !== 1) {
            return back();
        }
        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
                'rating' => 'rating',
                'views' => 'views_count',
                'difficulty' => 'difficulty',
                'entries' => 'entries_count',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }
        switch ($type) {
            case 'challenge':
                $content = Challenge::withCount('reports')
                    ->withCount('entries')
                    ->whereHas('reports')
                    ->entered(!empty($request['entered']) ? true : false)
                    ->difficulty($request['difficulty'] ?? null)
                    ->dateBetween([
                        'from' => $request['date_from'] ?? null,
                        'to' => $request['date_to'] ?? null
                    ])
                    ->following(!empty($request['following']) ? true : false)
                    ->orderBy($sort[0], $sort[1])
                    ->paginate(20);
                break;
            case 'entry':
                $content = ChallengeEntry::withCount('reports')
                    ->whereHas('reports')
                    ->winner(!empty($request['winner']) ? true : false)
                    ->dateBetween([
                        'from' => $request['date_from'] ?? null,
                        'to' => $request['date_to'] ?? null
                    ])
                    ->orderBy($sort[0], $sort[1])
                    ->paginate(20);
                break;
            case 'review':
                $content = Review::withCount('reports')
                    ->whereHas('reports')
                    ->rating($request['rating'] ?? null)
                    ->dateBetween([
                        'from' => $request['date_from'] ?? null,
                        'to' => $request['date_to'] ?? null
                    ])
                    ->orderBy($sort[0], $sort[1])
                    ->paginate(20);
                break;
            case 'spot_comment':
                $content = SpotComment::withCount('reports')
                    ->whereHas('reports')
                    ->dateBetween([
                        'from' => $request['date_from'] ?? null,
                        'to' => $request['date_to'] ?? null
                    ])
                    ->orderBy($sort[0], $sort[1])
                    ->paginate(20);
                break;
            case 'spot':
            default:
                $content = Spot::withCount('reports')
                    ->withCount('views')
                    ->whereHas('reports')
                    ->hitlist(!empty($request['on_hitlist']) ? true : false)
                    ->ticked(!empty($request['ticked_hitlist']) ? true : false)
                    ->rating($request['rating'] ?? null)
                    ->dateBetween([
                        'from' => $request['date_from'] ?? null,
                        'to' => $request['date_to'] ?? null
                    ])
                    ->following(!empty($request['following']) ? true : false)
                    ->orderBy($sort[0], $sort[1])
                    ->paginate(20);
                break;
        }

        return view('content_listings', [
            'title' => 'Reported ' . ucfirst(str_replace('_', ' ', $type)) . 's',
            'content' => $content,
            'component' => $type,
        ]);
    }

    public function discard($id, $type)
    {
        $reports = Report::where('reportable_id', $id)->where('reportable_type', $type)->get();

        foreach ($reports as $report) {
            $report->delete();
        }

        return back()->with('status', 'Successfully discarded report.');
    }
}
