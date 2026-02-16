<?php

namespace App\Http\Controllers\Admin;

use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Imports\QuestionImport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;
use App\Services\Question\QuestionService;
use App\Http\Requests\Question\CreateQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(private QuestionService $service)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index($quiz_id)
    {
        $quiz = Quiz::find($quiz_id);
        $questions = $this->service->listQuestion($quiz_id);
        return view('backend.question.index', compact('quiz_id', 'questions', 'quiz'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($quiz_id)
    {
        return view('backend.question.create', compact('quiz_id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateQuestionRequest $request, $quiz_id)
    {
        $response = $this->service->createQuestion($request);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: "admin.quiz.question.index", route_params: ['quiz' => $quiz_id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $quiz_id, string $id)
    {
        $quiz = Quiz::find($quiz_id);
        $question = $this->service->showQuestion($quiz_id, $id);
        return view('backend.question.edit', compact('quiz_id', 'question', 'quiz'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuestionRequest $request, $quiz_id, $id)
    {
        $response = $this->service->updateQuestion($request, $id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => $response->message(),
                'data' => $response->getData(),
                'code' => $response->code(),
                'type' => $response->getResponeType()
            ]);
        }

        return redirect()
            ->route('admin.quiz.question.index', ['quiz' => $quiz_id])
            ->with($response->getResponeType(), $response->message());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($quiz_id, Question $question)
    {
        $response = null;

        DB::transaction(function () use ($question, &$response) {
            $question->translations()->delete(); // delete child records first

            $response = $this->service->destroy($question);
        });

        return Response::sendResponse($response?->getResponeType(), $response?->code(), $response?->message(), redirect: 'admin.quiz.question.index', route_params: ['quiz' => $quiz_id]);
    }

    public function importQuestion(Request $request, $quiz_id)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:2048'
        ]);

        Excel::import(new QuestionImport($quiz_id), $request->file('file'));

        return back()->with('success', 'Users imported successfully!');
    }

    public function destroyAll($quiz_id)
    {
        $response = $this->service->destroyAll($quiz_id);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.quiz.question.index', route_params: ['quiz' => $quiz_id]);
    }

    public function removeImage(Request $request, Question $question)
    {
        $type = $request->input('type');
        if ($type === 'image') {
            $fileField = 'image';
        } elseif ($type === 'visual_explanation') {
            $fileField = 'visual_explanation';
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid type'], 400);
        }

        $filePath = public_path('images/' . $question->$fileField);
        if ($question->$fileField && file_exists($filePath)) {
            unlink($filePath);
            $question->$fileField = null;
            $question->save();
        }
        return response()->json(['success' => true, 'message' => "Image remove successfully"]);
    }
}
