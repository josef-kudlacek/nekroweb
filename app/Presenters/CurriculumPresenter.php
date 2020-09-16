<?php


namespace App\Presenters;

use App\Model;


class CurriculumPresenter extends BasePresenter
{
    private $curriculum;

    public function __construct(Model\Curriculum $curriculum)
    {
        $this->curriculum = $curriculum;
    }

    public function renderShow()
    {
        $this->template->lessons = $this->curriculum->getLessons();
    }
}