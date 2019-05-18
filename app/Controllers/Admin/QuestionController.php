<?php
namespace FK3\Controllers\Admin;

use FK3\Exceptions\FrugalException;
use FK3\Models\Question;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;

class QuestionController extends Controller
{
    public $auditPage = "Question Manager";

    /*
     * Show question Index.
     */
    public function index()
    {
        return view('admin.questions.index');
    }

    /**
     * Show an existing question
     * @param Question $question
     * @return mixed
     */
    public function show(Question $question)
    {
        return view('admin.questions.create')->withQuestion($question);
    }

    /**
     * Create new question
     * @return mixed
     */
    public function create()
    {
        return view('admin.questions.create')->withQuestion(new Question);
    }

    /**
     * Store a new question
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function store(Request $request)
    {
        $question = new Question();
        $request->merge([
            'vendor_id' => 0,
            'group_id' => 0,
        ]);
        if (
            !$request->question ||
            !$request->response_type ||
            !$request->stage ||
            !$request->question_category_id
        ) {
            throw new FrugalException("You must specify a question, a response type, a stage, and a category.");
        }

        $question->create($request->all());
        audit($this->auditPage, "Added a new question ($request->id)");
        return ['callback' => "redirect:/admin/questions"];
    }

    /**
     * Update a question.
     * @param Question $question
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function update(Question $question, Request $request)
    {
        if(!$request->contract) $request->merge(['contract' => 0]);
        if(!$request->small_job) $request->merge(['small_job' => 0]);
        if(!$request->on_checklist) $request->merge(['on_checklist' => 0]);
        if(!$request->on_job_board) $request->merge(['on_job_board' => 0]);

        $question->update($request->all());
        audit($this->auditPage, "Updated Question ID $question->id");
        return $this->success("Question Updated", ['callback' => "redirect:/admin/questions"]);
    }

    /**
     * Activate/Deactivate Question
     * @param Question $question
     * @return array
     */
    public function destroy(Question $question)
    {
        $question->update(['active' => !$question->active]);
        $message = (!$question->active) ? "Deactivated" : "Activated";
        audit($this->auditPage, "$message $question->id");
        return ['callback' => "redirect:/admin/questions"];

    }
}
