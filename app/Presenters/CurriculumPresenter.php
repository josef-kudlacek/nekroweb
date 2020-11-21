<?php


namespace App\Presenters;

use App\Model;


class CurriculumPresenter extends BasePresenter
{
    private $curriculum;

    /** @var Model\Semester
     * @inject
     */
    public $semester;

    public function __construct(Model\Curriculum $curriculum)
    {
        $this->curriculum = $curriculum;
    }

    public function renderShow()
    {
        $this->template->lessons = $this->curriculum->getLessons();
        $this->template->semester = $this->semester->GetActualSemester();
    }
}