<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\FeedbackRepository;
use Illuminate\Http\Request;

class FeedBackController extends Controller
{
    private $feedback_repository;

    public function __construct(FeedbackRepository $feedback_repository)
    {
        $this->feedback_repository = $feedback_repository;
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit') ?? 15;
        $filter = $request->all();
        $list = $this->feedback_repository->paginate($filter, ['user:id,phone'], $limit);

        return renderSuccess($list);
    }

    public function delete(Request $request)
    {
        $ids = $request->input('ids');

        if ($ids) {
            $this->feedback_repository->delete($ids);
        }

        return renderSuccess();
    }
}
