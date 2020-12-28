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
        $this->template->lessons = $this->isLogged();
    }

    private function isLogged()
    {
        parent::startup();
        if ($this->getUser()->loggedIn) {
            $userIdentity = $this->getUser()->getIdentity();

            return $this->curriculum->getLessonsByUserAndSemester($userIdentity->getId(), $userIdentity->semesterFrom, $userIdentity->semesterTo);
        } else {
            return $this->curriculum->getLessons();
        }
    }
}