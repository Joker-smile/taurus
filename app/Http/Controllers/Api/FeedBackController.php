<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\FeedbackRepository;
use App\Utils\Code;
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
        $list = $this->feedback_repository->paginate(['user_id' => currentUser()->id], null, $limit);

        return renderSuccess($list);
    }

    public function create(Request $request)
    {
        $rules = [
            'describe' => 'required|string',
            'type' => 'required|integer|in:1,2,3,4',
            'images' => 'array|nullable',
            'images.*' => 'url|nullable',
        ];

        if ($error = $this->formValidate($request, $rules, $data)) {
            return renderError(Code::PARAM_IS_ERROR, $error);
        }

        $images_count = count($data['images'] ?? []);
        if ($images_count > 5) {
            return renderError(Code::FAILED, '图片不能超过5张');
        }
        if (!$images_count) {
            $data['images'] = [];
        }

        $data['user_id'] = currentUser()->id;
        $data['client_type'] = $request->input('client_type');
        $result = $this->feedback_repository->create($data);
        if (!$result) return renderError(Code::SAVE_FAILED);

        return renderSuccess(Code::SUCCESS);
    }
}
