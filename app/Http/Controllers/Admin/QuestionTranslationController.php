<?php

namespace App\Http\Controllers\Admin;

use App\Models\Question;
use App\Models\Language;
use Illuminate\Http\Request;
use App\Models\QuestionTranslation;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class QuestionTranslationController extends Controller
{
  public function index()
  {
    $languages = Language::where('status', 'active')->get();
    return view('backend.question_translations.index', compact('languages'));
  }

  // public function getTranslations(Request $request)
  // {
  //   $query = QuestionTranslation::with(['question', 'language'])
  //     ->select('question_translations.*');

  //   if ($request->language_id) {
  //     $query->where('language_id', $request->language_id);
  //   }

  //   return DataTables::of($query)
  //     ->addColumn('question_text', function ($row) {
  //       return $row->question->question ?? 'N/A';
  //     })
  //     ->addColumn('translated_text', function ($row) {
  //       return $row->question_translation ?? 'Not translated';
  //     })
  //     ->addColumn('language', function ($row) {
  //       return $row->language->name ?? 'N/A';
  //     })
  //     ->addColumn('audio_status', function ($row) {
  //       if ($row->audio_file) {
  //         return '<span class="badge badge-success">Generated</span>';
  //       }
  //       return '<span class="badge badge-warning">Pending</span>';
  //     })
  //     ->addColumn('actions', function ($row) {
  //       $actions = '';

  //       // Preview audio if exists
  //       if ($row->audio_file) {
  //         $actions .= '<button type="button" class="btn btn-icon btn-sm btn-info mr-1 play-audio" data-audio="' . asset('audios/' . $row->audio_file) . '">
  //                       <i data-feather="play"></i>
  //                   </button>';
  //       }

  //       // Edit button
  //       $actions .= '<button type="button" class="btn btn-icon btn-sm btn-primary mr-1 edit-translation" data-id="' . $row->id . '">
  //                   <i data-feather="edit-2"></i>
  //               </button>';

  //       return $actions;
  //     })
  //     ->rawColumns(['audio_status', 'actions'])
  //     ->make(true);
  // }

  public function update(Request $request, $id)
  {
    $request->validate([
      'question_translation' => 'required|string',
      'a_translation' => 'required|string',
      'b_translation' => 'required|string',
      'c_translation' => 'required|string',
      'd_translation' => 'required|string',
      'answer_explanation_translation' => 'nullable|string'
    ]);

    $translation = QuestionTranslation::findOrFail($id);
    $translation->update($request->all());

    return response()->json([
      'success' => true,
      'message' => 'Translation updated successfully'
    ]);
  }

  public function show($id)
  {
    $translation = QuestionTranslation::with(['question', 'language'])->findOrFail($id);
    return response()->json($translation);
  }
}