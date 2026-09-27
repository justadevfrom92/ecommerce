<?php

namespace App\Http\Controllers\Admin\Emails;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Support\DataTable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends Controller
{
    public function index(Request $request): View
    {
        $query = EmailLog::query();

        if (array_key_exists((string) $request->query('status'), EmailLog::STATUSES)) {
            $query->where('status', $request->query('status'));
        }

        if ($type = $request->query('type')) {
            $query->where('template_key', $type);
        }

        $logs = DataTable::paginate($query, $request,
            searchable: ['to', 'subject'],
            sortable: ['to', 'subject', 'status', 'created_at'],
            defaultSort: 'created_at',
        );

        return view('admin.emails.log.index', compact('logs'));
    }
}
